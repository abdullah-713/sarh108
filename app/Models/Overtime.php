<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Overtime extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'overtime';

    protected $fillable = [
        'employee_id',
        'attendance_record_id',
        'overtime_date',
        'hours',
        'rate_per_hour',
        'total_amount',
        'overtime_type',
        'is_approved',
        'is_paid',
        'approval_status',
        'approved_by',
        'approved_at',
        'payment_status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'overtime_date' => 'date',
        'hours' => 'float',
        'rate_per_hour' => 'float',
        'total_amount' => 'float',
        'is_approved' => 'boolean',
        'is_paid' => 'boolean',
        'approved_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            // Auto-calculate total amount
            if ($model->hours && $model->rate_per_hour) {
                $model->total_amount = $model->hours * $model->rate_per_hour;
            }
        });
    }

    /**
     * Get the employee associated with this overtime.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the attendance record associated with this overtime.
     */
    public function attendanceRecord()
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    /**
     * Get the approver of this overtime.
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
     * Approve the overtime.
     */
    public function approve($approvedBy)
    {
        $this->update([
            'is_approved' => true,
            'approval_status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);
    }

    /**
     * Reject the overtime.
     */
    public function reject($approvedBy)
    {
        $this->update([
            'is_approved' => false,
            'approval_status' => 'rejected',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);
    }

    /**
     * Mark as paid.
     */
    public function markAsPaid()
    {
        $this->update([
            'is_paid' => true,
            'payment_status' => 'paid',
        ]);
    }

    /**
     * Scope to get approved overtimes.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope to get paid overtimes.
     */
    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    /**
     * Scope to get pending approvals.
     */
    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }

    /**
     * Scope to get pending payments.
     */
    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Scope to filter by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('overtime_type', $type);
    }

    /**
     * Scope to get overtime by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('overtime_date', [$startDate, $endDate]);
    }
}
