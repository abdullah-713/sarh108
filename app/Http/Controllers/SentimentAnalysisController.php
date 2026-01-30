<?php

namespace App\Http\Controllers;

use App\Models\SentimentAnalysis;
use App\Services\SentimentAnalysisService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SentimentAnalysisController extends Controller
{
    protected SentimentAnalysisService $sentimentService;

    public function __construct(SentimentAnalysisService $sentimentService)
    {
        $this->sentimentService = $sentimentService;
    }

    /**
     * عرض لوحة تحليل المشاعر
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $companyUserIds = $user->getAllCompanyUserIds();

        // ملخص الشركة
        $summary = $this->sentimentService->getCompanySummary($companyUserIds);

        // التحليلات المقلقة
        $concerningAnalyses = $this->sentimentService->getConcerningAnalyses($companyUserIds, 10);

        // التحليلات التي تحتاج متابعة
        $requiresFollowup = $this->sentimentService->getEmployeesRequiringFollowup($companyUserIds);

        // التوزيع حسب الفرع
        $byBranch = $this->sentimentService->getSentimentByBranch($companyUserIds);

        // التوزيع حسب القسم
        $byDepartment = $this->sentimentService->getSentimentByDepartment($companyUserIds);

        // الاتجاه
        $trend = $this->sentimentService->getSentimentTrend($companyUserIds, 30);

        return Inertia::render('ai/sentiment-analysis', [
            'summary' => $summary,
            'concerningAnalyses' => $concerningAnalyses,
            'requiresFollowup' => $requiresFollowup,
            'byBranch' => $byBranch,
            'byDepartment' => $byDepartment,
            'trend' => $trend,
        ]);
    }

    /**
     * تشغيل التحليل الأسبوعي
     */
    public function runAnalysis()
    {
        $user = auth()->user();
        $companyUserIds = $user->getAllCompanyUserIds();

        $results = $this->sentimentService->runWeeklyAnalysis($companyUserIds);

        return response()->json([
            'success' => true,
            'message' => "تم تحليل {$results['total_analyzed']} موظف",
            'data' => $results,
        ]);
    }

    /**
     * تحليل موظف واحد
     */
    public function analyzeEmployee($employeeId)
    {
        $employee = \App\Models\Employee::findOrFail($employeeId);
        $analysis = $this->sentimentService->analyzeEmployee($employee);

        return response()->json([
            'success' => true,
            'data' => $analysis,
        ]);
    }

    /**
     * الحصول على تحليلات موظف
     */
    public function getEmployeeAnalyses($employeeId)
    {
        $analyses = SentimentAnalysis::forEmployee($employeeId)
            ->orderBy('analysis_date', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $analyses,
        ]);
    }

    /**
     * تعيين مسؤول للمتابعة
     */
    public function assignFollowup(Request $request, SentimentAnalysis $sentimentAnalysis)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'followup_date' => 'nullable|date',
        ]);

        $this->sentimentService->assignFollowup(
            $sentimentAnalysis,
            $validated['user_id'],
            $validated['followup_date'] ?? null
        );

        return redirect()->back()->with('success', 'تم تعيين المسؤول');
    }

    /**
     * تحديث حالة المتابعة
     */
    public function updateFollowup(Request $request, SentimentAnalysis $sentimentAnalysis)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        $this->sentimentService->updateFollowupStatus(
            $sentimentAnalysis,
            $validated['status'],
            $validated['notes'] ?? null
        );

        return redirect()->back()->with('success', 'تم تحديث الحالة');
    }

    /**
     * إنشاء تحليل يدوي
     */
    public function createManual(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'sentiment' => 'required|in:very_positive,positive,neutral,negative,very_negative',
            'score' => 'required|numeric|min:0|max:100',
            'concerns_summary' => 'nullable|string',
            'requires_followup' => 'boolean',
        ]);

        $analysis = $this->sentimentService->createManualAnalysis(
            $validated['employee_id'],
            $validated['sentiment'],
            $validated['score'],
            [
                'concerns_summary' => $validated['concerns_summary'] ?? null,
                'requires_followup' => $validated['requires_followup'] ?? false,
            ]
        );

        return redirect()->back()->with('success', 'تم إنشاء التحليل');
    }

    /**
     * الحصول على الاتجاه (API)
     */
    public function getTrend(Request $request)
    {
        $user = auth()->user();
        $companyUserIds = $user->getAllCompanyUserIds();

        $days = $request->get('days', 30);
        $trend = $this->sentimentService->getSentimentTrend($companyUserIds, $days);

        return response()->json([
            'success' => true,
            'data' => $trend,
        ]);
    }
}
