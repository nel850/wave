<?php

namespace App\Livewire\Chat;

use App\Models\Conversation;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]


class Chat extends Component
{

    public $query;
    public $selectedConversation;


    public function mount() {
        $this->selectedConversation = Conversation::findOrFail($this->query);

        //dd($this->selectedConversation);
    }



    public function render()
    {
        return view('livewire.chat.chat');
    }
}
