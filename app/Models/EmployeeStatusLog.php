<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'status',
        'checkin_time',
        'checkout_time',
        'late_minutes',
        'early_leave_minutes',
        'worked_minutes',
        'deduction_points',
        'is_perfect_day',
    ];

    protected $casts = [
        'date' => 'date',
        'checkin_time' => 'datetime:H:i',
        'checkout_time' => 'datetime:H:i',
        'is_perfect_day' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * الحصول على سجل اليوم للموظف
     */
    public static function getTodayLog(int $employeeId): ?self
    {
        return self::where('employee_id', $employeeId)
            ->where('date', today())
            ->first();
    }

    /**
     * تحديث أو إنشاء سجل اليوم
     */
    public static function updateOrCreateToday(int $employeeId, array $data): self
    {
        return self::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'date' => today(),
            ],
            $data
        );
    }

    /**
     * الحصول على إحصائيات الموظف
     */
    public static function getEmployeeStats(int $employeeId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = self::where('employee_id', $employeeId);
        
        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }
        
        $logs = $query->get();
        
        return [
            'total_days' => $logs->count(),
            'present_days' => $logs->where('status', 'present')->count(),
            'late_days' => $logs->where('status', 'late')->count(),
            'absent_days' => $logs->where('status', 'absent')->count(),
            'perfect_days' => $logs->where('is_perfect_day', true)->count(),
            'total_late_minutes' => $logs->sum('late_minutes'),
            'total_worked_minutes' => $logs->sum('worked_minutes'),
            'total_deduction_points' => $logs->sum('deduction_points'),
            'attendance_rate' => $logs->count() > 0 
                ? round(($logs->whereIn('status', ['present', 'late'])->count() / $logs->count()) * 100, 2) 
                : 0,
        ];
    }

    /**
     * لون الحالة
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'present' => 'green',
            'late' => 'orange',
            'absent' => 'red',
            'on_leave' => 'blue',
            'holiday' => 'purple',
            default => 'gray',
        };
    }

    /**
     * اسم الحالة بالعربية
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'present' => 'حاضر',
            'late' => 'متأخر',
            'absent' => 'غائب',
            'on_leave' => 'إجازة',
            'holiday' => 'عطلة',
            default => 'غير محدد',
        };
    }
}
