<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BreakPeriod extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'break_periods';

    protected $fillable = [
        'attendance_record_id',
        'employee_id',
        'break_start',
        'break_end',
        'break_duration',
        'break_type',
        'reason',
        'is_approved',
        'exceeds_limit',
        'approval_status',
        'approved_by',
        'approved_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'break_start' => 'datetime',
        'break_end' => 'datetime',
        'break_duration' => 'float',
        'is_approved' => 'boolean',
        'exceeds_limit' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the attendance record associated with this break period.
     */
    public function attendanceRecord()
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    /**
     * Get the employee associated with this break period.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the approver of this break period.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the creator of this record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Calculate the actual break duration.
     */
    public function calculateDuration()
    {
        if ($this->break_start && $this->break_end) {
            $start = \Carbon\Carbon::parse($this->break_start);
            $end = \Carbon\Carbon::parse($this->break_end);
            return $end->diffInMinutes($start);
        }
        return 0;
    }

    /**
     * Check if break exceeds allowed limit.
     */
    public function checkIfExceedsLimit($maxBreakDuration = 60)
    {
        $actualDuration = $this->calculateDuration();
        return $actualDuration > $maxBreakDuration;
    }

    /**
     * Scope to get approved breaks.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope to get pending breaks.
     */
    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }

    /**
     * Scope to get breaks exceeding limit.
     */
    public function scopeExceedsLimit($query)
    {
        return $query->where('exceeds_limit', true);
    }

    /**
     * Scope to filter by break type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('break_type', $type);
    }
}
