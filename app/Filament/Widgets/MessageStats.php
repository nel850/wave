<?php

namespace App\Filament\Widgets;

use App\Models\Message;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MessageStats extends BaseWidget
{
    protected static ?int $sort = 1;

    public ?string $filter = 'week';

    protected function getStats(): array
    {
        $query = Message::query()
            ->join('conversations', 'messages.conversation_id', '=', 'conversations.id')
            ->select(
                'conversations.conversation_type',
                DB::raw('COUNT(*) as total_messages'),
                DB::raw('COUNT(CASE WHEN messages.status = "sent" THEN 1 END) as sent_messages'),
                DB::raw('COUNT(CASE WHEN messages.status = "delivered" THEN 1 END) as delivered_messages'),
                DB::raw('COUNT(CASE WHEN messages.status = "failed" THEN 1 END) as failed_messages')
            );

        // Apply date filter
        switch ($this->filter) {
            case 'today':
                $query->whereDate('messages.created_at', Carbon::today());
                break;
            case 'week':
                $query->where('messages.created_at', '>=', Carbon::now()->subWeek());
                break;
            case 'month':
                $query->where('messages.created_at', '>=', Carbon::now()->subMonth());
                break;
        }

        $stats = $query->groupBy('conversations.conversation_type')->get();

        $allStats = [];

        foreach ($stats as $stat) {
            $allStats[] = Stat::make(ucfirst($stat->conversation_type) . ' Total', $stat->total_messages)
                ->description($this->getTimeframeDescription())
                ->descriptionIcon('heroicon-m-calendar')
                ->chart([
                    $stat->sent_messages,
                    $stat->delivered_messages,
                    $stat->failed_messages,
                ]);

            $allStats[] = Stat::make(ucfirst($stat->conversation_type) . ' Success Rate',
                number_format(($stat->delivered_messages / max($stat->total_messages, 1)) * 100, 1) . '%')
                ->description($stat->delivered_messages . ' delivered of ' . $stat->total_messages . ' messages')
                ->color($this->getSuccessRateColor($stat->delivered_messages, $stat->total_messages));
        }

        return $allStats;
    }

    protected function getSuccessRateColor(int $delivered, int $total): string
    {
        $rate = ($delivered / max($total, 1)) * 100;

        if ($rate >= 90) return 'success';
        if ($rate >= 70) return 'warning';
        return 'danger';
    }

    protected function getTimeframeDescription(): string
    {
        return match ($this->filter) {
            'today' => 'Today',
            'week' => 'Last 7 days',
            'month' => 'Last 30 days',
            default => 'All time',
        };
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last 7 days',
            'month' => 'Last 30 days',
        ];
    }
}
