<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeStatusLog;
use App\Models\NewsTicker;
use Carbon\Carbon;

class MVPService
{
    /**
     * حساب نقاط MVP للموظف
     */
    public function calculateMVPScore($employeeId, $startDate = null, $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfMonth();

        $logs = EmployeeStatusLog::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        if ($logs->isEmpty()) {
            return [
                'score' => 0,
                'breakdown' => [],
            ];
        }

        $totalDays = $logs->count();
        $presentDays = $logs->where('status', 'present')->count();
        $lateDays = $logs->where('status', 'late')->count();
        $onTimeDays = $logs->where('status', 'present')->where('late_minutes', 0)->count();
        $earlyDays = $logs->where('is_early', true)->count();
        $totalLateMinutes = $logs->sum('late_minutes');

        // حساب النقاط
        $attendanceScore = ($presentDays + $lateDays) / max(1, $totalDays) * 30; // 30 نقطة للحضور
        $punctualityScore = $onTimeDays / max(1, $totalDays) * 35; // 35 نقطة للالتزام
        $earlyScore = $earlyDays / max(1, $totalDays) * 15; // 15 نقطة للوصول المبكر
        
        // خصم التأخير (حد أقصى 10 نقاط)
        $latePenalty = min(10, $totalLateMinutes / 60);
        
        // مكافأة السلسلة
        $employee = Employee::find($employeeId);
        $streakBonus = min(10, ($employee?->current_streak ?? 0) / 3); // 10 نقاط للسلسلة

        $totalScore = max(0, $attendanceScore + $punctualityScore + $earlyScore + $streakBonus - $latePenalty);

        return [
            'score' => round($totalScore, 2),
            'breakdown' => [
                'attendance_score' => round($attendanceScore, 2),
                'punctuality_score' => round($punctualityScore, 2),
                'early_score' => round($earlyScore, 2),
                'streak_bonus' => round($streakBonus, 2),
                'late_penalty' => round($latePenalty, 2),
            ],
            'stats' => [
                'total_days' => $totalDays,
                'present_days' => $presentDays,
                'late_days' => $lateDays,
                'on_time_days' => $onTimeDays,
                'early_days' => $earlyDays,
                'total_late_minutes' => $totalLateMinutes,
            ],
        ];
    }

    /**
     * الحصول على ترتيب MVP
     */
    public function getMVPRanking($limit = 10, $startDate = null, $endDate = null, $companyUserIds = null): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfMonth();

        $query = Employee::where('status', 'active');

        if ($companyUserIds) {
            $query->whereIn('created_by', $companyUserIds);
        }

        $employees = $query->get();

        $rankings = [];
        foreach ($employees as $employee) {
            $mvpData = $this->calculateMVPScore($employee->id, $startDate, $endDate);
            $rankings[] = [
                'employee_id' => $employee->id,
                'name' => $employee->name,
                'avatar' => $employee->avatar,
                'branch' => $employee->branch?->name,
                'department' => $employee->department?->name,
                'score' => $mvpData['score'],
                'breakdown' => $mvpData['breakdown'],
                'stats' => $mvpData['stats'],
                'current_streak' => $employee->current_streak,
                'total_badges' => $employee->total_badges,
            ];
        }

        // ترتيب حسب النقاط
        usort($rankings, fn($a, $b) => $b['score'] <=> $a['score']);

        // إضافة الترتيب
        $rankings = array_slice($rankings, 0, $limit);
        foreach ($rankings as $index => &$ranking) {
            $ranking['rank'] = $index + 1;
        }

        return $rankings;
    }

    /**
     * تحديد MVP الشهر
     */
    public function selectMonthlyMVP($companyUserIds = null): ?array
    {
        $rankings = $this->getMVPRanking(1, null, null, $companyUserIds);

        if (empty($rankings)) {
            return null;
        }

        $mvp = $rankings[0];
        $period = Carbon::now()->format('Y-m');

        // إنشاء خبر
        NewsTicker::createMVPNews($mvp['name'], Carbon::now()->format('F Y'));

        return $mvp;
    }

    /**
     * الحصول على العشرة الأوائل
     */
    public function getTopTen($companyUserIds = null): array
    {
        return $this->getMVPRanking(10, null, null, $companyUserIds);
    }

    /**
     * الحصول على أداء الموظف
     */
    public function getEmployeePerformance($employeeId): array
    {
        $employee = Employee::with(['branch', 'department'])->find($employeeId);
        
        if (!$employee) {
            return [];
        }

        // بيانات الشهر الحالي
        $currentMonth = $this->calculateMVPScore($employeeId);

        // بيانات الشهر السابق
        $lastMonth = $this->calculateMVPScore(
            $employeeId,
            Carbon::now()->subMonth()->startOfMonth(),
            Carbon::now()->subMonth()->endOfMonth()
        );

        // التغير
        $scoreChange = $currentMonth['score'] - $lastMonth['score'];

        return [
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'avatar' => $employee->avatar,
                'branch' => $employee->branch?->name,
                'department' => $employee->department?->name,
            ],
            'current_month' => $currentMonth,
            'last_month' => $lastMonth,
            'score_change' => round($scoreChange, 2),
            'trend' => $scoreChange > 0 ? 'up' : ($scoreChange < 0 ? 'down' : 'stable'),
            'streak' => [
                'current' => $employee->current_streak,
                'longest' => $employee->longest_streak,
            ],
            'badges' => $employee->total_badges,
            'mvp_points' => $employee->mvp_points,
        ];
    }
}
