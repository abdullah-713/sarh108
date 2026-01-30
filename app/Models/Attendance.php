<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'attendances';

    protected $fillable = [
        'employee_id',
        'shift_id',
        'attendance_date',
        'check_in_time',
        'check_out_time',
        'check_in_location',
        'check_out_location',
        'latitude_in',
        'longitude_in',
        'latitude_out',
        'longitude_out',
        'qr_code_uuid',
        'total_hours',
        'break_hours',
        'overtime_hours',
        'work_hours',
        'is_late',
        'is_early_departure',
        'is_absent',
        'is_present',
        'is_holiday',
        'is_weekend',
        'status',
        'notes',
        'justification',
        'approval_status',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'total_hours' => 'float',
        'break_hours' => 'float',
        'overtime_hours' => 'float',
        'work_hours' => 'float',
        'is_late' => 'boolean',
        'is_early_departure' => 'boolean',
        'is_absent' => 'boolean',
        'is_present' => 'boolean',
        'is_holiday' => 'boolean',
        'is_weekend' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the employee associated with the attendance.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the shift associated with the attendance.
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get the creator of the attendance record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the approver of the attendance record.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the break periods associated with this attendance.
     */
    public function breakPeriods()
    {
        return $this->hasMany(BreakPeriod::class, 'attendance_record_id');
    }

    /**
     * Get the overtime records associated with this attendance.
     */
    public function overtimes()
    {
        return $this->hasMany(Overtime::class, 'attendance_record_id');
    }

    /**
     * Get alerts for this attendance.
     */
    public function alerts()
    {
        return $this->hasMany(AttendanceAlert::class, 'attendance_id');
    }

    /**
     * Calculate total work hours (check_out - check_in - breaks).
     */
    public function calculateTotalHours()
    {
        if ($this->check_in_time && $this->check_out_time) {
            $checkIn = \Carbon\Carbon::parse($this->check_in_time);
            $checkOut = \Carbon\Carbon::parse($this->check_out_time);
            $diff = $checkOut->diffInSeconds($checkIn) / 3600; // Convert to hours
            $breakHours = $this->break_hours ?? 0;
            return $diff - $breakHours;
        }
        return 0;
    }

    /**
     * Check if the attendance is late based on policy.
     */
    public function checkIfLate(AttendancePolicy $policy = null)
    {
        if (!$this->check_in_time) {
            return false;
        }

        $checkInTime = \Carbon\Carbon::parse($this->check_in_time);
        $shift = $this->shift;

        if ($shift && $shift->start_time) {
            $expectedTime = \Carbon\Carbon::parse($this->attendance_date . ' ' . $shift->start_time);
            
            if ($policy && $policy->late_arrival_grace) {
                $expectedTime->addMinutes($policy->late_arrival_grace);
            }

            return $checkInTime->gt($expectedTime);
        }

        return false;
    }

    /**
     * Scope to get attendance by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('attendance_date', [$startDate, $endDate]);
    }

    /**
     * Scope to get present employees.
     */
    public function scopePresent($query)
    {
        return $query->where('is_present', true)->where('is_absent', false);
    }

    /**
     * Scope to get absent employees.
     */
    public function scopeAbsent($query)
    {
        return $query->where('is_absent', true);
    }

    /**
     * Scope to get late employees.
     */
    public function scopeLate($query)
    {
        return $query->where('is_late', true);
    }

    /**
     * Scope to get pending approvals.
     */
    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }
}
