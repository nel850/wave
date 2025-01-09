<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class IncomingMessageNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    protected $message;
    protected $conversation;

    public function __construct($message, $conversation)
    {
        $this->message = $message;
        $this->conversation = $conversation;
    }

    public function via(object $notifiable): array
    {
        return ['broadcast'];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'message' => [
                'id' => $this->message->id,
                'body' => $this->message->body,
                'sender_id' => $this->message->sender_id,
                'receiver_id' => $this->message->receiver_id,
                'conversation_id' => $this->message->conversation_id,
                'status' => $this->message->status,
                'created_at' => $this->message->created_at,
                'sent_at' => $this->message->sent_at,
            ],
            'conversation' => [
                'id' => $this->conversation->id,
                'type' => $this->conversation->conversation_type,
            ],
        ]);
    }
}
