<?php

namespace App\Livewire\Chat;

use App\Models\Conversation;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ChatList extends Component
{
    public $selectedConversation;
    public $query;
    public $type = 'all';


    public function filterByType($type)
    {
        $this->type = $type;
    }



    public function render()
    {
        $user = Auth::user();
        $conversations = $user->conversations()
        ->when($this->type !== 'all', function($query) {
            return $query->where('conversation_type', $this->type);
        })
        ->latest('updated_at')
        ->get();
        return view('livewire.chat.chat-list', [
          'conversations' => $conversations
        ]);
    }
}
