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
        $this->loadMessages();
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

            Log::info('Loading messages', [
                'conversation_id' => $this->selectedConversation->id,
            'current_user_id' => Auth::user()->id

            ]);

            $this->loadedMessages = Message::where('conversation_id', $this->selectedConversation->id)
                ->where(function ($query) {
                    $query->where('sender_id', Auth::user()->id)
                          ->orWhere('receiver_id');
                })


                ->orderBy('created_at', 'asc')
                ->get();

                // Log::info('Loaded messages count', [
                //     'count' => $this->loadedMessages->count()
                // ]);
        } else {
            $this->loadedMessages = collect(); // Empty collection if no conversation is selected
        }
    }

    public function render()
    {
        return view('livewire.chat.chat-box');
    }
}
