<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeStatusLog;
use App\Models\NewsTicker;
use Carbon\Carbon;

class StreakService
{
    /**
     * تحديث سلسلة الموظف
     */
    public function updateEmployeeStreak($employeeId): void
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return;
        }

        $today = Carbon::today();
        $todayLog = EmployeeStatusLog::where('employee_id', $employeeId)
            ->where('date', $today)
            ->first();

        // إذا لم يكن هناك سجل لليوم، لا نفعل شيء
        if (!$todayLog) {
            return;
        }

        // التحقق من الحضور المثالي (حاضر بدون تأخير)
        $isPerfectDay = $todayLog->status === 'present' && $todayLog->late_minutes === 0;

        if ($isPerfectDay) {
            // التحقق من اليوم السابق
            $lastAttendanceDate = $employee->last_attendance_date;
            
            if ($lastAttendanceDate && Carbon::parse($lastAttendanceDate)->isYesterday()) {
                // استمرار السلسلة
                $employee->current_streak++;
            } else {
                // بدء سلسلة جديدة
                $employee->current_streak = 1;
                $employee->streak_start_date = $today;
            }

            // تحديث أطول سلسلة
            if ($employee->current_streak > $employee->longest_streak) {
                $employee->longest_streak = $employee->current_streak;

                // إذا حقق رقماً قياسياً جديداً (كل 10 أيام)
                if ($employee->longest_streak % 10 === 0) {
                    NewsTicker::createStreakNews($employee->name, $employee->longest_streak);
                }
            }

            $employee->last_attendance_date = $today;
        } else {
            // كسر السلسلة
            $employee->current_streak = 0;
            $employee->streak_start_date = null;
        }

        $employee->save();
    }

    /**
     * تحديث سلاسل جميع الموظفين
     */
    public function updateAllStreaks(): int
    {
        $employees = Employee::where('status', 'active')->get();
        $updated = 0;

        foreach ($employees as $employee) {
            $this->updateEmployeeStreak($employee->id);
            $updated++;
        }

        return $updated;
    }

    /**
     * إعادة تعيين السلاسل المنقطعة
     */
    public function resetBrokenStreaks(): int
    {
        $yesterday = Carbon::yesterday();
        $reset = 0;

        // الموظفون الذين لم يسجلوا حضوراً مثالياً أمس
        $employees = Employee::where('status', 'active')
            ->where('current_streak', '>', 0)
            ->get();

        foreach ($employees as $employee) {
            $yesterdayLog = EmployeeStatusLog::where('employee_id', $employee->id)
                ->where('date', $yesterday)
                ->first();

            // إذا لم يكن هناك سجل أو كان هناك تأخير/غياب
            if (!$yesterdayLog || $yesterdayLog->status !== 'present' || $yesterdayLog->late_minutes > 0) {
                // لا نكسر السلسلة إذا كان في إجازة أو عطلة
                if (!$yesterdayLog || !in_array($yesterdayLog->status, ['on_leave', 'holiday'])) {
                    $employee->update([
                        'current_streak' => 0,
                        'streak_start_date' => null,
                    ]);
                    $reset++;
                }
            }
        }

        return $reset;
    }

    /**
     * الحصول على أعلى السلاسل
     */
    public function getTopStreaks($limit = 10, $companyUserIds = null): array
    {
        $query = Employee::where('status', 'active')
            ->where('current_streak', '>', 0)
            ->orderBy('current_streak', 'desc')
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
                'current_streak' => $employee->current_streak,
                'longest_streak' => $employee->longest_streak,
                'streak_start_date' => $employee->streak_start_date?->format('Y-m-d'),
            ];
        })->toArray();
    }

    /**
     * الحصول على الأرقام القياسية
     */
    public function getRecordBreakers($limit = 5, $companyUserIds = null): array
    {
        $query = Employee::where('status', 'active')
            ->orderBy('longest_streak', 'desc')
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
                'longest_streak' => $employee->longest_streak,
                'current_streak' => $employee->current_streak,
            ];
        })->toArray();
    }
}
