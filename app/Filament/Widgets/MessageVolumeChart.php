<?php

namespace App\Filament\Widgets;

use App\Models\Message;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MessageVolumeChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Message Volume Trends';

    public ?string $filter = 'week';

    protected function getData(): array
    {
        $query = Message::query()
            ->join('conversations', 'messages.conversation_id', '=', 'conversations.id')
            ->select(
                'conversations.conversation_type',
                DB::raw('DATE(messages.created_at) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->where('messages.created_at', '>=', $this->getStartDate())
            ->groupBy('conversations.conversation_type', 'date')
            ->orderBy('date');

        $records = $query->get();

        $datasets = [];
        $labels = [];

        // Group by channel type
        foreach ($records->groupBy('conversation_type') as $type => $data) {
            $datasets[] = [
                'label' => ucfirst($type),
                'data' => $data->pluck('total')->toArray(),
            ];

            // Collect unique dates
            $labels = array_merge($labels, $data->pluck('date')->toArray());
        }

        return [
            'datasets' => $datasets,
            'labels' => array_unique($labels),
        ];
    }

    protected function getStartDate(): Carbon
    {
        return match ($this->filter) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            default => Carbon::now()->subWeek(),
        };
    }

    protected function getType(): string
    {
        return 'line';
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
