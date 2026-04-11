<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'post_id',
        'user_id',
        'seller_id',
        'rating',
        'comment',
        'status',
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}