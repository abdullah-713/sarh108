<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkHoursSetting extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'work_hours_settings';

    protected $fillable = [
        'department_id',
        'shift_id',
        'daily_working_hours',
        'shift_start_time',
        'shift_end_time',
        'late_arrival_grace',
        'early_departure_grace',
        'break_duration',
        'max_breaks_per_day',
        'max_break_duration',
        'allow_flexible_timing',
        'overtime_rate_per_hour',
        'overtime_rate_holiday',
        'is_active',
        'description',
        'created_by',
    ];

    protected $casts = [
        'daily_working_hours' => 'float',
        'shift_start_time' => 'datetime:H:i',
        'shift_end_time' => 'datetime:H:i',
        'late_arrival_grace' => 'float',
        'early_departure_grace' => 'float',
        'break_duration' => 'float',
        'max_break_duration' => 'float',
        'allow_flexible_timing' => 'boolean',
        'overtime_rate_per_hour' => 'float',
        'overtime_rate_holiday' => 'float',
        'is_active' => 'boolean',
    ];

    /**
     * Get the department associated with this setting.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the shift associated with this setting.
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get the creator of this setting.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get active settings.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get settings for a specific department.
     */
    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Get settings for a specific shift.
     */
    public function scopeForShift($query, $shiftId)
    {
        return $query->where('shift_id', $shiftId);
    }

    /**
     * Calculate if an arrival time is late.
     */
    public function isLateArrival($checkInTime)
    {
        $shiftStart = \Carbon\Carbon::parse($this->shift_start_time);
        $checkIn = \Carbon\Carbon::parse($checkInTime);
        $graceMinutes = $this->late_arrival_grace;

        return $checkIn->gt($shiftStart->addMinutes($graceMinutes));
    }

    /**
     * Calculate if a departure time is early.
     */
    public function isEarlyDeparture($checkOutTime)
    {
        $shiftEnd = \Carbon\Carbon::parse($this->shift_end_time);
        $checkOut = \Carbon\Carbon::parse($checkOutTime);
        $graceMinutes = $this->early_departure_grace;

        return $checkOut->lt($shiftEnd->subMinutes($graceMinutes));
    }
}
