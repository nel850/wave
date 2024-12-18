<?php

namespace App\Livewire;

use App\Models\Recipient;
use Livewire\Component;
use App\Models\Conversation;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;


#[Layout('layouts.app')]
class Recipients extends Component
{
    public $type;

    public function mount($type = null) // Optional: add a mount method to set the type
    {
        $this->type = $type ?? 'sms'; // Set a default type if not provided
    }

    public function startConversation($recipientId, $type = null)
    {
        $conversationType = $type ?? $this->type;

        $authenticatedUserId = Auth::user()->id;
 // Check if conversation exists
        $existingConversation = Conversation::where(function ($query) use ($authenticatedUserId, $recipientId, $conversationType) {
            $query->where('sender_id', $authenticatedUserId)
                  ->where('receiver_id', $recipientId)
                  ->where('conversation_type', $conversationType);
        })->orWhere(function ($query) use ($authenticatedUserId, $recipientId, $conversationType) {
            $query->where('sender_id', $recipientId)
                  ->where('receiver_id', $authenticatedUserId)
                  ->where('conversation_type', $conversationType);
        })->first();

        if ($existingConversation) {
            return redirect()->route('chat', ['query' => $existingConversation->id]);
        }

        // Create conversation
        $createdConversation = Conversation::create([
            'sender_id' => $authenticatedUserId,
            'receiver_id' => $recipientId,
            'conversation_type' => $conversationType,
        ]);

        return redirect()->route('chat', ['query' => $createdConversation->id]);
    }

    public function sms($recipientId)
    {
        return $this->startConversation($recipientId, 'sms');
    }

    public function whatsapp($recipientId)
    {
        return $this->startConversation($recipientId, 'whatsapp');
    }

    public function render()
    {
        return view('livewire.recipients', ['recipients' => Recipient::all()]);
    }
}
