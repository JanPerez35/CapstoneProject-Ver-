<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Chat
 *
 * Represents a conversation between a buyer and a seller regarding a specific post.
 *
 * Responsibilities:
 * - defining relationships to users, posts, and messages
 * - providing access to the other participant in the chat
 */
class Chat extends Model
{
    protected $fillable = [
        'post_id',
        'buyer_user_id',
        'seller_user_id',
        'status',
    ];
    // Define relationships to User and Post models
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }

    // Define relationship to the seller user
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_user_id');
    }

    // Define relationship to the related post
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    // Define relationship to messages in the chat
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    // Method to get the other participant in the chat
    public function otherUser()
    {
        $currentUserId = auth()->id();

        if ($this->buyer_user_id == $currentUserId) {
            return $this->seller_user_id ? $this->seller : null;
        }

        return $this->buyer_user_id ? $this->buyer : null;
    }
}