<?php

namespace App\Http\Controllers\Api;

use App\Models\Attendance;
use App\Models\AttendanceAlert;
use App\Models\Employee;
use App\Models\Overtime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManagerAttendanceController extends ApiController
{
    /**
     * Get manager dashboard data.
     */
    public function dashboard(Request $request)
    {
        try {
            $validated = $request->validate([
                'department_id' => 'nullable|exists:departments,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
            ]);

            $startDate = $validated['start_date'] ? Carbon::parse($validated['start_date']) : Carbon::now()->startOfMonth();
            $endDate = $validated['end_date'] ? Carbon::parse($validated['end_date']) : Carbon::now();

            // Get manager's department
            $manager = auth()->user()->employee;
            $departmentId = $validated['department_id'] ?? $manager->department_id;

            // Get employees in department
            $employees = Employee::where('department_id', $departmentId)->pluck('id');

            // Get statistics
            $presentToday = Attendance::whereIn('employee_id', $employees)
                ->whereDate('attendance_date', today())
                ->where('status', 'present')
                ->count();

            $absentToday = Attendance::whereIn('employee_id', $employees)
                ->whereDate('attendance_date', today())
                ->where('is_absent', true)
                ->count();

            $lateToday = Attendance::whereIn('employee_id', $employees)
                ->whereDate('attendance_date', today())
                ->where('is_late', true)
                ->count();

            $onBreak = DB::table('break_periods')
                ->whereIn('employee_id', $employees)
                ->whereNull('break_end')
                ->count();

            // Get attendance trend
            $attendanceTrend = Attendance::whereIn('employee_id', $employees)
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->select(
                    'attendance_date',
                    DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present'),
                    DB::raw('SUM(CASE WHEN is_absent = true THEN 1 ELSE 0 END) as absent'),
                    DB::raw('SUM(CASE WHEN is_late = true THEN 1 ELSE 0 END) as late')
                )
                ->groupBy('attendance_date')
                ->orderBy('attendance_date')
                ->get();

            // Get department statistics
            $departmentStats = Attendance::whereIn('employee_id', $employees)
                ->whereDate('attendance_date', today())
                ->select(
                    'status',
                    DB::raw('count(*) as count')
                )
                ->groupBy('status')
                ->get();

            // Get active alerts
            $alerts = AttendanceAlert::whereIn('employee_id', $employees)
                ->where('is_resolved', false)
                ->orderBy('alert_time', 'desc')
                ->take(10)
                ->with('employee')
                ->get();

            // Get recent activities
            $recentActivities = Attendance::whereIn('employee_id', $employees)
                ->orderBy('created_at', 'desc')
                ->take(20)
                ->with('employee')
                ->get()
                ->map(fn($record) => [
                    'employee_name' => $record->employee?->full_name,
                    'action' => $record->status === 'present' ? 'تسجيل حضور' : 'تسجيل غياب',
                    'timestamp' => $record->created_at,
                ]);

            return $this->success([
                'total_employees' => $employees->count(),
                'present_today' => $presentToday,
                'absent_today' => $absentToday,
                'late_today' => $lateToday,
                'on_break' => $onBreak,
                'attendance_trend' => $attendanceTrend,
                'department_stats' => $departmentStats,
                'alerts' => $alerts,
                'recent_activities' => $recentActivities,
            ]);

        } catch (\Exception $e) {
            return $this->error('Failed to fetch dashboard: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get team attendance list.
     */
    public function teamAttendance(Request $request)
    {
        try {
            $validated = $request->validate([
                'date' => 'required|date',
                'department_id' => 'nullable|exists:departments,id',
                'status' => 'nullable|in:present,absent,late,on_leave',
                'page' => 'nullable|integer|min:1',
            ]);

            $manager = auth()->user()->employee;
            $departmentId = $validated['department_id'] ?? $manager->department_id;
            $date = Carbon::parse($validated['date']);

            $query = Attendance::where('attendance_date', $date)
                ->with(['employee', 'shift'])
                ->whereHas('employee', function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });

            if ($validated['status'] ?? false) {
                if ($validated['status'] === 'present') {
                    $query->where('status', 'present');
                } elseif ($validated['status'] === 'absent') {
                    $query->where('is_absent', true);
                } elseif ($validated['status'] === 'late') {
                    $query->where('is_late', true);
                }
            }

            $attendance = $query->paginate(15);

            return $this->paginate($attendance, 'Team attendance retrieved');

        } catch (\Exception $e) {
            return $this->error('Failed to fetch team attendance: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Approve or reject attendance record.
     */
    public function approveAttendance(Request $request)
    {
        try {
            $validated = $request->validate([
                'attendance_id' => 'required|exists:attendances,id',
                'action' => 'required|in:approve,reject',
                'notes' => 'nullable|string',
            ]);

            $attendance = Attendance::findOrFail($validated['attendance_id']);

            $attendance->update([
                'approval_status' => $validated['action'] === 'approve' ? 'approved' : 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'notes' => $validated['notes'],
            ]);

            return $this->success([
                'attendance' => $attendance,
                'message' => 'Attendance record ' . $validated['action'] . 'ed successfully',
            ]);

        } catch (\Exception $e) {
            return $this->error('Failed to approve attendance: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get overtime requests for approval.
     */
    public function overtimeRequests(Request $request)
    {
        try {
            $validated = $request->validate([
                'status' => 'nullable|in:pending,approved,rejected',
                'page' => 'nullable|integer|min:1',
            ]);

            $manager = auth()->user()->employee;

            $query = Overtime::with('employee', 'attendanceRecord')
                ->whereHas('employee', function ($q) use ($manager) {
                    $q->where('department_id', $manager->department_id);
                });

            if ($validated['status'] ?? false) {
                $query->where('approval_status', $validated['status']);
            }

            $overtimes = $query->orderBy('created_at', 'desc')->paginate(15);

            return $this->paginate($overtimes, 'Overtime requests retrieved');

        } catch (\Exception $e) {
            return $this->error('Failed to fetch overtime requests: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Approve overtime request.
     */
    public function approveOvertime(Request $request)
    {
        try {
            $validated = $request->validate([
                'overtime_id' => 'required|exists:overtime,id',
                'action' => 'required|in:approve,reject',
                'notes' => 'nullable|string',
            ]);

            $overtime = Overtime::findOrFail($validated['overtime_id']);

            if ($validated['action'] === 'approve') {
                $overtime->approve(auth()->id());
            } else {
                $overtime->reject(auth()->id());
            }

            if ($validated['notes'] ?? false) {
                $overtime->update(['notes' => $validated['notes']]);
            }

            return $this->success([
                'overtime' => $overtime,
                'message' => 'Overtime request ' . $validated['action'] . 'ed successfully',
            ]);

        } catch (\Exception $e) {
            return $this->error('Failed to approve overtime: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Generate team report.
     */
    public function generateTeamReport(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'department_id' => 'nullable|exists:departments,id',
                'format' => 'nullable|in:json,pdf,excel',
            ]);

            $manager = auth()->user()->employee;
            $departmentId = $validated['department_id'] ?? $manager->department_id;
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);

            $employees = Employee::where('department_id', $departmentId)->get();

            $reportData = [];
            foreach ($employees as $employee) {
                $attendanceRecords = Attendance::where('employee_id', $employee->id)
                    ->whereBetween('attendance_date', [$startDate, $endDate])
                    ->get();

                $present = $attendanceRecords->where('status', 'present')->count();
                $absent = $attendanceRecords->where('is_absent', true)->count();
                $late = $attendanceRecords->where('is_late', true)->count();
                $totalHours = $attendanceRecords->sum('total_hours');

                $reportData[] = [
                    'employee_name' => $employee->full_name,
                    'employee_id' => $employee->id,
                    'present_days' => $present,
                    'absent_days' => $absent,
                    'late_arrivals' => $late,
                    'total_hours' => round($totalHours, 2),
                ];
            }

            return $this->success([
                'report' => $reportData,
                'summary' => [
                    'total_employees' => $employees->count(),
                    'period' => $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'),
                ],
            ]);

        } catch (\Exception $e) {
            return $this->error('Failed to generate report: ' . $e->getMessage(), 500);
        }
    }
}
