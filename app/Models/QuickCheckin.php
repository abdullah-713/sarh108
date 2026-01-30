<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuickCheckin extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'branch_id',
        'type',
        'checked_at',
        'latitude',
        'longitude',
        'wifi_ssid',
        'wifi_bssid',
        'verification_method',
        'is_verified',
        'late_minutes',
        'notes',
        'device_info',
        'ip_address',
        'verified_by',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_verified' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * التحقق من وجود تسجيل اليوم
     */
    public static function hasTodayCheckin(int $employeeId, string $type = 'checkin'): bool
    {
        return self::where('employee_id', $employeeId)
            ->where('type', $type)
            ->whereDate('checked_at', today())
            ->exists();
    }

    /**
     * الحصول على آخر تسجيل للموظف
     */
    public static function getLastCheckin(int $employeeId): ?self
    {
        return self::where('employee_id', $employeeId)
            ->orderBy('checked_at', 'desc')
            ->first();
    }

    /**
     * الحصول على تسجيلات اليوم للفرع
     */
    public static function getTodayByBranch(int $branchId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('branch_id', $branchId)
            ->whereDate('checked_at', today())
            ->with('employee')
            ->orderBy('checked_at', 'desc')
            ->get();
    }

    /**
     * الحالة بناءً على التأخير
     */
    public function getStatusAttribute(): string
    {
        if ($this->late_minutes == 0) {
            return 'on_time';
        } elseif ($this->late_minutes <= 15) {
            return 'slightly_late';
        } elseif ($this->late_minutes <= 45) {
            return 'late';
        } else {
            return 'very_late';
        }
    }

    /**
     * لون الحالة
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'on_time' => 'green',
            'slightly_late' => 'yellow',
            'late' => 'orange',
            'very_late' => 'red',
            default => 'gray',
        };
    }
}
