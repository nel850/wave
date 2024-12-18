<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipient extends Model
{
use HasFactory;

protected $fillable = [
    'name',
    'wa_id',
    'phone_number',
    'avatar_url',
    'status'
];

    protected function conversations()
    {
        return $this->hasMany(Conversation::class, 'receiver_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
}
