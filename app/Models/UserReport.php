<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserReport
 *
 * Represents a report made by a user against another user for inappropriate behavior or content.
 *
 * Responsibilities:
 * - defining relationships to User and Post models
 * - handling report attributes such as reason, description, status, and resolution timestamp
 */
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

    // Define relationships to User and Post models
    public function reporter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Define relationship to the reported user
    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    // Define relationship to the related post
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}