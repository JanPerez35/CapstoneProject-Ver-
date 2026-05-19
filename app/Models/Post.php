<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Post
 *
 * Represents a product listing created by a user.
 *
 * Responsibilities:
 * - defining relationships to User, Chat, and Review models
 * - providing accessors for formatted attributes (e.g., time ago)
 * - handling post attributes such as title, description, cost, and photos
 */
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
    
    protected $appends = ['time_ago'];

    // Define relationships to User, Chat, and Review models
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define relationship to chats related to this post
    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    // Accessor to get human-readable time ago format   
    public function getTimeAgoAttribute()   
    {
        if (!$this->created_at) {
            return '';
        }

        return $this->created_at->diffForHumans();
    }

    // Define relationship to reviews for this post
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}