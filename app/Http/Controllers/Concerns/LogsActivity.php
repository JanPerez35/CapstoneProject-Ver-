<?php

namespace App\Http\Controllers\Concerns;

use App\Models\ActivityLog;

/**
 * Trait LogsActivity
 *
 * Provides reusable activity logging functionality for controllers.
 *
 * Responsibilities:
 * - retrieving the authenticated user
 * - storing activity records in the ActivityLog table
 * - saving the action performed, the user's role, IP address,
 *   and an optional descriptive comment
 */
trait LogsActivity
{
    /**
     * Stores an activity log entry for the authenticated user.
     *
     * If no user is authenticated, the method returns without
     * creating any log record.
     *
     * Logged fields:
     * - user_id: ID of the authenticated user
     * - role: role label of the user at the time of the action
     * - action: short description of the action performed
     * - ip_address: IP address from the current request
     * - comment: optional extra details about the action
     * 
     * @param string|null $comment Optional for additional context
     * @return void
     */
    private function logActivity($action, $comment = null)
    {
        $user = auth()->user();

        // Do not log anything if there is no authenticated user
        if (!$user) {
            return;
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'role' => $user->role_label,
            'action' => $action,
            'ip_address' => request()->ip(),
            'comment' => $comment,
            'created_at' => now(),
        ]);
    }
}