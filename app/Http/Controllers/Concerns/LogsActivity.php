<?php

namespace App\Http\Controllers\Concerns;

use App\Models\ActivityLog;

trait LogsActivity
{
    private function logActivity($action, $comment = null)
    {
        $user = auth()->user();

        if (!$user) {
            return;
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'role' => $user->role_label,
            'action' => $action,
            'ip_address' => request()->ip(),
            'comment' => $comment,
        ]);
    }
}