<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserReport;
use App\Http\Controllers\Concerns\LogsActivity;
use App\Models\Post;
use App\Models\User;
use App\Services\EmailService;

/**
 * Class UserReportController
 *
 * Handles user reporting and moderation actions.
 *
 * Responsibilities:
 * - creating reports
 * - retrieving reports
 * - resolving reports
 * - banning users
 */
class UserReportController extends Controller
{
    use LogsActivity;

    /**
     * Email service used to send account-related notifications.
     */
    protected $emailService;

    /**
     * Injects the EmailService dependency.
     */
    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }
    /**
     * Stores a new user report.
     *
     * Validates:
     * - reported user
     * - related post
     * - reason and description
     *
     * Logs the report creation.
     */
    public function store(Request $request)
    {
        $request->validate([
            'reported_user_id' => 'required|exists:users,id',
            'post_id' => 'required|exists:posts,id',
            'report_reason' => 'required|string|max:255',
            'description' => 'required|string|min:10|max:1000',
        ]);

        if (auth()->id() == $request->reported_user_id) {
            abort(403, 'No puedes reportarte a ti mismo');
        }

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
        /**
        * Retrieves all pending user reports.
        *
        * Includes:
        * - reporter info
        * - reported user info
        * - related post info
        *
        * Returns JSON response for API or view for management.
        */
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

    /**
     * Displays reports management view.
     *
     * Filters:
     * - only pending reports
     */
    public function index()
    {
        $reports = UserReport::with(['reporter', 'reportedUser', 'post'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('marketplace_management.reports_management', compact('reports'));
    }
    /**
     * Marks a report as resolved.
     *
     * Updates:
     * - status
     * - resolved timestamp
     *
     * Logs the action.
     */
    public function resolve(UserReport $report)
    {
        $report->load('reportedUser');

        if (!in_array(auth()->user()->role, ['Admin Super', 'Admin Mercado'])) {
            abort(403);
        }

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
    /**
     * Bans a reported user.
     *
     * Actions:
     * - updates user status to "Bloqueado"
     * - deletes all user's posts
     * - resolves the report
     * - logs the action
     */
    public function ban(UserReport $report)
    {
        $report->load('reportedUser');

        $previousStatus = $report->reportedUser->status;

        if (!in_array(auth()->user()->role, ['Admin Super', 'Admin Mercado'])) {
            abort(403);
        }

        $report->reportedUser->update([
            'status' => 'Bloqueado'
        ]);

        Post::where('user_id', $report->reported_user_id)->delete();

        $report->update([
            'status' => 'resolved',
            'resolved_at' => now()
        ]);

        $superAdmin = User::where('role', 'Admin Super')
            ->where('status', 'Activo')
            ->first();

        $superAdminEmail = $superAdmin?->email ?? '+1 (787)-832-4040 Ext. 3841, 2008';

        /**
         * Send email when a reported user is banned.
         * Only triggers if the user was not already blocked.
         */
        if ($previousStatus !== 'Bloqueado') {
            $this->emailService->send(
                $report->reportedUser->email,
                'Cuenta bloqueada',
                'Tu cuenta ha sido bloqueada de la plataforma MAIKINE. Si entiendes que esto fue un error, comunícate con el super administrador (' . $superAdminEmail . ').'
            );
        }

        $this->logActivity(
            'Bloquear usuario',
            "Se bloqueó al usuario '{$report->reportedUser->email}' (ID: {$report->reported_user_id}) mediante el reporte (ID: {$report->id})"
        );

        return response()->json([
            'success' => true
        ]);
    }
}
