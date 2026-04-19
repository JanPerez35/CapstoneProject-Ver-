<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;

/**
 * Class AccessLogController
 *
 * Handles all access log-related actions within the application.
 *
 * Responsibilities:
 * - retrieving activity logs
 * - displaying logs with pagination
 * - loading related user information for each log
 */
class AccessLogController extends Controller
{
    /**
     * Displays the access logs view.
     *
     * Retrieves all activity logs ordered from newest to oldest,
     * including the associated user information, and applies
     * pagination (10 logs per page).
     */
    public function index()
    {
        // Get activity logs with related user data
        $logs = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Return logs to the view
        return view('access_logs', compact('logs'));
    }
}