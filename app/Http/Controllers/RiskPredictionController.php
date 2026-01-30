<?php

namespace App\Http\Controllers;

use App\Models\RiskPrediction;
use App\Services\RiskPredictionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RiskPredictionController extends Controller
{
    protected RiskPredictionService $riskService;

    public function __construct(RiskPredictionService $riskService)
    {
        $this->riskService = $riskService;
    }

    /**
     * عرض صفحة توقعات المخاطر
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $companyUserIds = $user->getAllCompanyUserIds();

        $status = $request->get('status', 'pending');
        $riskType = $request->get('risk_type');
        $severity = $request->get('severity');

        $predictions = RiskPrediction::whereHas('employee', function ($q) use ($companyUserIds) {
                $q->whereIn('created_by', $companyUserIds);
            })
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($riskType, fn($q) => $q->where('risk_type', $riskType))
            ->when($severity, fn($q) => $q->where('severity', $severity))
            ->with(['employee:id,first_name,last_name', 'branch:id,name'])
            ->orderBy('risk_score', 'desc')
            ->paginate(20);

        // إحصائيات
        $stats = [
            'total' => RiskPrediction::whereHas('employee', function ($q) use ($companyUserIds) {
                $q->whereIn('created_by', $companyUserIds);
            })->where('status', 'pending')->count(),
            'high_risk' => RiskPrediction::whereHas('employee', function ($q) use ($companyUserIds) {
                $q->whereIn('created_by', $companyUserIds);
            })->where('status', 'pending')->whereIn('severity', ['high', 'critical'])->count(),
            'by_type' => RiskPrediction::whereHas('employee', function ($q) use ($companyUserIds) {
                $q->whereIn('created_by', $companyUserIds);
            })->where('status', 'pending')
                ->selectRaw('risk_type, COUNT(*) as count')
                ->groupBy('risk_type')
                ->pluck('count', 'risk_type'),
        ];

        return Inertia::render('ai/risk-predictions', [
            'predictions' => $predictions,
            'stats' => $stats,
            'filters' => [
                'status' => $status,
                'risk_type' => $riskType,
                'severity' => $severity,
            ],
        ]);
    }

    /**
     * تشغيل تحليل المخاطر
     */
    public function runAnalysis()
    {
        $user = auth()->user();
        $companyUserIds = $user->getAllCompanyUserIds();

        $results = $this->riskService->runAllAnalyses($companyUserIds);

        return response()->json([
            'success' => true,
            'message' => "تم إنشاء {$results['total']} توقع جديد",
            'data' => $results,
        ]);
    }

    /**
     * تحديث حالة التوقع
     */
    public function updateStatus(Request $request, RiskPrediction $riskPrediction)
    {
        $validated = $request->validate([
            'status' => 'required|in:reviewed,acted,resolved,dismissed',
            'notes' => 'nullable|string|max:500',
        ]);

        $this->riskService->updatePredictionStatus(
            $riskPrediction,
            $validated['status'],
            auth()->id(),
            $validated['notes'] ?? null
        );

        return redirect()->back()->with('success', 'تم تحديث حالة التوقع');
    }

    /**
     * تسجيل نتيجة التوقع
     */
    public function recordOutcome(Request $request, RiskPrediction $riskPrediction)
    {
        $validated = $request->validate([
            'was_accurate' => 'required|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $this->riskService->recordOutcome(
            $riskPrediction,
            $validated['was_accurate'],
            $validated['notes'] ?? null
        );

        return redirect()->back()->with('success', 'تم تسجيل النتيجة');
    }

    /**
     * الحصول على توقعات موظف (API)
     */
    public function getEmployeePredictions($employeeId)
    {
        $predictions = RiskPrediction::forEmployee($employeeId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $predictions,
        ]);
    }

    /**
     * لوحة المعلومات
     */
    public function dashboard()
    {
        $user = auth()->user();
        $companyUserIds = $user->getAllCompanyUserIds();

        $upcomingRisks = RiskPrediction::whereHas('employee', function ($q) use ($companyUserIds) {
                $q->whereIn('created_by', $companyUserIds);
            })
            ->pending()
            ->upcoming(7)
            ->with(['employee:id,first_name,last_name', 'branch:id,name'])
            ->orderBy('predicted_date')
            ->limit(10)
            ->get();

        $criticalRisks = RiskPrediction::whereHas('employee', function ($q) use ($companyUserIds) {
                $q->whereIn('created_by', $companyUserIds);
            })
            ->pending()
            ->highRisk()
            ->with(['employee:id,first_name,last_name'])
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'upcoming_risks' => $upcomingRisks,
                'critical_risks' => $criticalRisks,
            ],
        ]);
    }
}
