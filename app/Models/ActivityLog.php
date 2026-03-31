<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'role',
        'action',
        'ip_address',
        'comment',
        'created_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}