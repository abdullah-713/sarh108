<?php

namespace App\Services;

use App\Models\AttendanceAlert;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AttendanceAlertNotification;

class AttendanceNotificationService
{
    /**
     * Create late arrival alert.
     */
    public static function createLateArrivalAlert($employeeId, $lateMinutes, $managerId = null)
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return null;
        }

        $alert = AttendanceAlert::create([
            'employee_id' => $employeeId,
            'manager_id' => $managerId ?? $employee->manager_id,
            'alert_type' => 'late_arrival',
            'message' => "Employee checked in {$lateMinutes} minutes late",
            'alert_time' => now(),
            'severity' => $lateMinutes > 30 ? 'critical' : 'warning',
            'metadata' => [
                'late_minutes' => $lateMinutes,
            ],
        ]);

        self::notifyManager($alert);
        return $alert;
    }

    /**
     * Create early departure alert.
     */
    public static function createEarlyDepartureAlert($employeeId, $earlyMinutes, $managerId = null)
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return null;
        }

        $alert = AttendanceAlert::create([
            'employee_id' => $employeeId,
            'manager_id' => $managerId ?? $employee->manager_id,
            'alert_type' => 'early_departure',
            'message' => "Employee checked out {$earlyMinutes} minutes early",
            'alert_time' => now(),
            'severity' => 'info',
            'metadata' => [
                'early_minutes' => $earlyMinutes,
            ],
        ]);

        self::notifyManager($alert);
        return $alert;
    }

    /**
     * Create break exceeded alert.
     */
    public static function createBreakExceededAlert($employeeId, $excessMinutes, $managerId = null)
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return null;
        }

        $alert = AttendanceAlert::create([
            'employee_id' => $employeeId,
            'manager_id' => $managerId ?? $employee->manager_id,
            'alert_type' => 'break_exceeded',
            'message' => "Break duration exceeded by {$excessMinutes} minutes",
            'alert_time' => now(),
            'severity' => 'warning',
            'metadata' => [
                'excess_minutes' => $excessMinutes,
            ],
        ]);

        self::notifyManager($alert);
        return $alert;
    }

    /**
     * Create absence alert.
     */
    public static function createAbsenceAlert($employeeId, $date, $managerId = null)
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return null;
        }

        $alert = AttendanceAlert::create([
            'employee_id' => $employeeId,
            'manager_id' => $managerId ?? $employee->manager_id,
            'alert_type' => 'absence',
            'message' => "Employee was absent on {$date}",
            'alert_time' => now(),
            'severity' => 'critical',
            'metadata' => [
                'date' => $date,
            ],
        ]);

        self::notifyManager($alert);
        return $alert;
    }

    /**
     * Create overtime alert.
     */
    public static function createOvertimeAlert($employeeId, $overtimeHours, $managerId = null)
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return null;
        }

        $alert = AttendanceAlert::create([
            'employee_id' => $employeeId,
            'manager_id' => $managerId ?? $employee->manager_id,
            'alert_type' => 'overtime',
            'message' => "Employee worked {$overtimeHours} hours of overtime",
            'alert_time' => now(),
            'severity' => 'info',
            'metadata' => [
                'overtime_hours' => $overtimeHours,
            ],
        ]);

        self::notifyManager($alert);
        return $alert;
    }

    /**
     * Create geofence violation alert.
     */
    public static function createGeofenceViolationAlert($employeeId, $distance, $managerId = null)
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return null;
        }

        $alert = AttendanceAlert::create([
            'employee_id' => $employeeId,
            'manager_id' => $managerId ?? $employee->manager_id,
            'alert_type' => 'geofence_violation',
            'message' => "Employee is {$distance}m outside geofence boundary",
            'alert_time' => now(),
            'severity' => 'critical',
            'metadata' => [
                'distance_meters' => $distance,
            ],
        ]);

        self::notifyManager($alert);
        return $alert;
    }

    /**
     * Send notification to manager.
     */
    private static function notifyManager($alert)
    {
        if ($alert->manager_id) {
            $manager = User::find($alert->manager_id);
            if ($manager) {
                Notification::send($manager, new AttendanceAlertNotification($alert));
            }
        }
    }

    /**
     * Send bulk notification.
     */
    public static function sendBulkNotification($userIds, $message, $type = 'info')
    {
        $users = User::whereIn('id', $userIds)->get();
        Notification::send($users, new AttendanceAlertNotification([
            'message' => $message,
            'type' => $type,
        ]));
    }

    /**
     * Resolve alert.
     */
    public static function resolveAlert($alertId, $resolutionNotes = null)
    {
        $alert = AttendanceAlert::find($alertId);
        if ($alert) {
            $alert->markAsResolved($resolutionNotes);
        }
        return $alert;
    }

    /**
     * Get unresolved alerts for manager.
     */
    public static function getUnresolvedAlerts($managerId, $limit = 20)
    {
        return AttendanceAlert::where('manager_id', $managerId)
            ->where('is_resolved', false)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Mark alerts as read.
     */
    public static function markAlertsAsRead($alertIds)
    {
        AttendanceAlert::whereIn('id', $alertIds)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}
