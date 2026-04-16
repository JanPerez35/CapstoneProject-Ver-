<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;

class AccessLogController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('access_logs', compact('logs'));
    }
}