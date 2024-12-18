<?php

namespace App\Livewire\Chat;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ChatList extends Component
{
    public $selectedConversation;
    public $query;





    public function render()
    {
        $user = Auth::user();
        return view('livewire.chat.chat-list', [
          'conversations' => $user->conversations()->latest('updated_at')->get()
        ]);
    }
}
