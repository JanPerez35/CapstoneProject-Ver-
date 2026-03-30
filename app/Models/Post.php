<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'user_id',
        'cost',
        'title',
        'status',
        'condition',
        'description',
        'category',
        'photo_1_url',
        'photo_2_url',
        'photo_3_url',
        'rating',
    ];

    // Seller (owner)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Chats related to this post
    public function chats()
    {
        return $this->hasMany(Chat::class);
    }
}