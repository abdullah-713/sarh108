<?php

namespace App\Http\Controllers\Api;

use App\Models\Attendance;
use App\Models\AttendanceAlert;
use App\Models\AttendancePolicy;
use App\Models\BreakPeriod;
use App\Models\Employee;
use App\Models\GeoLocation;
use App\Models\WorkHoursSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AttendanceController extends ApiController
{
    /**
     * Employee checks in.
     */
    public function checkIn(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'qr_code' => 'nullable|string',
                'device_info' => 'nullable|array',
            ]);

            $employee = Employee::findOrFail($validated['employee_id']);

            // Verify geolocation if required
            if ($this->isGeoLocationCheckRequired()) {
                $this->verifyGeolocation($validated['latitude'], $validated['longitude'], $employee->branch_id);
            }

            // Check if already checked in today
            $existingCheckIn = Attendance::where('employee_id', $validated['employee_id'])
                ->whereDate('attendance_date', today())
                ->whereNotNull('check_in_time')
                ->first();

            if ($existingCheckIn) {
                return $this->error('Employee already checked in today', 400);
            }

            // Create attendance record
            $attendance = Attendance::create([
                'employee_id' => $validated['employee_id'],
                'shift_id' => $employee->shift_id,
                'attendance_date' => today(),
                'check_in_time' => now(),
                'check_in_location' => $request->input('location_name'),
                'latitude_in' => $validated['latitude'],
                'longitude_in' => $validated['longitude'],
                'qr_code_uuid' => Str::uuid(),
                'is_present' => true,
                'status' => 'present',
                'created_by' => auth()->id(),
            ]);

            // Check if late
            $workHoursSetting = WorkHoursSetting::forDepartment($employee->department_id)->active()->first();
            if ($workHoursSetting && $workHoursSetting->isLateArrival($attendance->check_in_time)) {
                $attendance->update(['is_late' => true, 'status' => 'late']);
                $this->createAlert($employee->id, 'late_arrival', 'Employee checked in late', auth()->user()->id);
            }

            // Calculate response time for performance
            $responseTime = now()->diffInMilliseconds($request->server('REQUEST_TIME_FLOAT') ? microtime(true) * 1000 : 0);

            return $this->success([
                'attendance' => $attendance,
                'message' => 'Check-in successful',
                'response_time_ms' => $responseTime,
                'check_in_time' => $attendance->check_in_time,
            ], 201);

        } catch (ValidationException $e) {
            return $this->error($e->errors(), 422);
        } catch (\Exception $e) {
            return $this->error('Check-in failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Employee checks out.
     */
    public function checkOut(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

            $employee = Employee::findOrFail($validated['employee_id']);

            // Verify geolocation
            if ($this->isGeoLocationCheckRequired()) {
                $this->verifyGeolocation($validated['latitude'], $validated['longitude'], $employee->branch_id);
            }

            // Get today's attendance
            $attendance = Attendance::where('employee_id', $validated['employee_id'])
                ->whereDate('attendance_date', today())
                ->whereNotNull('check_in_time')
                ->whereNull('check_out_time')
                ->first();

            if (!$attendance) {
                return $this->error('No check-in found for today', 400);
            }

            // Update check-out
            $attendance->update([
                'check_out_time' => now(),
                'check_out_location' => $request->input('location_name'),
                'latitude_out' => $validated['latitude'],
                'longitude_out' => $validated['longitude'],
            ]);

            // Calculate work hours
            $this->calculateWorkHours($attendance);

            // Check for early departure
            $workHoursSetting = WorkHoursSetting::forDepartment($employee->department_id)->active()->first();
            if ($workHoursSetting && $workHoursSetting->isEarlyDeparture($attendance->check_out_time)) {
                $attendance->update(['is_early_departure' => true]);
                $this->createAlert($employee->id, 'early_departure', 'Employee checked out early', auth()->user()->id);
            }

            return $this->success([
                'attendance' => $attendance,
                'message' => 'Check-out successful',
                'total_hours' => $attendance->total_hours,
                'overtime_hours' => $attendance->overtime_hours,
            ], 200);

        } catch (ValidationException $e) {
            return $this->error($e->errors(), 422);
        } catch (\Exception $e) {
            return $this->error('Check-out failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Start a break period.
     */
    public function startBreak(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'break_type' => 'required|in:lunch,prayer,coffee,medical',
                'reason' => 'nullable|string',
            ]);

            // Get today's attendance
            $attendance = Attendance::where('employee_id', $validated['employee_id'])
                ->whereDate('attendance_date', today())
                ->whereNotNull('check_in_time')
                ->whereNull('check_out_time')
                ->first();

            if (!$attendance) {
                return $this->error('Employee has not checked in today', 400);
            }

            // Check break count
            $breakCount = BreakPeriod::where('attendance_record_id', $attendance->id)
                ->count();

            $workHoursSetting = WorkHoursSetting::forDepartment(auth()->user()->employee->department_id)->active()->first();
            if ($workHoursSetting && $breakCount >= $workHoursSetting->max_breaks_per_day) {
                return $this->error('Maximum breaks reached for the day', 400);
            }

            $break = BreakPeriod::create([
                'attendance_record_id' => $attendance->id,
                'employee_id' => $validated['employee_id'],
                'break_start' => now(),
                'break_type' => $validated['break_type'],
                'reason' => $validated['reason'],
                'created_by' => auth()->id(),
            ]);

            return $this->success([
                'break' => $break,
                'message' => 'Break started',
            ], 201);

        } catch (ValidationException $e) {
            return $this->error($e->errors(), 422);
        } catch (\Exception $e) {
            return $this->error('Start break failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * End a break period.
     */
    public function endBreak(Request $request)
    {
        try {
            $validated = $request->validate([
                'break_id' => 'required|exists:break_periods,id',
            ]);

            $break = BreakPeriod::findOrFail($validated['break_id']);

            if ($break->break_end) {
                return $this->error('Break already ended', 400);
            }

            // Calculate break duration
            $break->update([
                'break_end' => now(),
                'break_duration' => now()->diffInMinutes($break->break_start),
            ]);

            // Check if exceeds limit
            $workHoursSetting = WorkHoursSetting::forDepartment(auth()->user()->employee->department_id)->active()->first();
            if ($workHoursSetting && $break->break_duration > $workHoursSetting->max_break_duration) {
                $break->update(['exceeds_limit' => true]);
                $this->createAlert($break->employee_id, 'break_exceeded', 'Break duration exceeded', auth()->user()->id);
            }

            return $this->success([
                'break' => $break,
                'message' => 'Break ended',
                'duration_minutes' => $break->break_duration,
            ], 200);

        } catch (ValidationException $e) {
            return $this->error($e->errors(), 422);
        } catch (\Exception $e) {
            return $this->error('End break failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get current attendance status.
     */
    public function getCurrentStatus(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $attendance = Attendance::where('employee_id', $validated['employee_id'])
            ->whereDate('attendance_date', today())
            ->first();

        if (!$attendance) {
            return $this->success([
                'status' => 'not_checked_in',
                'message' => 'Employee has not checked in yet',
            ]);
        }

        $currentBreak = BreakPeriod::where('attendance_record_id', $attendance->id)
            ->whereNull('break_end')
            ->first();

        return $this->success([
            'attendance' => $attendance,
            'current_break' => $currentBreak,
            'status' => $currentBreak ? 'on_break' : ($attendance->check_out_time ? 'checked_out' : 'checked_in'),
        ]);
    }

    /**
     * Verify geolocation is within allowed boundaries.
     */
    private function verifyGeolocation($latitude, $longitude, $branchId)
    {
        $location = GeoLocation::forBranch($branchId)->active()->checkIn()->first();

        if (!$location) {
            throw new \Exception('No check-in location configured for this branch');
        }

        if (!$location->isWithinGeofence($latitude, $longitude)) {
            throw new \Exception('Location is outside allowed geofence. Distance: ' . 
                $location->calculateDistance($latitude, $longitude) . 'm');
        }
    }

    /**
     * Check if geolocation verification is required.
     */
    private function isGeoLocationCheckRequired(): bool
    {
        // This can be configured in settings
        return true;
    }

    /**
     * Calculate work hours and overtime.
     */
    private function calculateWorkHours(Attendance $attendance)
    {
        if (!$attendance->check_in_time || !$attendance->check_out_time) {
            return;
        }

        $checkIn = \Carbon\Carbon::parse($attendance->check_in_time);
        $checkOut = \Carbon\Carbon::parse($attendance->check_out_time);
        $totalMinutes = $checkOut->diffInMinutes($checkIn);
        $breakMinutes = $attendance->breakPeriods()->sum('break_duration') ?? 0;
        $workMinutes = $totalMinutes - $breakMinutes;
        $workHours = $workMinutes / 60;

        $workHoursSetting = WorkHoursSetting::forDepartment($attendance->employee->department_id)->active()->first();
        $expectedHours = $workHoursSetting ? $workHoursSetting->daily_working_hours : 8;

        $overtimeHours = max(0, $workHours - $expectedHours);

        $attendance->update([
            'total_hours' => $workHours,
            'break_hours' => $breakMinutes / 60,
            'work_hours' => $workHours,
            'overtime_hours' => $overtimeHours,
        ]);
    }

    /**
     * Create an attendance alert.
     */
    private function createAlert($employeeId, $alertType, $message, $managerId = null)
    {
        AttendanceAlert::create([
            'employee_id' => $employeeId,
            'manager_id' => $managerId,
            'alert_type' => $alertType,
            'message' => $message,
            'alert_time' => now(),
            'severity' => 'warning',
        ]);
    }
}
