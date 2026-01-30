<?php

namespace App\Http\Controllers;

use App\Models\AttendanceAuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    /**
     * Display audit logs.
     */
    public function index(Request $request): Response
    {
        $companyId = auth()->user()->company_id;
        
        $query = AttendanceAuditLog::forCompany($companyId)
            ->with(['user', 'employee', 'reviewer']);

        // Filters
        if ($request->has('action') && $request->action !== 'all') {
            $query->byAction($request->action);
        }

        if ($request->has('severity') && $request->severity !== 'all') {
            $query->bySeverity($request->severity);
        }

        if ($request->has('user_id')) {
            $query->forUser($request->user_id);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->boolean('suspicious_only')) {
            $query->suspicious();
        }

        if ($request->boolean('requires_review')) {
            $query->requiresReview();
        }

        $logs = $query->latest()->paginate(50);

        // Stats
        $stats = AttendanceAuditLog::getDailySummary($companyId);

        return Inertia::render('security/audit-logs', [
            'logs' => $logs,
            'stats' => $stats,
            'actions' => AttendanceAuditLog::$actions,
            'severities' => AttendanceAuditLog::$severities,
            'filters' => $request->only(['action', 'severity', 'user_id', 'date_from', 'date_to', 'suspicious_only', 'requires_review']),
        ]);
    }

    /**
     * Show log details.
     */
    public function show(AttendanceAuditLog $auditLog): Response
    {
        $auditLog->load(['user', 'employee', 'reviewer']);

        return Inertia::render('security/audit-log-details', [
            'log' => $auditLog,
        ]);
    }

    /**
     * Mark as reviewed.
     */
    public function markReviewed(AttendanceAuditLog $auditLog)
    {
        $auditLog->markAsReviewed(auth()->id());

        return back()->with('success', 'تم تحديد السجل كمراجع');
    }

    /**
     * Get suspicious logs count (API).
     */
    public function getSuspiciousCount()
    {
        $companyId = auth()->user()->company_id;
        
        $count = AttendanceAuditLog::forCompany($companyId)
            ->suspicious()
            ->where('reviewed', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get logs requiring review (API).
     */
    public function getRequiresReview()
    {
        $companyId = auth()->user()->company_id;
        
        $logs = AttendanceAuditLog::forCompany($companyId)
            ->requiresReview()
            ->with(['user', 'employee'])
            ->latest()
            ->limit(20)
            ->get();

        return response()->json($logs);
    }

    /**
     * Export audit logs.
     */
    public function export(Request $request)
    {
        $companyId = auth()->user()->company_id;
        
        $query = AttendanceAuditLog::forCompany($companyId)
            ->with(['user', 'employee']);

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->latest()->get();

        // Log this export action
        AttendanceAuditLog::log(
            'export',
            new \stdClass(),
            null,
            ['type' => 'audit_logs', 'count' => $logs->count()],
            'تصدير سجلات التدقيق'
        );

        // Return as CSV
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="audit_logs_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'التاريخ',
                'المستخدم',
                'الموظف',
                'الإجراء',
                'الخطورة',
                'الوصف',
                'IP',
                'الجهاز',
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user?->name ?? '-',
                    $log->employee ? $log->employee->first_name . ' ' . $log->employee->last_name : '-',
                    $log->action_name,
                    $log->severity_name,
                    $log->description,
                    $log->ip_address,
                    $log->device_type,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get activity timeline for user.
     */
    public function userActivity(Request $request, int $userId): Response
    {
        $companyId = auth()->user()->company_id;
        
        $logs = AttendanceAuditLog::forCompany($companyId)
            ->forUser($userId)
            ->latest()
            ->paginate(50);

        return Inertia::render('security/user-activity', [
            'logs' => $logs,
            'userId' => $userId,
        ]);
    }

    /**
     * Dashboard summary.
     */
    public function dashboard(): Response
    {
        $companyId = auth()->user()->company_id;

        // Today's summary
        $todaySummary = AttendanceAuditLog::getDailySummary($companyId);

        // Last 7 days trend
        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $summary = AttendanceAuditLog::getDailySummary($companyId, $date);
            $trend[] = [
                'date' => $date,
                'total' => $summary['total_actions'],
                'suspicious' => $summary['suspicious_count'],
            ];
        }

        // Suspicious logs requiring review
        $pendingReview = AttendanceAuditLog::forCompany($companyId)
            ->requiresReview()
            ->with(['user', 'employee'])
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('security/audit-dashboard', [
            'todaySummary' => $todaySummary,
            'trend' => $trend,
            'pendingReview' => $pendingReview,
        ]);
    }
}
