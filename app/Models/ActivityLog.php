<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ActivityLog
 *
 * Represents an audit log entry for important user actions in the system.
 *
 * Activity logs store the user, role at the time of the action, action name,
 * IP address, descriptive comment, and creation timestamp. The model disables
 * automatic timestamps because created_at is manually inserted when the log
 * entry is created.
 */
class ActivityLog extends Model
{
    /**
     * Database table associated with the model.
     *
     * @var string
     */
    protected $table = 'activity_logs';

    /**
     * Indicates whether the model should automatically maintain timestamps.
     *
     * This is disabled because the activity_logs table stores created_at
     * manually and does not rely on Laravel's updated_at behavior.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Attributes that can be mass assigned.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'role',
        'action',
        'ip_address',
        'comment',
        'created_at',
    ];

    /**
     * Gets the user associated with this activity log entry.
     *
     * @return BelongsTo<User, ActivityLog>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}