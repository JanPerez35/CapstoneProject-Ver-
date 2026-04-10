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

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_user_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
    public function otherUser()
    {
        $currentUserId = auth()->id();

        if ($this->buyer_user_id == $currentUserId) {
            return $this->seller_user_id ? $this->seller : null;
        }

        return $this->buyer_user_id ? $this->buyer : null;
    }
}