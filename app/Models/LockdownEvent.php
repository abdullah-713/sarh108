<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LockdownEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'branch_id',
        'title',
        'title_ar',
        'description',
        'lockdown_type',
        'status',
        'start_time',
        'end_time',
        'actual_end_time',
        'initiated_by',
        'ended_by',
        'allow_emergency_checkin',
        'allow_emergency_checkout',
        'exempt_employees',
        'exempt_departments',
        'exempt_designations',
        'notification_message',
        'notification_message_ar',
        'notify_employees',
        'notify_managers',
        'end_reason',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'actual_end_time' => 'datetime',
        'allow_emergency_checkin' => 'boolean',
        'allow_emergency_checkout' => 'boolean',
        'exempt_employees' => 'array',
        'exempt_departments' => 'array',
        'exempt_designations' => 'array',
        'notify_employees' => 'boolean',
        'notify_managers' => 'boolean',
    ];

    protected $appends = ['lockdown_type_name', 'status_name', 'status_color'];

    // Lockdown types
    public static array $lockdownTypes = [
        'full' => 'إغلاق كامل',
        'partial' => 'إغلاق جزئي',
        'checkin_only' => 'منع التسجيل فقط',
        'checkout_only' => 'منع الخروج فقط',
        'emergency' => 'طوارئ',
    ];

    // Statuses
    public static array $statuses = [
        'scheduled' => ['name' => 'مجدول', 'color' => 'bg-blue-100 text-blue-800'],
        'active' => ['name' => 'نشط', 'color' => 'bg-red-100 text-red-800'],
        'ended' => ['name' => 'منتهي', 'color' => 'bg-green-100 text-green-800'],
        'cancelled' => ['name' => 'ملغي', 'color' => 'bg-gray-100 text-gray-800'],
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function ender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ended_by');
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(LockdownAttendanceLog::class);
    }

    // Accessors
    public function getLockdownTypeNameAttribute(): string
    {
        return self::$lockdownTypes[$this->lockdown_type] ?? $this->lockdown_type;
    }

    public function getStatusNameAttribute(): string
    {
        return self::$statuses[$this->status]['name'] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::$statuses[$this->status]['color'] ?? 'bg-gray-100 text-gray-800';
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->title_ar ?: $this->title;
    }

    // Check if employee is exempt
    public function isEmployeeExempt(Employee $employee): bool
    {
        // Check specific employees
        if (!empty($this->exempt_employees) && in_array($employee->id, $this->exempt_employees)) {
            return true;
        }

        // Check departments
        if (!empty($this->exempt_departments) && in_array($employee->department_id, $this->exempt_departments)) {
            return true;
        }

        // Check designations
        if (!empty($this->exempt_designations) && in_array($employee->designation_id, $this->exempt_designations)) {
            return true;
        }

        return false;
    }

    // Check if action is allowed
    public function isActionAllowed(string $action, Employee $employee): bool
    {
        // If employee is exempt, allow
        if ($this->isEmployeeExempt($employee)) {
            return true;
        }

        // Check based on lockdown type
        switch ($this->lockdown_type) {
            case 'full':
                return false;
            case 'checkin_only':
                return $action !== 'checkin';
            case 'checkout_only':
                return $action !== 'checkout';
            case 'partial':
                // Allow emergency actions
                if ($action === 'checkin' && $this->allow_emergency_checkin) {
                    return true;
                }
                if ($action === 'checkout' && $this->allow_emergency_checkout) {
                    return true;
                }
                return false;
            case 'emergency':
                return $this->allow_emergency_checkout && $action === 'checkout';
            default:
                return false;
        }
    }

    // Start lockdown
    public function activate(): bool
    {
        return $this->update(['status' => 'active']);
    }

    // End lockdown
    public function end(int $endedBy, ?string $reason = null): bool
    {
        return $this->update([
            'status' => 'ended',
            'ended_by' => $endedBy,
            'actual_end_time' => now(),
            'end_reason' => $reason,
        ]);
    }

    // Cancel lockdown
    public function cancel(int $cancelledBy, ?string $reason = null): bool
    {
        return $this->update([
            'status' => 'cancelled',
            'ended_by' => $cancelledBy,
            'end_reason' => $reason,
        ]);
    }

    // Log attendance attempt during lockdown
    public function logAttempt(Employee $employee, string $actionType, bool $wasAllowed, ?array $location = null, ?string $overrideReason = null, ?int $overriddenBy = null): void
    {
        $this->attendanceLogs()->create([
            'employee_id' => $employee->id,
            'action_type' => $wasAllowed ? "emergency_{$actionType}" : "blocked_{$actionType}",
            'attempted_at' => now(),
            'latitude' => $location['latitude'] ?? null,
            'longitude' => $location['longitude'] ?? null,
            'device_id' => $location['device_id'] ?? null,
            'was_allowed' => $wasAllowed,
            'override_reason' => $overrideReason,
            'overridden_by' => $overriddenBy,
        ]);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForBranch($query, ?int $branchId)
    {
        return $query->where(function ($q) use ($branchId) {
            $q->whereNull('branch_id') // All branches
              ->orWhere('branch_id', $branchId);
        });
    }

    // Get active lockdown for company/branch
    public static function getActiveLockdown(int $companyId, ?int $branchId = null): ?self
    {
        return self::where('company_id', $companyId)
            ->where('status', 'active')
            ->where(function ($q) use ($branchId) {
                $q->whereNull('branch_id');
                if ($branchId) {
                    $q->orWhere('branch_id', $branchId);
                }
            })
            ->first();
    }

    // Create new lockdown
    public static function createLockdown(array $data): self
    {
        $lockdown = self::create($data);

        // Activate immediately if start_time is now or past
        if ($lockdown->start_time->isPast()) {
            $lockdown->activate();
        }

        return $lockdown;
    }
}
