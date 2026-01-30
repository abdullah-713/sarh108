<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Overtime;
use App\Models\BreakPeriod;
use App\Models\AttendanceAlert;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceReportService
{
    /**
     * Generate daily attendance report.
     */
    public static function generateDailyReport($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();

        $totalEmployees = DB::table('employees')->count();
        
        $attendance = Attendance::whereDate('attendance_date', $date)
            ->select([
                DB::raw('COUNT(DISTINCT employee_id) as total_records'),
                DB::raw('COUNT(DISTINCT CASE WHEN status = "present" THEN employee_id END) as present'),
                DB::raw('COUNT(DISTINCT CASE WHEN is_absent = true THEN employee_id END) as absent'),
                DB::raw('COUNT(DISTINCT CASE WHEN is_late = true THEN employee_id END) as late'),
                DB::raw('AVG(total_hours) as avg_hours'),
                DB::raw('SUM(overtime_hours) as total_overtime'),
            ])
            ->first();

        $breaks = BreakPeriod::whereDate('created_at', $date)
            ->select([
                DB::raw('COUNT(*) as total_breaks'),
                DB::raw('AVG(break_duration) as avg_duration'),
                DB::raw('COUNT(DISTINCT CASE WHEN exceeds_limit = true THEN id END) as exceeds_limit'),
            ])
            ->first();

        $alerts = AttendanceAlert::whereDate('alert_time', $date)
            ->select([
                DB::raw('COUNT(*) as total'),
                DB::raw('COUNT(DISTINCT CASE WHEN severity = "critical" THEN id END) as critical'),
                DB::raw('COUNT(DISTINCT CASE WHEN is_resolved = false THEN id END) as unresolved'),
            ])
            ->first();

        return [
            'date' => $date->format('Y-m-d'),
            'summary' => [
                'total_employees' => $totalEmployees,
                'attendance_rate' => $totalEmployees > 0 ? round(($attendance->present / $totalEmployees) * 100, 2) : 0,
            ],
            'attendance' => $attendance,
            'breaks' => $breaks,
            'alerts' => $alerts,
        ];
    }

    /**
     * Generate weekly attendance report.
     */
    public static function generateWeeklyReport($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();

        $dailyStats = [];
        $currentDate = $startOfWeek->copy();

        while ($currentDate->lte($endOfWeek)) {
            $dailyStats[] = self::generateDailyReport($currentDate);
            $currentDate->addDay();
        }

        $totalAttendance = Attendance::whereBetween('attendance_date', [$startOfWeek, $endOfWeek])
            ->select([
                DB::raw('COUNT(DISTINCT employee_id) as total_unique'),
                DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as total_present'),
                DB::raw('SUM(CASE WHEN is_absent = true THEN 1 ELSE 0 END) as total_absent'),
                DB::raw('AVG(total_hours) as avg_hours'),
            ])
            ->first();

        return [
            'period' => $startOfWeek->format('Y-m-d') . ' to ' . $endOfWeek->format('Y-m-d'),
            'daily_stats' => $dailyStats,
            'weekly_summary' => $totalAttendance,
        ];
    }

    /**
     * Generate monthly attendance report.
     */
    public static function generateMonthlyReport($year = null, $month = null)
    {
        $now = Carbon::now();
        $year = $year ?? $now->year;
        $month = $month ?? $now->month;

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $stats = Attendance::whereBetween('attendance_date', [$startDate, $endDate])
            ->select([
                'attendance_date',
                DB::raw('COUNT(DISTINCT employee_id) as present'),
                DB::raw('COUNT(DISTINCT CASE WHEN is_absent = true THEN employee_id END) as absent'),
                DB::raw('COUNT(DISTINCT CASE WHEN is_late = true THEN employee_id END) as late'),
                DB::raw('AVG(total_hours) as avg_hours'),
            ])
            ->groupBy('attendance_date')
            ->orderBy('attendance_date')
            ->get();

        // Employee-wise summary
        $employeeStats = Attendance::whereBetween('attendance_date', [$startDate, $endDate])
            ->with('employee')
            ->select([
                'employee_id',
                DB::raw('COUNT(*) as working_days'),
                DB::raw('COUNT(CASE WHEN status = "present" THEN 1 END) as present_days'),
                DB::raw('COUNT(CASE WHEN is_absent = true THEN 1 END) as absent_days'),
                DB::raw('COUNT(CASE WHEN is_late = true THEN 1 END) as late_days'),
                DB::raw('ROUND(AVG(total_hours), 2) as avg_hours'),
                DB::raw('ROUND(SUM(total_hours), 2) as total_hours'),
                DB::raw('ROUND(SUM(overtime_hours), 2) as overtime_hours'),
            ])
            ->groupBy('employee_id')
            ->get();

        // Overtime summary
        $overtime = Overtime::whereBetween('overtime_date', [$startDate, $endDate])
            ->select([
                'employee_id',
                DB::raw('SUM(hours) as total_hours'),
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('COUNT(*) as count'),
            ])
            ->groupBy('employee_id')
            ->with('employee')
            ->get();

        return [
            'period' => $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'),
            'daily_summary' => $stats,
            'employee_summary' => $employeeStats,
            'overtime_summary' => $overtime,
        ];
    }

    /**
     * Generate yearly attendance report.
     */
    public static function generateYearlyReport($year = null)
    {
        $year = $year ?? Carbon::now()->year;
        $startDate = Carbon::createFromDate($year, 1, 1);
        $endDate = $startDate->copy()->endOfYear();

        $monthlyStats = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $stats = Attendance::whereBetween('attendance_date', [$monthStart, $monthEnd])
                ->select([
                    DB::raw('COUNT(DISTINCT employee_id) as total'),
                    DB::raw('COUNT(DISTINCT CASE WHEN status = "present" THEN employee_id END) as present'),
                    DB::raw('COUNT(DISTINCT CASE WHEN is_absent = true THEN employee_id END) as absent'),
                    DB::raw('AVG(total_hours) as avg_hours'),
                ])
                ->first();

            $monthlyStats[$month] = $stats;
        }

        return [
            'year' => $year,
            'monthly_summary' => $monthlyStats,
        ];
    }

    /**
     * Generate employee-wise detailed report.
     */
    public static function generateEmployeeReport($employeeId, $startDate, $endDate)
    {
        $records = Attendance::where('employee_id', $employeeId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->with(['breakPeriods', 'overtimes'])
            ->orderBy('attendance_date')
            ->get();

        $summary = [
            'total_days' => $records->count(),
            'present_days' => $records->where('status', 'present')->count(),
            'absent_days' => $records->where('is_absent', true)->count(),
            'late_days' => $records->where('is_late', true)->count(),
            'total_hours' => round($records->sum('total_hours'), 2),
            'total_breaks' => $records->sum(fn($r) => $r->breakPeriods->count()),
            'total_overtime' => round($records->sum('overtime_hours'), 2),
        ];

        return [
            'employee_id' => $employeeId,
            'period' => $startDate . ' to ' . $endDate,
            'summary' => $summary,
            'records' => $records,
        ];
    }

    /**
     * Generate department-wise report.
     */
    public static function generateDepartmentReport($departmentId, $startDate, $endDate)
    {
        $employees = DB::table('employees')
            ->where('department_id', $departmentId)
            ->pluck('id');

        $attendance = Attendance::whereIn('employee_id', $employees)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->select([
                DB::raw('COUNT(DISTINCT employee_id) as total_employees'),
                DB::raw('COUNT(DISTINCT CASE WHEN status = "present" THEN employee_id END) as present'),
                DB::raw('COUNT(DISTINCT CASE WHEN is_absent = true THEN employee_id END) as absent'),
                DB::raw('AVG(total_hours) as avg_hours'),
                DB::raw('SUM(overtime_hours) as total_overtime'),
            ])
            ->first();

        return [
            'department_id' => $departmentId,
            'period' => $startDate . ' to ' . $endDate,
            'summary' => $attendance,
        ];
    }

    /**
     * Generate compliance report.
     */
    public static function generateComplianceReport($startDate, $endDate)
    {
        $totalAttendanceRecords = Attendance::whereBetween('attendance_date', [$startDate, $endDate])->count();

        $compliant = Attendance::whereBetween('attendance_date', [$startDate, $endDate])
            ->where('is_late', false)
            ->where('is_absent', false)
            ->count();

        $violations = $totalAttendanceRecords - $compliant;
        $complianceRate = $totalAttendanceRecords > 0 ? round(($compliant / $totalAttendanceRecords) * 100, 2) : 100;

        return [
            'period' => $startDate . ' to ' . $endDate,
            'total_records' => $totalAttendanceRecords,
            'compliant_records' => $compliant,
            'violation_records' => $violations,
            'compliance_rate' => $complianceRate,
        ];
    }
}
