<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        'post_id',
        'buyer_user_id',
        'seller_user_id',
        'status',
    ];

    // Messages in this chat
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    // Buyer
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }

    // Seller
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_user_id');
    }

    // Post
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}