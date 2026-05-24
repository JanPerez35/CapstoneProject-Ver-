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
        $query = $this->buildFilteredQuery($request);
        $logs = $query->paginate(10)->withQueryString();

        return view('access_logs', compact('logs'));
    }

    public function exportCsv(Request $request)
    {
        $logs = $this->buildFilteredQuery($request)->get();

        $hasFilters = $request->filled('search') || $request->filled('role')
            || $request->filled('event') || $request->filled('date');

        $filename = $hasFilters
            ? 'access_logs_filtrado_' . now('America/Puerto_Rico')->format('Y-m-d') . '.csv'
            : 'access_logs_' . now('America/Puerto_Rico')->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Marca de Tiempo', 'Usuario', 'Rol', 'Evento', 'Dirección IP', 'Comentario']);

            foreach ($logs as $log) {
                $timestamp = \Carbon\Carbon::parse($log->created_at)
                    ->timezone('America/Puerto_Rico')
                    ->format('Y-m-d H:i:s');

                $user = trim(($log->user->first_name ?? '') . ' ' . ($log->user->last_name ?? '')) ?: 'Usuario';

                fputcsv($handle, [
                    $timestamp,
                    $user,
                    $log->role,
                    $log->action,
                    $log->ip_address,
                    $log->comment ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function buildFilteredQuery(Request $request)
    {
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
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

        if ($date = $request->input('date')) {
            $query->whereDate('created_at', $date);
        }

        return $query;
    }
}
