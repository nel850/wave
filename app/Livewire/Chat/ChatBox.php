<?php

namespace App\Livewire\Chat;

use App\Models\Message;
use App\Models\Conversation;
use App\Services\WhatsAppService;
use App\Services\SmsService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class ChatBox extends Component
{

    public $selectedConversation;
    public $body;
    public $loadedMessages;

    protected $whatsAppService;
    protected $smsService;



    public function boot(WhatsAppService $whatsAppService ,SmsService $smsService)
    {
        $this->whatsAppService = $whatsAppService;
        $this->smsService = $smsService;
    }



    public function mount()
{
    Log::info('ChatBox mounted', [
        'user_id' => Auth::id(),
        'conversation_id' => optional($this->selectedConversation)->id
    ]);
    $this->loadMessages();
}



protected $listeners = [
    'loadedMessages' => '$refresh'
];

// Updated getListeners to include both broadcast and custom events
public function getListeners()
    {
        if (!$this->selectedConversation) {
            return [];
        }

        $channelName = "chatbox.{$this->selectedConversation->id}";
        return [
            "{$channelName},.App\\Notifications\\MessageReceived" => 'handleBroadcastedNotification',
            'newMessage' => '$refresh'
        ];
    }

    public function handleBroadcastedNotification($event)
{


    Log::info('Handling broadcasted notification in Livewire', [
        'notification' => $event,
        'conversation_id' => optional($this->selectedConversation)->id
    ]);

    // Check if we have the required data
    if (!isset($notification['message']) || !isset($notification['message']['conversation_id'])) {
        Log::error('Invalid notification format', ['notification' => $event]);
        return;
    }

    // Verify this is for the current conversation
    if ($event['message']['conversation_id'] !== $this->selectedConversation->id) {
        Log::info('Notification is for different conversation');
        return;
    }

    try {
        // Create new message instance
        $newMessage = new Message([
            'id' => $notification['message']['id'],
            'conversation_id' => $notification['message']['conversation_id'],
            'body' => $notification['message']['body'],
            'sender_id' => $notification['message']['sender_id'],
            'receiver_id' => $notification['message']['receiver_id'],
            'status' => $notification['message']['status'],
            'created_at' => $notification['message']['created_at']
        ]);

        // Add to collection
        $this->loadedMessages->push($newMessage);

        Log::info('Added new message to conversation', ['message_id' => $newMessage->id]);

        // Force a re-render
        $this->dispatch('newMessage');
        $this->dispatch('scroll-bottom');
    } catch (\Exception $e) {
        Log::error('Error handling notification', [
            'error' => $e->getMessage(),
            'notification' => $event
        ]);
    }
}


    public function sendMessage()
    {
        $this->validate(['body' => 'required|string']);

        $createdMessage = Message::create([
            'conversation_id' => $this->selectedConversation->id,
            'sender_id' => Auth::id(),
            'receiver_id' => $this->selectedConversation->recipient->id,
            'body' => $this->body,
            'status' => 'sent',
        ]);

        if ($this->selectedConversation->conversation_type === 'sms') {
            $this->sendSms($createdMessage);
        } elseif ($this->selectedConversation->conversation_type === 'whatsapp') {
            $this->sendWhatsAppMessage($createdMessage);
        }

        $this->reset('body');

        // Scroll to bottom
        $this->dispatch('scroll-bottom');

        // Push the message
        $this->loadedMessages->push($createdMessage);

    }


    protected function sendWhatsAppMessage(Message $message)
    {
        try {
            $recipientPhone = $this->selectedConversation->recipient->phone_number;
            $result = $this->whatsAppService->sendMessage($recipientPhone, $message->body);

            $message->update([
                'status' => $result['success'] ? 'sent' : 'failed',
                'external_message_id' => $result['success'] ? $result['message_id'] : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp message', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
            $message->update(['status' => 'failed']);
        }
    }

    protected function sendSms(Message $message)
    {
        try {
            $recipientPhone = $this->selectedConversation->recipient->phone_number;
            $results = $this->smsService->sendMessage($recipientPhone, $message->body);

            // Assuming only one recipient in this case
            $result = $results[0];

            $message->update([
                'status' => 'sent',
                'external_message_id' => $result['message_id'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send SMS message', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);

            $message->update(['status' => 'sent']);
        }
    }



    protected function loadMessages()
    {
        if ($this->selectedConversation) {
            $this->loadedMessages = Message::where('conversation_id', $this->selectedConversation->id)
                ->orderBy('created_at', 'asc')
                ->get();
        } else {
            $this->loadedMessages = collect();
        }
    }

    public function render()
    {
        return view('livewire.chat.chat-box');
    }
}
