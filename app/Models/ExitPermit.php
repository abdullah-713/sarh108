<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ExitPermit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'employee_id',
        'branch_id',
        'department_id',
        'permit_type',
        'permit_date',
        'exit_time',
        'expected_return_time',
        'actual_return_time',
        'reason',
        'destination',
        'requires_vehicle',
        'vehicle_number',
        'status',
        'approved_by',
        'approved_at',
        'approval_note',
        'rejection_reason',
        'is_extended',
        'extended_return_time',
        'extension_reason',
        'extension_approved_by',
        'total_minutes_out',
        'affects_attendance',
        'qr_code',
    ];

    protected $casts = [
        'permit_date' => 'date',
        'exit_time' => 'datetime',
        'expected_return_time' => 'datetime',
        'actual_return_time' => 'datetime',
        'approved_at' => 'datetime',
        'extended_return_time' => 'datetime',
        'requires_vehicle' => 'boolean',
        'is_extended' => 'boolean',
        'affects_attendance' => 'boolean',
    ];

    protected $appends = ['permit_type_name', 'status_name', 'status_color'];

    // Permit types
    public static array $permitTypes = [
        'personal' => 'شخصي',
        'official' => 'رسمي',
        'medical' => 'طبي',
        'emergency' => 'طارئ',
        'other' => 'أخرى',
    ];

    // Statuses
    public static array $statuses = [
        'pending' => ['name' => 'بانتظار الموافقة', 'color' => 'bg-yellow-100 text-yellow-800'],
        'approved' => ['name' => 'موافق عليه', 'color' => 'bg-green-100 text-green-800'],
        'rejected' => ['name' => 'مرفوض', 'color' => 'bg-red-100 text-red-800'],
        'cancelled' => ['name' => 'ملغي', 'color' => 'bg-gray-100 text-gray-800'],
        'used' => ['name' => 'مستخدم', 'color' => 'bg-blue-100 text-blue-800'],
        'expired' => ['name' => 'منتهي الصلاحية', 'color' => 'bg-gray-100 text-gray-500'],
    ];

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->qr_code) {
                $model->qr_code = 'EP-' . strtoupper(Str::random(12));
            }
        });
    }

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function extensionApprovedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'extension_approved_by');
    }

    // Accessors
    public function getPermitTypeNameAttribute(): string
    {
        return self::$permitTypes[$this->permit_type] ?? $this->permit_type;
    }

    public function getStatusNameAttribute(): string
    {
        return self::$statuses[$this->status]['name'] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::$statuses[$this->status]['color'] ?? 'bg-gray-100 text-gray-800';
    }

    // Check if permit can be used
    public function canBeUsed(): bool
    {
        return $this->status === 'approved' && 
               $this->permit_date->isToday() &&
               !$this->actual_return_time;
    }

    // Approve permit
    public function approve(int $approvedBy, ?string $note = null): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'approval_note' => $note,
        ]);
    }

    // Reject permit
    public function reject(int $rejectedBy, string $reason): bool
    {
        return $this->update([
            'status' => 'rejected',
            'approved_by' => $rejectedBy,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    // Mark as used (employee left)
    public function markAsUsed(): bool
    {
        return $this->update([
            'status' => 'used',
        ]);
    }

    // Record return
    public function recordReturn(): bool
    {
        $returnTime = now();
        $exitTime = \Carbon\Carbon::parse($this->permit_date->format('Y-m-d') . ' ' . $this->exit_time->format('H:i:s'));
        
        return $this->update([
            'actual_return_time' => $returnTime,
            'total_minutes_out' => $returnTime->diffInMinutes($exitTime),
        ]);
    }

    // Request extension
    public function requestExtension(string $newReturnTime, string $reason): bool
    {
        return $this->update([
            'is_extended' => true,
            'extended_return_time' => $newReturnTime,
            'extension_reason' => $reason,
        ]);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeForToday($query)
    {
        return $query->whereDate('permit_date', today());
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    // Count permits for employee
    public static function countForEmployee(int $employeeId, string $period = 'month'): int
    {
        $query = self::where('employee_id', $employeeId)
            ->whereIn('status', ['approved', 'used']);

        if ($period === 'day') {
            $query->whereDate('permit_date', today());
        } elseif ($period === 'month') {
            $query->whereMonth('permit_date', now()->month)
                  ->whereYear('permit_date', now()->year);
        }

        return $query->count();
    }
}
