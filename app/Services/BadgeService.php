<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\EmployeeBadge;
use App\Models\Employee;
use App\Models\EmployeeStatusLog;
use App\Models\NewsTicker;
use Carbon\Carbon;

class BadgeService
{
    /**
     * فحص ومنح الشارات التلقائية
     */
    public function checkAndAwardBadges($employeeId): array
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return [];
        }

        $awardedBadges = [];
        $badges = Badge::where('is_active', true)
            ->where('is_auto_award', true)
            ->get();

        foreach ($badges as $badge) {
            if ($this->qualifiesForBadge($employee, $badge)) {
                $period = Carbon::now()->format('Y-m');
                
                // التحقق من عدم امتلاك الشارة لهذه الفترة
                if (!EmployeeBadge::hasBadge($employeeId, $badge->id, $period)) {
                    EmployeeBadge::awardBadge($employeeId, $badge->id, $period);
                    $awardedBadges[] = $badge;

                    // إنشاء خبر
                    NewsTicker::createBadgeNews($employee->name, $badge->name_ar);

                    // تحديث عداد الشارات
                    $employee->increment('total_badges');
                    $employee->increment('mvp_points', $badge->points);
                }
            }
        }

        return $awardedBadges;
    }

    /**
     * التحقق من استحقاق الشارة
     */
    public function qualifiesForBadge(Employee $employee, Badge $badge): bool
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        switch ($badge->type) {
            case 'punctuality':
                $onTimeDays = EmployeeStatusLog::where('employee_id', $employee->id)
                    ->whereBetween('date', [$startOfMonth, $endOfMonth])
                    ->where('status', 'present')
                    ->where('late_minutes', 0)
                    ->count();
                return $onTimeDays >= ($badge->required_days ?? 20);

            case 'early_bird':
                $earlyDays = EmployeeStatusLog::where('employee_id', $employee->id)
                    ->whereBetween('date', [$startOfMonth, $endOfMonth])
                    ->where('is_early', true)
                    ->count();
                return $earlyDays >= ($badge->required_days ?? 10);

            case 'streak':
                return $employee->current_streak >= ($badge->required_streak ?? 30);

            case 'perfect_month':
                $totalDays = EmployeeStatusLog::where('employee_id', $employee->id)
                    ->whereBetween('date', [$startOfMonth, $endOfMonth])
                    ->count();
                $perfectDays = EmployeeStatusLog::where('employee_id', $employee->id)
                    ->whereBetween('date', [$startOfMonth, $endOfMonth])
                    ->where('status', 'present')
                    ->where('late_minutes', 0)
                    ->count();
                
                if ($totalDays === 0) return false;
                $rate = ($perfectDays / $totalDays) * 100;
                return $rate >= ($badge->required_rate ?? 100);

            case 'attendance':
                $attendedDays = EmployeeStatusLog::where('employee_id', $employee->id)
                    ->whereBetween('date', [$startOfMonth, $endOfMonth])
                    ->whereIn('status', ['present', 'late'])
                    ->count();
                return $attendedDays >= ($badge->required_days ?? 22);

            default:
                return false;
        }
    }

    /**
     * منح شارة يدوياً
     */
    public function awardBadgeManually($employeeId, $badgeId, $reason = null, $awardedBy = null): ?EmployeeBadge
    {
        $employee = Employee::find($employeeId);
        $badge = Badge::find($badgeId);

        if (!$employee || !$badge) {
            return null;
        }

        $period = Carbon::now()->format('Y-m');
        $employeeBadge = EmployeeBadge::awardBadge($employeeId, $badgeId, $period, $reason, $awardedBy);

        // إنشاء خبر
        NewsTicker::createBadgeNews($employee->name, $badge->name_ar);

        // تحديث عداد الشارات
        $employee->increment('total_badges');
        $employee->increment('mvp_points', $badge->points);

        return $employeeBadge;
    }

    /**
     * الحصول على شارات الموظف
     */
    public function getEmployeeBadges($employeeId): array
    {
        return EmployeeBadge::getEmployeeBadges($employeeId)->map(function ($eb) {
            return [
                'id' => $eb->id,
                'badge' => [
                    'id' => $eb->badge->id,
                    'name' => $eb->badge->name,
                    'name_ar' => $eb->badge->name_ar,
                    'icon' => $eb->badge->icon,
                    'color' => $eb->badge->color,
                    'tier' => $eb->badge->tier,
                    'tier_name' => $eb->badge->tier_name,
                    'tier_color' => $eb->badge->tier_color,
                    'points' => $eb->badge->points,
                ],
                'awarded_date' => $eb->awarded_date->format('Y-m-d'),
                'period' => $eb->period,
                'reason' => $eb->reason,
            ];
        })->toArray();
    }

    /**
     * الحصول على لوحة الصدارة
     */
    public function getLeaderboard($limit = 10, $companyUserIds = null): array
    {
        $query = Employee::where('status', 'active')
            ->orderBy('mvp_points', 'desc')
            ->orderBy('total_badges', 'desc')
            ->limit($limit);

        if ($companyUserIds) {
            $query->whereIn('created_by', $companyUserIds);
        }

        return $query->get()->map(function ($employee, $index) {
            return [
                'rank' => $index + 1,
                'employee_id' => $employee->id,
                'name' => $employee->name,
                'avatar' => $employee->avatar,
                'branch' => $employee->branch?->name,
                'mvp_points' => $employee->mvp_points,
                'total_badges' => $employee->total_badges,
                'current_streak' => $employee->current_streak,
                'longest_streak' => $employee->longest_streak,
            ];
        })->toArray();
    }
}
