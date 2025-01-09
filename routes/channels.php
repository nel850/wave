<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('chatbox.{conversationId}', function ($user, $conversationId) {
    Log::info('Channel authorization attempt', [
        'user' => $user->id,
        'conversation' => $conversationId
    ]);

    return Conversation::where('id', $conversationId)
        ->where(function ($query) use ($user) {
            $query->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
        })->exists();
});
