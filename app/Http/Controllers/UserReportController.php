<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserReport;
use App\Http\Controllers\Concerns\LogsActivity;
use App\Models\Post;

class UserReportController extends Controller
{
    use LogsActivity;

    public function store(Request $request)
    {
        $request->validate([
            'reported_user_id' => 'required|exists:users,id',
            'post_id' => 'required|exists:posts,id',
            'report_reason' => 'required|string|max:255',
            'description' => 'required|string|min:10|max:1000',
        ]);

        $report = UserReport::create([
            'user_id' => auth()->id(),
            'reported_user_id' => $request->reported_user_id,
            'post_id' => $request->post_id,
            'report_reason' => $request->report_reason,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        $this->logActivity(
            'Crear reporte de usuario',
            "Se creó un reporte (ID: {$report->id}) contra el usuario ID: {$request->reported_user_id} por razón: '{$request->report_reason}'"
        );

        return response()->json([
            'success' => true
        ]);
    }
    public function getReports()
    {
        $reports = UserReport::with([
            'reporter:id,name',
            'reportedUser:id,name,status',
            'post:id,user_id,title'
        ])
        ->where('status', 'pending')
        ->latest()
        ->get();

        return response()->json($reports);
    }

    public function index()
    {
        $reports = UserReport::with(['reporter', 'reportedUser', 'post'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('marketplace_management.reports_management', compact('reports'));
    }

    public function resolve(UserReport $report)
    {
        $report->load('reportedUser');

        $report->update([
            'status' => 'resolved',
            'resolved_at' => now()
        ]);

        $this->logActivity(
            'Resolver reporte',
            "Se resolvió el reporte (ID: {$report->id}) contra el usuario '{$report->reportedUser->email}' (ID: {$report->reported_user_id})"
        );

        return response()->json([
            'success' => true
        ]);
    }

    public function ban(UserReport $report)
    {
        $report->load('reportedUser');

        $report->reportedUser->update([
            'status' => 'Bloqueado'
        ]);

        Post::where('user_id', $report->reported_user_id)->delete();

        $report->update([
            'status' => 'resolved',
            'resolved_at' => now()
        ]);

        $this->logActivity(
            'Bloquear usuario',
            "Se bloqueó al usuario '{$report->reportedUser->email}' (ID: {$report->reported_user_id}) mediante el reporte (ID: {$report->id})"
        );

        return response()->json([
            'success' => true
        ]);
    }
}