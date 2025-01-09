<?php

namespace App\Notifications;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;


class MessageReceived extends Notification implements ShouldBroadcastNow
{
    protected $message;
    protected $conversationId;

    public function __construct($message, $conversationId)
    {
        $this->message = $message;
        $this->conversationId = $conversationId;
    }

    public function via($notifiable)
    {
        return ['broadcast'];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        Log::info('Broadcasting message', [
            'message_id' => $this->message->id,
            'conversation_id' => $this->conversationId
        ]);

        return new BroadcastMessage([
            'message' => [
                'id' => $this->message->id,
                'conversation_id' => $this->conversationId,
                'body' => $this->message->body,
                'sender_id' => $this->message->sender_id,
                'receiver_id' => $this->message->receiver_id,
                'status' => $this->message->status,
                'created_at' => $this->message->created_at->format('g:i a')
            ]
        ]);
    }

    public function broadcastOn()
    {
        return new Channel('chatbox.'.$this->conversationId); // Changed from PrivateChannel to Channel
    }
}
