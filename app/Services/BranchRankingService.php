<?php

namespace App\Services;

use App\Models\BranchPerformance;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeStatusLog;
use Carbon\Carbon;

class BranchRankingService
{
    /**
     * حساب أداء جميع الفروع ليوم معين
     */
    public function calculateDailyPerformance($date = null, $companyUserIds = null): void
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();

        $query = Branch::query();
        if ($companyUserIds) {
            $query->whereIn('created_by', $companyUserIds);
        }

        $branches = $query->get();

        foreach ($branches as $branch) {
            $this->calculateBranchPerformance($branch, $date);
        }

        // تحديث الترتيب
        BranchPerformance::updateRankings($date);
    }

    /**
     * حساب أداء فرع واحد
     */
    public function calculateBranchPerformance(Branch $branch, Carbon $date): BranchPerformance
    {
        $employees = Employee::where('branch_id', $branch->id)
            ->where('status', 'active')
            ->get();

        $totalEmployees = $employees->count();

        if ($totalEmployees === 0) {
            return $this->createEmptyPerformance($branch->id, $date);
        }

        $presentCount = 0;
        $lateCount = 0;
        $absentCount = 0;
        $onLeaveCount = 0;
        $earlyArrivalCount = 0;
        $totalLateMinutes = 0;

        foreach ($employees as $employee) {
            $log = EmployeeStatusLog::where('employee_id', $employee->id)
                ->where('date', $date)
                ->first();

            if (!$log) {
                $absentCount++;
                continue;
            }

            switch ($log->status) {
                case 'present':
                    $presentCount++;
                    if ($log->is_early) {
                        $earlyArrivalCount++;
                    }
                    break;
                case 'late':
                    $lateCount++;
                    $totalLateMinutes += $log->late_minutes;
                    break;
                case 'absent':
                    $absentCount++;
                    break;
                case 'on_leave':
                case 'holiday':
                    $onLeaveCount++;
                    break;
            }
        }

        // حساب النسب
        $workingEmployees = $totalEmployees - $onLeaveCount;
        $attendanceRate = $workingEmployees > 0 
            ? (($presentCount + $lateCount) / $workingEmployees) * 100 
            : 0;
        $punctualityRate = $workingEmployees > 0 
            ? ($presentCount / $workingEmployees) * 100 
            : 0;
        $earlyArrivalRate = $workingEmployees > 0 
            ? ($earlyArrivalCount / $workingEmployees) * 100 
            : 0;
        $avgLateMinutes = $lateCount > 0 
            ? $totalLateMinutes / $lateCount 
            : 0;

        // حساب الأيام المثالية والاستمرارية
        $isPerfectDay = $lateCount === 0 && $absentCount === 0;
        $previousPerformance = BranchPerformance::where('branch_id', $branch->id)
            ->where('date', $date->copy()->subDay())
            ->first();

        $perfectDaysCount = $previousPerformance 
            ? ($isPerfectDay ? $previousPerformance->perfect_days_count + 1 : 0) 
            : ($isPerfectDay ? 1 : 0);
        
        $streakDays = $previousPerformance && $previousPerformance->attendance_rate >= 95
            ? $previousPerformance->streak_days + 1
            : ($attendanceRate >= 95 ? 1 : 0);

        $data = [
            'attendance_rate' => $attendanceRate,
            'punctuality_rate' => $punctualityRate,
            'early_arrival_rate' => $earlyArrivalRate,
            'perfect_days_count' => $perfectDaysCount,
        ];

        $performanceScore = BranchPerformance::calculateScore($data);

        return BranchPerformance::updateOrCreate(
            ['branch_id' => $branch->id, 'date' => $date],
            [
                'total_employees' => $totalEmployees,
                'present_count' => $presentCount,
                'late_count' => $lateCount,
                'absent_count' => $absentCount,
                'on_leave_count' => $onLeaveCount,
                'attendance_rate' => round($attendanceRate, 2),
                'punctuality_rate' => round($punctualityRate, 2),
                'early_arrival_rate' => round($earlyArrivalRate, 2),
                'total_late_minutes' => $totalLateMinutes,
                'avg_late_minutes' => round($avgLateMinutes, 2),
                'performance_score' => $performanceScore,
                'perfect_days_count' => $perfectDaysCount,
                'streak_days' => $streakDays,
            ]
        );
    }

    /**
     * إنشاء أداء فارغ
     */
    private function createEmptyPerformance($branchId, Carbon $date): BranchPerformance
    {
        return BranchPerformance::updateOrCreate(
            ['branch_id' => $branchId, 'date' => $date],
            [
                'total_employees' => 0,
                'present_count' => 0,
                'late_count' => 0,
                'absent_count' => 0,
                'on_leave_count' => 0,
                'attendance_rate' => 0,
                'punctuality_rate' => 0,
                'early_arrival_rate' => 0,
                'total_late_minutes' => 0,
                'avg_late_minutes' => 0,
                'performance_score' => 0,
                'perfect_days_count' => 0,
                'streak_days' => 0,
            ]
        );
    }

    /**
     * الحصول على ترتيب الفروع
     */
    public function getRanking($date = null, $companyUserIds = null): array
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();

        $rankings = BranchPerformance::getRankingForDate($date, $companyUserIds);

        return $rankings->map(function ($performance) {
            return [
                'branch_id' => $performance->branch_id,
                'branch_name' => $performance->branch?->name,
                'rank' => $performance->rank,
                'rank_change' => $performance->rank_change,
                'rank_color' => $performance->rank_color,
                'performance_score' => $performance->performance_score,
                'attendance_rate' => $performance->attendance_rate,
                'punctuality_rate' => $performance->punctuality_rate,
                'total_employees' => $performance->total_employees,
                'present_count' => $performance->present_count,
                'late_count' => $performance->late_count,
                'absent_count' => $performance->absent_count,
                'streak_days' => $performance->streak_days,
            ];
        })->toArray();
    }

    /**
     * الحصول على أفضل الفروع
     */
    public function getTopBranches($limit = 5, $period = 'today', $companyUserIds = null): array
    {
        $query = BranchPerformance::with('branch')
            ->orderBy('performance_score', 'desc')
            ->limit($limit);

        switch ($period) {
            case 'today':
                $query->where('date', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereBetween('date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                break;
        }

        if ($companyUserIds) {
            $query->whereHas('branch', function ($q) use ($companyUserIds) {
                $q->whereIn('created_by', $companyUserIds);
            });
        }

        return $query->get()->toArray();
    }
}
