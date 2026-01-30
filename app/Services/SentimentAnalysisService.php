<?php

namespace App\Services;

use App\Models\SentimentAnalysis;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SentimentAnalysisService
{
    /**
     * تحليل المشاعر لجميع الموظفين
     */
    public function analyzeAllEmployees(array $companyUserIds): Collection
    {
        $analyses = collect();

        $employees = Employee::whereIn('created_by', $companyUserIds)
            ->where('status', 'active')
            ->get();

        foreach ($employees as $employee) {
            $analysis = SentimentAnalysis::analyzeAttendancePattern($employee);
            $analyses->push($analysis);
        }

        return $analyses;
    }

    /**
     * تحليل موظف واحد
     */
    public function analyzeEmployee(Employee $employee): SentimentAnalysis
    {
        return SentimentAnalysis::analyzeAttendancePattern($employee);
    }

    /**
     * الحصول على ملخص الشركة
     */
    public function getCompanySummary(array $companyUserIds, $startDate = null, $endDate = null): array
    {
        return SentimentAnalysis::getCompanySummary($companyUserIds, $startDate, $endDate);
    }

    /**
     * الحصول على الموظفين المحتاجين للمتابعة
     */
    public function getEmployeesRequiringFollowup(array $companyUserIds): Collection
    {
        return SentimentAnalysis::whereHas('employee', function ($q) use ($companyUserIds) {
                $q->whereIn('created_by', $companyUserIds);
            })
            ->requiresFollowup()
            ->with(['employee', 'branch', 'department'])
            ->orderBy('sentiment_score')
            ->get();
    }

    /**
     * الحصول على التحليلات المقلقة
     */
    public function getConcerningAnalyses(array $companyUserIds, int $limit = 20): Collection
    {
        return SentimentAnalysis::whereHas('employee', function ($q) use ($companyUserIds) {
                $q->whereIn('created_by', $companyUserIds);
            })
            ->concerning()
            ->with(['employee', 'branch'])
            ->orderBy('sentiment_score')
            ->limit($limit)
            ->get();
    }

    /**
     * تحديث حالة المتابعة
     */
    public function updateFollowupStatus(
        SentimentAnalysis $analysis,
        string $status,
        ?string $notes = null
    ): SentimentAnalysis {
        $analysis->update([
            'followup_status' => $status,
            'followup_notes' => $notes,
        ]);

        return $analysis->fresh();
    }

    /**
     * تعيين مسؤول للمتابعة
     */
    public function assignFollowup(
        SentimentAnalysis $analysis,
        int $userId,
        ?string $followupDate = null
    ): SentimentAnalysis {
        $analysis->update([
            'assigned_to' => $userId,
            'followup_date' => $followupDate ? Carbon::parse($followupDate) : null,
            'followup_status' => 'pending',
        ]);

        return $analysis->fresh();
    }

    /**
     * الحصول على اتجاه المشاعر
     */
    public function getSentimentTrend(array $companyUserIds, int $days = 30): array
    {
        $trend = [];
        
        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            
            $dayAnalyses = SentimentAnalysis::whereHas('employee', function ($q) use ($companyUserIds) {
                    $q->whereIn('created_by', $companyUserIds);
                })
                ->whereDate('analysis_date', $date)
                ->get();

            if ($dayAnalyses->isNotEmpty()) {
                $trend[] = [
                    'date' => $date->format('Y-m-d'),
                    'average_score' => round($dayAnalyses->avg('sentiment_score'), 2),
                    'count' => $dayAnalyses->count(),
                    'concerning_count' => $dayAnalyses->where('is_concerning', true)->count(),
                ];
            }
        }

        return $trend;
    }

    /**
     * الحصول على توزيع المشاعر حسب الفرع
     */
    public function getSentimentByBranch(array $companyUserIds): array
    {
        return SentimentAnalysis::whereHas('employee', function ($q) use ($companyUserIds) {
                $q->whereIn('created_by', $companyUserIds);
            })
            ->whereDate('analysis_date', '>=', Carbon::now()->subDays(30))
            ->selectRaw('branch_id, 
                AVG(sentiment_score) as avg_score, 
                COUNT(*) as count,
                SUM(CASE WHEN is_concerning = 1 THEN 1 ELSE 0 END) as concerning_count')
            ->groupBy('branch_id')
            ->with('branch:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'branch_id' => $item->branch_id,
                    'branch_name' => $item->branch?->name ?? 'غير محدد',
                    'average_score' => round($item->avg_score, 2),
                    'count' => $item->count,
                    'concerning_count' => $item->concerning_count,
                ];
            })
            ->toArray();
    }

    /**
     * الحصول على توزيع المشاعر حسب القسم
     */
    public function getSentimentByDepartment(array $companyUserIds): array
    {
        return SentimentAnalysis::whereHas('employee', function ($q) use ($companyUserIds) {
                $q->whereIn('created_by', $companyUserIds);
            })
            ->whereDate('analysis_date', '>=', Carbon::now()->subDays(30))
            ->selectRaw('department_id, 
                AVG(sentiment_score) as avg_score, 
                COUNT(*) as count')
            ->groupBy('department_id')
            ->with('department:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'department_id' => $item->department_id,
                    'department_name' => $item->department?->name ?? 'غير محدد',
                    'average_score' => round($item->avg_score, 2),
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * إنشاء تحليل يدوي
     */
    public function createManualAnalysis(
        int $employeeId,
        string $sentiment,
        float $score,
        array $data = []
    ): SentimentAnalysis {
        $employee = Employee::findOrFail($employeeId);

        return SentimentAnalysis::create([
            'employee_id' => $employeeId,
            'branch_id' => $employee->branch_id,
            'department_id' => $employee->department_id,
            'source_type' => 'manual_entry',
            'sentiment' => $sentiment,
            'sentiment_score' => $score,
            'confidence_score' => 100,
            'emotions' => $data['emotions'] ?? null,
            'primary_emotion' => $data['primary_emotion'] ?? null,
            'is_concerning' => in_array($sentiment, ['negative', 'very_negative']),
            'concerns_summary' => $data['concerns_summary'] ?? null,
            'recommendations' => $data['recommendations'] ?? null,
            'requires_followup' => $data['requires_followup'] ?? false,
            'analysis_date' => Carbon::today(),
            'period_type' => 'daily',
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * تشغيل التحليل الأسبوعي
     */
    public function runWeeklyAnalysis(array $companyUserIds): array
    {
        $analyses = $this->analyzeAllEmployees($companyUserIds);

        $concerningCount = $analyses->where('is_concerning', true)->count();
        $followupRequired = $analyses->where('requires_followup', true)->count();

        return [
            'total_analyzed' => $analyses->count(),
            'concerning_count' => $concerningCount,
            'followup_required' => $followupRequired,
            'average_score' => round($analyses->avg('sentiment_score'), 2),
            'positive_count' => $analyses->whereIn('sentiment', ['positive', 'very_positive'])->count(),
            'negative_count' => $analyses->whereIn('sentiment', ['negative', 'very_negative'])->count(),
        ];
    }
}
