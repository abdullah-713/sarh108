<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\BreakPeriod;
use App\Models\Overtime;
use App\Models\WorkHoursSetting;
use Carbon\Carbon;

class AttendanceCalculationService
{
    /**
     * Calculate total work hours from check-in/check-out times.
     */
    public static function calculateTotalWorkHours($checkInTime, $checkOutTime, $breakMinutes = 0)
    {
        if (!$checkInTime || !$checkOutTime) {
            return 0;
        }

        $checkIn = Carbon::parse($checkInTime);
        $checkOut = Carbon::parse($checkOutTime);

        $totalMinutes = $checkOut->diffInMinutes($checkIn);
        $workMinutes = $totalMinutes - $breakMinutes;

        return round($workMinutes / 60, 2); // Convert to hours
    }

    /**
     * Calculate overtime hours.
     */
    public static function calculateOvertime($workHours, $expectedHours)
    {
        $overtime = $workHours - $expectedHours;
        return $overtime > 0 ? round($overtime, 2) : 0;
    }

    /**
     * Calculate overtime compensation amount.
     */
    public static function calculateOvertimeAmount($hours, $ratePerHour)
    {
        return round($hours * $ratePerHour, 2);
    }

    /**
     * Check if arrival is late based on policy.
     */
    public static function isLateArrival($checkInTime, $expectedStartTime, $gracePeriodMinutes = 15)
    {
        $checkIn = Carbon::parse($checkInTime);
        $expected = Carbon::parse($expectedStartTime);
        $gracePeriod = $expected->copy()->addMinutes($gracePeriodMinutes);

        return $checkIn->gt($gracePeriod);
    }

    /**
     * Calculate late minutes.
     */
    public static function calculateLateMinutes($checkInTime, $expectedStartTime, $gracePeriodMinutes = 15)
    {
        if (!self::isLateArrival($checkInTime, $expectedStartTime, $gracePeriodMinutes)) {
            return 0;
        }

        $checkIn = Carbon::parse($checkInTime);
        $expected = Carbon::parse($expectedStartTime);
        $gracePeriod = $expected->copy()->addMinutes($gracePeriodMinutes);

        return $checkIn->diffInMinutes($gracePeriod);
    }

    /**
     * Check if departure is early.
     */
    public static function isEarlyDeparture($checkOutTime, $expectedEndTime, $gracePeriodMinutes = 15)
    {
        $checkOut = Carbon::parse($checkOutTime);
        $expected = Carbon::parse($expectedEndTime);
        $gracePeriod = $expected->copy()->subMinutes($gracePeriodMinutes);

        return $checkOut->lt($gracePeriod);
    }

    /**
     * Calculate early departure minutes.
     */
    public static function calculateEarlyDepartureMinutes($checkOutTime, $expectedEndTime, $gracePeriodMinutes = 15)
    {
        if (!self::isEarlyDeparture($checkOutTime, $expectedEndTime, $gracePeriodMinutes)) {
            return 0;
        }

        $checkOut = Carbon::parse($checkOutTime);
        $expected = Carbon::parse($expectedEndTime);
        $gracePeriod = $expected->copy()->subMinutes($gracePeriodMinutes);

        return $gracePeriod->diffInMinutes($checkOut);
    }

    /**
     * Calculate daily performance score (0-100).
     */
    public static function calculatePerformanceScore($attendance)
    {
        $score = 100;

        // Deduct for late arrival
        if ($attendance->is_late) {
            $score -= 10;
        }

        // Deduct for early departure
        if ($attendance->is_early_departure) {
            $score -= 10;
        }

        // Deduct for exceeding break time
        $breaksExceeded = $attendance->breakPeriods()
            ->where('exceeds_limit', true)
            ->count();

        if ($breaksExceeded > 0) {
            $score -= (5 * $breaksExceeded);
        }

        // Bonus for working overtime
        if ($attendance->overtime_hours > 0) {
            $score += min(10, $attendance->overtime_hours); // Max 10 points
        }

        return max(0, min(100, $score)); // Ensure score is between 0-100
    }

    /**
     * Calculate half-day threshold.
     */
    public static function isHalfDay($workHours, $expectedHours, $halfDayThreshold = 4)
    {
        return $workHours >= $halfDayThreshold && $workHours < $expectedHours;
    }

