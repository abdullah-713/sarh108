<?php

namespace App\Http\Controllers\Api;

use App\Models\Attendance;
use App\Models\AttendanceAlert;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Overtime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminAttendanceController extends ApiController
{
    /**
     * Get admin dashboard with all statistics.
     */
    public function dashboard(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
            ]);

            $startDate = $validated['start_date'] ? Carbon::parse($validated['start_date']) : Carbon::now()->startOfMonth();
            $endDate = $validated['end_date'] ? Carbon::parse($validated['end_date']) : Carbon::now();

            // Summary statistics
            $summary = [
                'total_employees' => Employee::count(),
                'total_present_today' => Attendance::whereDate('attendance_date', today())
                    ->where('status', 'present')->count(),
                'total_absent_today' => Attendance::whereDate('attendance_date', today())
                    ->where('is_absent', true)->count(),
                'total_late_today' => Attendance::whereDate('attendance_date', today())
                    ->where('is_late', true)->count(),
                'total_on_break' => DB::table('break_periods')
                    ->whereNull('break_end')
                    ->count(),
                'average_working_hours' => round(
                    Attendance::whereDate('attendance_date', today())
                        ->avg('total_hours') ?? 0,
                    2
                ),
                'total_overtime_hours' => Overtime::whereDate('overtime_date', today())
                    ->sum('hours') ?? 0,
            ];

            // Attendance by branch
            $attendanceByBranch = DB::table('branches')
                ->leftJoin('employees', 'branches.id', '=', 'employees.branch_id')
                ->leftJoin('attendances', function ($join) {
                    $join->on('employees.id', '=', 'attendances.employee_id')
                        ->whereDate('attendances.attendance_date', today());
                })
                ->select(
                    'branches.name',
                    DB::raw('COUNT(DISTINCT employees.id) as total_employees'),
                    DB::raw('COUNT(DISTINCT CASE WHEN attendances.status = "present" THEN attendances.employee_id END) as present'),
                    DB::raw('COUNT(DISTINCT CASE WHEN attendances.is_absent = true THEN attendances.employee_id END) as absent')
                )
                ->groupBy('branches.id', 'branches.name')
                ->get();

            // Department performance
            $departmentPerformance = DB::table('departments')
                ->leftJoin('employees', 'departments.id', '=', 'employees.department_id')
                ->leftJoin('attendances', function ($join) {
                    $join->on('employees.id', '=', 'attendances.employee_id')
                        ->whereBetween('attendances.attendance_date', [$startDate, $endDate]);
                })
                ->select(
                    'departments.name',
                    DB::raw('COUNT(DISTINCT CASE WHEN attendances.status = "present" THEN attendances.employee_id END) as present'),
                    DB::raw('COUNT(DISTINCT CASE WHEN attendances.is_absent = true THEN attendances.employee_id END) as absent'),
                    DB::raw('COUNT(DISTINCT CASE WHEN attendances.is_late = true THEN attendances.employee_id END) as late')
                )
                ->groupBy('departments.id', 'departments.name')
                ->get();

            // Hourly attendance distribution
            $hourlyAttendance = Attendance::whereDate('attendance_date', today())
                ->select(
                    DB::raw('HOUR(check_in_time) as hour'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();

            // Monthly trends
            $monthlyTrends = Attendance::whereBetween('attendance_date', [$startDate, $endDate])
                ->select(
                    'attendance_date as date',
                    DB::raw('COUNT(CASE WHEN status = "present" THEN 1 END) as present'),
                    DB::raw('COUNT(CASE WHEN is_absent = true THEN 1 END) as absent'),
                    DB::raw('COUNT(CASE WHEN is_late = true THEN 1 END) as late')
                )
                ->groupBy('attendance_date')
                ->orderBy('attendance_date')
                ->get();

            // Overtime summary
            $overtimeSummary = Overtime::whereBetween('overtime_date', [$startDate, $endDate])
                ->select(
                    DB::raw('CONCAT(employees.first_name, " ", employees.last_name) as name'),
                    DB::raw('SUM(hours) as hours')
                )
                ->join('employees', 'overtime.employee_id', '=', 'employees.id')
                ->groupBy('overtime.employee_id', 'employees.first_name', 'employees.last_name')
                ->orderByDesc('hours')
                ->take(10)
                ->get();

            // Critical alerts
            $criticalAlerts = AttendanceAlert::where('severity', 'critical')
                ->where('is_resolved', false)
                ->with('employee')
                ->orderBy('alert_time', 'desc')
                ->take(10)
                ->get();

            // Compliance score
            $totalDays = Attendance::whereBetween('attendance_date', [$startDate, $endDate])->count();
            $compliantDays = Attendance::whereBetween('attendance_date', [$startDate, $endDate])
                ->where('is_late', false)
                ->where('is_absent', false)
                ->count();
            $complianceScore = $totalDays > 0 ? round(($compliantDays / $totalDays) * 100) : 100;

            return $this->success([
                'summary' => $summary,
                'attendance_by_branch' => $attendanceByBranch,
                'department_performance' => $departmentPerformance,
                'hourly_attendance' => $hourlyAttendance,
                'monthly_trends' => $monthlyTrends,
                'overtime_summary' => $overtimeSummary,
                'critical_alerts' => $criticalAlerts,
                'compliance_score' => $complianceScore,
            ]);

        } catch (\Exception $e) {
            return $this->error('Failed to fetch dashboard: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Export attendance report.
     */
    public function exportReport(Request $request)
    {
        try {
            $validated = $request->validate([
                'format' => 'required|in:pdf,excel',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'branch_id' => 'nullable|exists:branches,id',
                'department_id' => 'nullable|exists:departments,id',
            ]);

            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);

            $query = Attendance::whereBetween('attendance_date', [$startDate, $endDate])
                ->with(['employee', 'shift']);

            if ($validated['branch_id'] ?? false) {
                $query->whereHas('employee', function ($q) use ($validated) {
                    $q->where('branch_id', $validated['branch_id']);
                });
            }

            if ($validated['department_id'] ?? false) {
                $query->whereHas('employee', function ($q) use ($validated) {
                    $q->where('department_id', $validated['department_id']);
                });
            }

            $records = $query->get();

            if ($validated['format'] === 'pdf') {
                return $this->generatePdfReport($records, $startDate, $endDate);
            } else {
                return $this->generateExcelReport($records, $startDate, $endDate);
            }

        } catch (\Exception $e) {
            return $this->error('Failed to export report: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get detailed employee attendance history.
     */
    public function employeeHistory(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'page' => 'nullable|integer|min:1',
            ]);

            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);

            $records = Attendance::where('employee_id', $validated['employee_id'])
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->with(['shift', 'breakPeriods', 'overtimes'])
                ->orderBy('attendance_date', 'desc')
                ->paginate(30);

            return $this->paginate($records, 'Employee attendance history retrieved');

        } catch (\Exception $e) {
            return $this->error('Failed to fetch history: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get system statistics.
     */
    public function statistics(Request $request)
    {
        try {
            $validated = $request->validate([
                'period' => 'nullable|in:day,week,month,year',
            ]);

            $period = $validated['period'] ?? 'month';

            $startDate = match($period) {
                'day' => Carbon::now()->startOfDay(),
                'week' => Carbon::now()->startOfWeek(),
                'month' => Carbon::now()->startOfMonth(),
                'year' => Carbon::now()->startOfYear(),
            };

            $endDate = Carbon::now();

            $stats = [
                'total_check_ins' => Attendance::whereBetween('created_at', [$startDate, $endDate])
                    ->whereNotNull('check_in_time')->count(),
                'total_check_outs' => Attendance::whereBetween('created_at', [$startDate, $endDate])
                    ->whereNotNull('check_out_time')->count(),
                'total_breaks_taken' => DB::table('break_periods')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereNotNull('break_end')->count(),
                'total_overtime_hours' => Overtime::whereBetween('created_at', [$startDate, $endDate])
                    ->sum('hours') ?? 0,
                'average_work_hours' => round(
                    Attendance::whereBetween('created_at', [$startDate, $endDate])
                        ->avg('total_hours') ?? 0,
                    2
                ),
                'alerts_generated' => AttendanceAlert::whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
            ];

            return $this->success($stats);

        } catch (\Exception $e) {
            return $this->error('Failed to fetch statistics: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Generate PDF report.
     */
    private function generatePdfReport($records, $startDate, $endDate)
    {
        // This would integrate with a PDF generation library like DOMPDF
        // For now, return JSON data that can be processed by frontend
        return response()->json([
            'format' => 'pdf',
            'records' => $records,
            'period' => $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'),
        ]);
    }

    /**
     * Generate Excel report.
     */
    private function generateExcelReport($records, $startDate, $endDate)
    {
        // This would integrate with a spreadsheet library like PhpSpreadsheet
        // For now, return JSON data that can be processed by frontend
        return response()->json([
            'format' => 'excel',
            'records' => $records,
            'period' => $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'),
        ]);
    }
}
