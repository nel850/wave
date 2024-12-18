<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{

    use HasFactory;

    protected $fillable = [
        'body',
        'sender_id',
        'receiver_id',
        'conversation_id',
       // 'direction',
        'status',
        'message_id',
        'read_at',
        'sent_at',
        'receiver_deleted_at',
        'sender_deleted_at'
    ];

    protected $cast = [
        //'metadata' => 'array'
    ];


    protected $dates = [
        'read_at',
        'sent_at',
        'receiver_deleted_at',
        'sender_deleted_at'
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     *
     */
    public function recipient()
    {
        return $this->belongsTo(Recipient::class, 'receiver_id');
    }

    public function isRead():bool
    {
        return $this->read_at != null;
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Mark message as read
     */
    public function markAsRead()
    {
        $this->read_at = now();
        $this->status = 'read';
        $this->save();
    }
}