    /**
     * Calculate attendance percentage for a period.
     */
    public static function calculateAttendancePercentage($employeeId, $startDate, $endDate)
    {
        $totalWorkingDays = Attendance::where('employee_id', $employeeId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->where('is_weekend', false)
            ->where('is_holiday', false)
            ->count();

        $presentDays = Attendance::where('employee_id', $employeeId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->where('status', 'present')
            ->count();

        if ($totalWorkingDays === 0) {
            return 0;
        }

        return round(($presentDays / $totalWorkingDays) * 100, 2);
    }

    /**
     * Identify anomalies in attendance pattern.
     */
    public static function detectAnomalies($employeeId, $days = 30)
    {
        $startDate = Carbon::now()->subDays($days);
        $endDate = Carbon::now();

        $records = Attendance::where('employee_id', $employeeId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get();

        $anomalies = [];

        // Check for consecutive absences
        $consecutiveAbsent = 0;
        foreach ($records as $record) {
            if ($record->is_absent) {
                $consecutiveAbsent++;
                if ($consecutiveAbsent >= 3) {
                    $anomalies[] = [
                        'type' => 'consecutive_absences',
                        'count' => $consecutiveAbsent,
                        'severity' => 'high',
                    ];
                }
            } else {
                $consecutiveAbsent = 0;
            }
        }

        // Check for excessive late arrivals
        $lateArrivals = $records->where('is_late', true)->count();
        if ($lateArrivals >= 5) {
            $anomalies[] = [
                'type' => 'excessive_late_arrivals',
                'count' => $lateArrivals,
                'severity' => 'medium',
            ];
        }

        // Check for excessive overtime
        $totalOvertime = $records->sum('overtime_hours');
        if ($totalOvertime > 50) {
            $anomalies[] = [
                'type' => 'excessive_overtime',
                'hours' => $totalOvertime,
                'severity' => 'medium',
            ];
        }

        return $anomalies;
    }

    /**
     * Calculate shift adjustment hours.
     */
    public static function calculateShiftAdjustment($attendanceId)
    {
        $attendance = Attendance::find($attendanceId);
        if (!$attendance) {
            return 0;
        }

        $setting = WorkHoursSetting::forDepartment($attendance->employee->department_id)->active()->first();
        if (!$setting) {
            return 0;
        }

        $expectedHours = $setting->daily_working_hours;
        $actualHours = $attendance->total_hours ?? 0;

        if ($actualHours >= $expectedHours) {
            return 0; // No adjustment needed
        }

        return round($expectedHours - $actualHours, 2);
    }

    /**
     * Calculate deduction for late arrival.
     */
    public static function calculateLateArrivalDeduction($lateMinutes, $hourlyRate)
    {
        $hourlyMinutes = 60;
        $deductionHours = $lateMinutes / $hourlyMinutes;
        return round($deductionHours * $hourlyRate, 2);
    }

    /**
     * Calculate deduction for early departure.
     */
    public static function calculateEarlyDepartureDeduction($earlyMinutes, $hourlyRate)
    {
        $hourlyMinutes = 60;
        $deductionHours = $earlyMinutes / $hourlyMinutes;
        return round($deductionHours * $hourlyRate, 2);
    }

    /**
     * Calculate break hours for compensation.
     */
    public static function calculateBreakCompensation($breakMinutes, $hourlyRate)
    {
        $breakHours = $breakMinutes / 60;
        // Break time is typically not compensated unless it exceeds limits
        return 0;
    }

    /**
     * Validate attendance data integrity.
     */
    public static function validateAttendanceData($attendance)
    {
        $errors = [];

        // Check if check-out is after check-in
        if ($attendance->check_in_time && $attendance->check_out_time) {
            $checkIn = Carbon::parse($attendance->check_in_time);
            $checkOut = Carbon::parse($attendance->check_out_time);

            if ($checkOut->lt($checkIn)) {
                $errors[] = 'Check-out time cannot be before check-in time';
            }

            // Check if total duration is reasonable (more than 12 hours is suspicious)
            if ($checkOut->diffInHours($checkIn) > 12) {
                $errors[] = 'Working duration exceeds 12 hours, please verify';
            }
        }

        // Validate GPS coordinates if present
        if ($attendance->latitude_in !== null && $attendance->longitude_in !== null) {
            if ($attendance->latitude_in < -90 || $attendance->latitude_in > 90) {
                $errors[] = 'Invalid latitude for check-in location';
            }
            if ($attendance->longitude_in < -180 || $attendance->longitude_in > 180) {
                $errors[] = 'Invalid longitude for check-in location';
            }
        }

        return $errors;
    }
}
