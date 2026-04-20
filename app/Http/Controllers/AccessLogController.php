<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

/**
 * Class AccessLogController
 *
 * Handles all access log-related actions within the application.
 *
 * Responsibilities:
 * - retrieving activity logs
 * - displaying logs with pagination
 * - loading related user information for each log
 * - filtering logs by search term, role, and event type
 */
class AccessLogController extends Controller
{
    /**
     * Displays the access logs view.
     *
     * Accepts optional query parameters to filter results server-side
     * before pagination, so filters work across all pages:
     * - search: matches user name, IP, role, action, or comment
     * - role: exact match on the stored role string
     * - event: exact match on the stored action string
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('comment', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($event = $request->input('event')) {
            $query->where('action', $event);
        }

        $logs = $query->paginate(10)->withQueryString();

        return view('access_logs', compact('logs'));
    }
}