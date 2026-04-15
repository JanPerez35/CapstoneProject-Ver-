<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserReport extends Model
{
    protected $table = 'user_reports';

    protected $fillable = [
        'user_id',
        'reported_user_id',
        'report_reason',
        'description',
        'status',
        'resolved_at',
        'post_id',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}