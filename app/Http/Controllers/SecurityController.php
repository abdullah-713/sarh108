<?php

namespace App\Http\Controllers;

use App\Models\LivenessCheck;
use App\Models\TamperLog;
use App\Services\LivenessService;
use App\Services\TamperDetectionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SecurityController extends Controller
{
    protected LivenessService $livenessService;
    protected TamperDetectionService $tamperService;

    public function __construct(
        LivenessService $livenessService,
        TamperDetectionService $tamperService
    ) {
        $this->livenessService = $livenessService;
        $this->tamperService = $tamperService;
    }

    /**
     * لوحة الأمان الرئيسية
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $companyUserIds = $user->getAllCompanyUserIds();

        // إحصائيات الفحوصات
        $livenessStats = $this->livenessService->getStats($companyUserIds);
        
        // إحصائيات التلاعب
        $tamperStats = $this->tamperService->getStats($companyUserIds);

        // محاولات التلاعب الأخيرة
        $recentTampers = TamperLog::where(function ($q) use ($companyUserIds) {
                $q->whereHas('employee', function ($eq) use ($companyUserIds) {
                    $eq->whereIn('created_by', $companyUserIds);
                });
            })
            ->with(['employee:id,first_name,last_name', 'branch:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // المخالفين المتكررين
        $repeatOffenders = $this->tamperService->getRepeatOffenders($companyUserIds);

        return Inertia::render('ai/security-dashboard', [
            'livenessStats' => $livenessStats,
            'tamperStats' => $tamperStats,
            'recentTampers' => $recentTampers,
            'repeatOffenders' => $repeatOffenders,
        ]);
    }

    /**
     * سجل فحوصات الحيوية
     */
    public function livenessLogs(Request $request)
    {
        $user = auth()->user();
        $companyUserIds = $user->getAllCompanyUserIds();

        $filter = $request->get('filter', 'all');

        $logs = LivenessCheck::whereHas('employee', function ($q) use ($companyUserIds) {
                $q->whereIn('created_by', $companyUserIds);
            })
            ->when($filter === 'passed', fn($q) => $q->passed())
            ->when($filter === 'failed', fn($q) => $q->failed())
            ->when($filter === 'spoofing', fn($q) => $q->spoofingAttempts())
            ->with(['employee:id,first_name,last_name'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('ai/liveness-logs', [
            'logs' => $logs,
            'filter' => $filter,
        ]);
    }

    /**
     * سجل محاولات التلاعب
     */
    public function tamperLogs(Request $request)
    {
        $user = auth()->user();
        $companyUserIds = $user->getAllCompanyUserIds();

        $status = $request->get('status');
        $type = $request->get('type');
        $severity = $request->get('severity');

        $logs = TamperLog::where(function ($q) use ($companyUserIds) {
                $q->whereHas('employee', function ($eq) use ($companyUserIds) {
                    $eq->whereIn('created_by', $companyUserIds);
                });
            })
            ->when($status, fn($q) => $q->where('review_status', $status))
            ->when($type, fn($q) => $q->where('tamper_type', $type))
            ->when($severity, fn($q) => $q->where('severity', $severity))
            ->with(['employee:id,first_name,last_name', 'branch:id,name', 'reviewer:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('ai/tamper-logs', [
            'logs' => $logs,
            'filters' => [
                'status' => $status,
                'type' => $type,
                'severity' => $severity,
            ],
        ]);
    }

    /**
     * مراجعة محاولة تلاعب
     */
    public function reviewTamper(Request $request, TamperLog $tamperLog)
    {
        $validated = $request->validate([
            'status' => 'required|in:confirmed,false_positive,dismissed',
            'notes' => 'nullable|string|max:500',
        ]);

        $tamperLog->update([
            'review_status' => $validated['status'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $validated['notes'],
        ]);

        return redirect()->back()->with('success', 'تم تحديث المراجعة');
    }

    /**
     * إجراء فحص حيوية (API)
     */
    public function performLivenessCheck(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'check_type' => 'required|in:face,blink,smile,turn_head,random',
            'image_data' => 'required|string',
        ]);

        $employee = \App\Models\Employee::findOrFail($validated['employee_id']);

        // التحقق من الجهاز أولاً
        $deviceCheck = $this->livenessService->verifyDevice($request);
        
        if (!$deviceCheck['is_valid']) {
            return response()->json([
                'success' => false,
                'message' => 'تم رفض الجهاز',
                'issues' => $deviceCheck['issues'],
            ], 403);
        }

        $check = $this->livenessService->performCheck(
            $employee,
            $validated['check_type'],
            ['base64' => $validated['image_data']],
            $request
        );

        return response()->json([
            'success' => $check->passed,
            'message' => $check->passed ? 'تم التحقق بنجاح' : 'فشل التحقق',
            'data' => [
                'passed' => $check->passed,
                'confidence' => $check->confidence_score,
                'is_spoofing' => $check->is_spoofing_attempt,
            ],
        ]);
    }

    /**
     * التحقق من الموقع (API)
     */
    public function verifyLocation(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'expected_latitude' => 'required|numeric',
            'expected_longitude' => 'required|numeric',
        ]);

        $employee = \App\Models\Employee::findOrFail($validated['employee_id']);

        $tamper = $this->tamperService->detectGPSSpoofing(
            $request,
            $employee,
            ['lat' => $validated['latitude'], 'lng' => $validated['longitude']],
            ['lat' => $validated['expected_latitude'], 'lng' => $validated['expected_longitude']]
        );

        if ($tamper) {
            return response()->json([
                'success' => false,
                'message' => 'تم اكتشاف تزوير في الموقع',
                'tamper_id' => $tamper->id,
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'الموقع صحيح',
        ]);
    }

    /**
     * إحصائيات الأمان (API)
     */
    public function getStats()
    {
        $user = auth()->user();
        $companyUserIds = $user->getAllCompanyUserIds();

        return response()->json([
            'success' => true,
            'data' => [
                'liveness' => $this->livenessService->getStats($companyUserIds),
                'tamper' => $this->tamperService->getStats($companyUserIds),
            ],
        ]);
    }
}
