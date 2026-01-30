<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeBadge extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'badge_id',
        'awarded_date',
        'period',
        'reason',
        'awarded_by',
        'is_displayed',
    ];

    protected $casts = [
        'awarded_date' => 'date',
        'is_displayed' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    public function awardedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }

    /**
     * الحصول على شارات الموظف
     */
    public static function getEmployeeBadges($employeeId)
    {
        return self::where('employee_id', $employeeId)
            ->with('badge')
            ->orderBy('awarded_date', 'desc')
            ->get();
    }

    /**
     * منح شارة لموظف
     */
    public static function awardBadge($employeeId, $badgeId, $period = null, $reason = null, $awardedBy = null): self
    {
        return self::create([
            'employee_id' => $employeeId,
            'badge_id' => $badgeId,
            'awarded_date' => now(),
            'period' => $period ?? now()->format('Y-m'),
            'reason' => $reason,
            'awarded_by' => $awardedBy,
        ]);
    }

    /**
     * التحقق مما إذا كان الموظف يمتلك شارة معينة
     */
    public static function hasBadge($employeeId, $badgeId, $period = null): bool
    {
        $query = self::where('employee_id', $employeeId)
            ->where('badge_id', $badgeId);

        if ($period) {
            $query->where('period', $period);
        }

        return $query->exists();
    }
}
