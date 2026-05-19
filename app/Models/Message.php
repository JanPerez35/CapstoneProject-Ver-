<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Message
 *
 * Represents a message sent within a chat conversation.
 *
 * Responsibilities:
 * - defining relationships to Chat and User models
 * - handling message content and metadata (status, timestamps)
 * - encrypting message content for security
 */
class Message extends Model
{
    protected $fillable = [
        'chat_id',
        'user_id',
        'content',
        'status',
        'sent_at',
        'read_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'content' => 'encrypted',
    ];
    
    // Define relationships to Chat and User models
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    // Define relationship to the sender user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}