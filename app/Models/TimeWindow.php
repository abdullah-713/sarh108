<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeWindow extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'start_time',
        'end_time',
        'grace_period',
        'branch_id',
        'shift_id',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * التحقق من الوقت الحالي ضمن النافذة
     */
    public function isCurrentlyOpen(): bool
    {
        $now = now()->format('H:i:s');
        $start = $this->start_time->format('H:i:s');
        $end = $this->end_time->format('H:i:s');
        
        return $now >= $start && $now <= $end;
    }

    /**
     * التحقق من الوقت مع فترة السماح
     */
    public function isWithinGracePeriod(): bool
    {
        $now = now();
        $endWithGrace = $this->end_time->copy()->addMinutes($this->grace_period);
        
        return $now->format('H:i:s') <= $endWithGrace->format('H:i:s');
    }

    /**
     * حساب دقائق التأخير
     */
    public function calculateLateMinutes(): int
    {
        $now = now();
        $expectedEnd = $this->end_time;
        
        if ($now->format('H:i:s') > $expectedEnd->format('H:i:s')) {
            return $now->diffInMinutes($expectedEnd);
        }
        
        return 0;
    }

    /**
     * الحصول على النافذة النشطة للفرع
     */
    public static function getActiveWindow(int $branchId, string $type = 'checkin'): ?self
    {
        return self::where('branch_id', $branchId)
            ->orWhereNull('branch_id')
            ->where('type', $type)
            ->where('is_active', true)
            ->first();
    }
}
