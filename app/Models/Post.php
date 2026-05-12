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
    
    protected $appends = ['time_ago'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    public function getTimeAgoAttribute()
    {
        if (!$this->created_at) {
            return '';
        }

        return $this->created_at->diffForHumans();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}