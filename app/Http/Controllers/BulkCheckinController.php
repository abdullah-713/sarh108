<?php

namespace App\Http\Controllers;

use App\Models\BulkCheckin;
use App\Models\QuickCheckin;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\EmployeeStatusLog;
use App\Models\DeductionTier;
use App\Models\TimeWindow;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BulkCheckinController extends Controller
{
    /**
     * صفحة التحضير الجماعي
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $companyUserIds = getCompanyAndUsersId();

        $branches = Branch::whereIn('created_by', $companyUserIds)
            ->select('id', 'name')
            ->get();

        $branchId = $request->input('branch_id', $branches->first()?->id);

        $employees = Employee::where('branch_id', $branchId)
            ->whereIn('created_by', $companyUserIds)
            ->with(['department', 'designation'])
            ->get()
            ->map(function ($employee) {
                $todayCheckin = QuickCheckin::where('employee_id', $employee->id)
                    ->where('type', 'checkin')
                    ->whereDate('checked_at', today())
                    ->first();

                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'employee_id' => $employee->employee_id,
                    'department' => $employee->department?->name,
                    'designation' => $employee->designation?->name,
                    'has_checkin' => (bool) $todayCheckin,
                    'checkin_time' => $todayCheckin?->checked_at?->format('H:i'),
                ];
            });

        $todayBulkCheckins = BulkCheckin::getTodayByBranch($branchId);

        return Inertia::render('attendance/bulk-checkin', [
            'branches' => $branches,
            'selectedBranch' => $branchId,
            'employees' => $employees,
            'todayBulkCheckins' => $todayBulkCheckins,
        ]);
    }

    /**
     * تنفيذ التحضير الجماعي
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'type' => 'required|in:checkin,checkout',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $branch = Branch::find($validated['branch_id']);
        $timeWindow = TimeWindow::getActiveWindow($validated['branch_id'], $validated['type']);
        $lateMinutes = $timeWindow ? $timeWindow->calculateLateMinutes() : 0;
        $deduction = DeductionTier::calculateDeduction($lateMinutes);

        $processedEmployees = [];
        $skippedEmployees = [];

        foreach ($validated['employee_ids'] as $employeeId) {
            // التحقق من عدم وجود تسجيل سابق
            if (QuickCheckin::hasTodayCheckin($employeeId, $validated['type'])) {
                $skippedEmployees[] = $employeeId;
                continue;
            }

            // إنشاء سجل الحضور
            QuickCheckin::create([
                'employee_id' => $employeeId,
                'branch_id' => $validated['branch_id'],
                'type' => $validated['type'],
                'checked_at' => now(),
                'verification_method' => 'manual',
                'is_verified' => true,
                'late_minutes' => $validated['type'] === 'checkin' ? $lateMinutes : 0,
                'notes' => 'تحضير جماعي بواسطة المشرف',
                'verified_by' => $user->id,
            ]);

            // تحديث سجل الحالة اليومي
            if ($validated['type'] === 'checkin') {
                EmployeeStatusLog::updateOrCreateToday($employeeId, [
                    'status' => $lateMinutes > 0 ? 'late' : 'present',
                    'checkin_time' => now()->format('H:i:s'),
                    'late_minutes' => $lateMinutes,
                    'deduction_points' => $deduction['points'],
                    'is_perfect_day' => $lateMinutes == 0,
                ]);
            } else {
                $todayLog = EmployeeStatusLog::getTodayLog($employeeId);
                if ($todayLog && $todayLog->checkin_time) {
                    $workedMinutes = now()->diffInMinutes($todayLog->checkin_time);
                    $todayLog->update([
                        'checkout_time' => now()->format('H:i:s'),
                        'worked_minutes' => $workedMinutes,
                    ]);
                }
            }

            $processedEmployees[] = $employeeId;
        }

        // إنشاء سجل التحضير الجماعي
        if (count($processedEmployees) > 0) {
            BulkCheckin::createBulkCheckin(
                $validated['branch_id'],
                $user->id,
                $processedEmployees,
                $validated['type'],
                $validated['notes']
            );
        }

        $message = sprintf(
            'تم تسجيل %s لـ %d موظف',
            $validated['type'] === 'checkin' ? 'الحضور' : 'الانصراف',
            count($processedEmployees)
        );

        if (count($skippedEmployees) > 0) {
            $message .= sprintf(' (تم تخطي %d موظف لوجود تسجيل سابق)', count($skippedEmployees));
        }

        return back()->with('success', $message);
    }

    /**
     * سجل التحضير الجماعي
     */
    public function logs(Request $request)
    {
        $user = auth()->user();
        $companyUserIds = getCompanyAndUsersId();

        $logs = BulkCheckin::with(['branch', 'supervisor'])
            ->whereHas('branch', function ($query) use ($companyUserIds) {
                $query->whereIn('created_by', $companyUserIds);
            })
            ->when($request->branch_id, function ($query, $branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when($request->date, function ($query, $date) {
                $query->whereDate('checked_at', $date);
            })
            ->orderBy('checked_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $branches = Branch::whereIn('created_by', $companyUserIds)
            ->select('id', 'name')
            ->get();

        return Inertia::render('attendance/bulk-checkin-logs', [
            'logs' => $logs,
            'branches' => $branches,
            'filters' => $request->only(['branch_id', 'date']),
        ]);
    }
}
