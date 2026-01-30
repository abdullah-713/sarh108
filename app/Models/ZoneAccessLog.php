<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZoneAccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_zone_id',
        'employee_id',
        'branch_id',
        'access_type',
        'access_time',
        'latitude',
        'longitude',
        'accuracy_meters',
        'device_id',
        'duration_minutes',
        'was_authorized',
        'denial_reason',
    ];

    protected $casts = [
        'access_time' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'was_authorized' => 'boolean',
    ];

    protected $appends = ['access_type_name'];

    // Access types
    public static array $accessTypes = [
        'entry' => 'دخول',
        'exit' => 'خروج',
    ];

    // Relationships
    public function workZone(): BelongsTo
    {
        return $this->belongsTo(WorkZone::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // Accessors
    public function getAccessTypeNameAttribute(): string
    {
        return self::$accessTypes[$this->access_type] ?? $this->access_type;
    }

    // Calculate duration on exit
    public static function calculateDuration(int $workZoneId, int $employeeId): ?int
    {
        $lastEntry = self::where('work_zone_id', $workZoneId)
            ->where('employee_id', $employeeId)
            ->where('access_type', 'entry')
            ->latest('access_time')
            ->first();

        if (!$lastEntry) {
            return null;
        }

        return now()->diffInMinutes($lastEntry->access_time);
    }

    // Get employee time in zone for date
    public static function getTimeInZone(int $workZoneId, int $employeeId, ?string $date = null): int
    {
        $date = $date ?: today()->toDateString();
        
        return self::where('work_zone_id', $workZoneId)
            ->where('employee_id', $employeeId)
            ->where('access_type', 'exit')
            ->whereDate('access_time', $date)
            ->sum('duration_minutes') ?? 0;
    }

    // Get unauthorized access attempts
    public static function getUnauthorizedAttempts(int $companyId, ?string $dateFrom = null, ?string $dateTo = null)
    {
        $query = self::whereHas('workZone', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->where('was_authorized', false);

        if ($dateFrom) {
            $query->whereDate('access_time', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('access_time', '<=', $dateTo);
        }

        return $query->with(['employee', 'workZone', 'branch'])->latest('access_time')->get();
    }
}
