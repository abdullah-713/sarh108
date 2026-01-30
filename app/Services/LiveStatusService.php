<?php

namespace App\Services;

use App\Models\EmployeeStatusLog;
use App\Models\QuickCheckin;
use App\Models\Employee;
use App\Models\Branch;
use Carbon\Carbon;

class LiveStatusService
{
    /**
     * الحصول على حالة الموظفين الحية
     */
    public function getEmployeesStatus(array $companyUserIds, ?int $branchId = null): array
    {
        $query = Employee::whereIn('created_by', $companyUserIds)
            ->with(['branch', 'department', 'designation']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $employees = $query->get();

        return $employees->map(function ($employee) {
            return $this->getEmployeeStatus($employee);
        })->toArray();
    }

    /**
     * الحصول على حالة موظف واحد
     */
    public function getEmployeeStatus(Employee $employee): array
    {
        $todayLog = EmployeeStatusLog::getTodayLog($employee->id);
        $todayCheckin = QuickCheckin::where('employee_id', $employee->id)
            ->where('type', 'checkin')
            ->whereDate('checked_at', today())
            ->first();
        $todayCheckout = QuickCheckin::where('employee_id', $employee->id)
            ->where('type', 'checkout')
            ->whereDate('checked_at', today())
            ->first();

        return [
            'id' => $employee->id,
            'name' => $employee->name,
            'employee_id' => $employee->employee_id,
            'branch' => $employee->branch?->name,
            'branch_id' => $employee->branch_id,
            'department' => $employee->department?->name,
            'designation' => $employee->designation?->name,
            'avatar' => $employee->avatar,
            'status' => $todayLog?->status ?? 'absent',
            'status_color' => $this->getStatusColor($todayLog?->status ?? 'absent'),
            'status_label' => $this->getStatusLabel($todayLog?->status ?? 'absent'),
            'checkin_time' => $todayCheckin?->checked_at?->format('H:i'),
            'checkout_time' => $todayCheckout?->checked_at?->format('H:i'),
            'late_minutes' => $todayLog?->late_minutes ?? 0,
            'worked_minutes' => $todayLog?->worked_minutes ?? 0,
            'is_verified' => $todayCheckin?->is_verified ?? false,
            'verification_method' => $todayCheckin?->verification_method,
            'is_perfect_day' => $todayLog?->is_perfect_day ?? false,
        ];
    }

    /**
     * الحصول على إحصائيات الفرع
     */
    public function getBranchStats(int $branchId): array
    {
        $employees = Employee::where('branch_id', $branchId)->get();
        $totalEmployees = $employees->count();

        $presentCount = 0;
        $lateCount = 0;
        $absentCount = 0;
        $onLeaveCount = 0;
        $totalLateMinutes = 0;

        foreach ($employees as $employee) {
            $todayLog = EmployeeStatusLog::getTodayLog($employee->id);
            
            if (!$todayLog) {
                $absentCount++;
                continue;
            }

            switch ($todayLog->status) {
                case 'present':
                    $presentCount++;
                    break;
                case 'late':
                    $lateCount++;
                    $totalLateMinutes += $todayLog->late_minutes;
                    break;
                case 'absent':
                    $absentCount++;
                    break;
                case 'on_leave':
                    $onLeaveCount++;
                    break;
            }
        }

        $attendanceRate = $totalEmployees > 0 
            ? round((($presentCount + $lateCount) / $totalEmployees) * 100, 2) 
            : 0;

        return [
            'total_employees' => $totalEmployees,
            'present' => $presentCount,
            'late' => $lateCount,
            'absent' => $absentCount,
            'on_leave' => $onLeaveCount,
            'total_late_minutes' => $totalLateMinutes,
            'attendance_rate' => $attendanceRate,
            'punctuality_rate' => $totalEmployees > 0 
                ? round(($presentCount / $totalEmployees) * 100, 2) 
                : 0,
        ];
    }

    /**
     * الحصول على لون الحالة
     */
    private function getStatusColor(string $status): string
    {
        return match ($status) {
            'present' => 'green',
            'late' => 'orange',
            'absent' => 'red',
            'on_leave' => 'blue',
            'holiday' => 'purple',
            default => 'gray',
        };
    }

    /**
     * الحصول على اسم الحالة
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'present' => 'حاضر',
            'late' => 'متأخر',
            'absent' => 'غائب',
            'on_leave' => 'إجازة',
            'holiday' => 'عطلة',
            default => 'غير محدد',
        };
    }

    /**
     * الحصول على الموظفين المتأخرين اليوم
     */
    public function getTodayLateEmployees(array $companyUserIds): array
    {
        return EmployeeStatusLog::where('date', today())
            ->where('status', 'late')
            ->whereHas('employee', function ($query) use ($companyUserIds) {
                $query->whereIn('created_by', $companyUserIds);
            })
            ->with('employee.branch')
            ->orderBy('late_minutes', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'employee_id' => $log->employee_id,
                    'employee_name' => $log->employee?->name,
                    'branch' => $log->employee?->branch?->name,
                    'checkin_time' => $log->checkin_time?->format('H:i'),
                    'late_minutes' => $log->late_minutes,
                    'deduction_points' => $log->deduction_points,
                ];
            })
            ->toArray();
    }

    /**
     * الحصول على الموظفين الغائبين اليوم
     */
    public function getTodayAbsentEmployees(array $companyUserIds): array
    {
        $allEmployees = Employee::whereIn('created_by', $companyUserIds)->pluck('id');
        
        $presentEmployees = EmployeeStatusLog::where('date', today())
            ->whereIn('status', ['present', 'late', 'on_leave', 'holiday'])
            ->pluck('employee_id');

        $absentIds = $allEmployees->diff($presentEmployees);

        return Employee::whereIn('id', $absentIds)
            ->with('branch')
            ->get()
            ->map(function ($employee) {
                return [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'branch' => $employee->branch?->name,
                ];
            })
            ->toArray();
    }
}
