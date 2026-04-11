<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserReport;

class UserReportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'reported_user_id' => 'required|exists:users,id',
            'report_reason' => 'required|string|max:255',
            'description' => 'required|string|min:10|max:1000',
        ]);

        UserReport::create([
            'user_id' => auth()->id(),
            'reported_user_id' => $request->reported_user_id,
            'report_reason' => $request->report_reason,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true
        ]);
    }

    public function index()
    {
        $reports = UserReport::with(['reporter', 'reportedUser'])
            ->latest()
            ->get();

        return response()->json($reports);
    }

    public function resolve(UserReport $report)
    {
        $report->update([
            'status' => 'resolved',
            'resolved_at' => now()
        ]);

        return response()->json([
            'success' => true
        ]);
    }

    public function ban(UserReport $report)
    {
        $report->reportedUser->update([
            'status' => 'banned'
        ]);

        $report->update([
            'status' => 'resolved',
            'resolved_at' => now()
        ]);

        return response()->json([
            'success' => true
        ]);
    }
}