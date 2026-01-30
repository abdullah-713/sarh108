<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeductionTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'min_minutes',
        'max_minutes',
        'deduction_points',
        'deduction_percentage',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'deduction_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * الحصول على مستوى الخصم حسب دقائق التأخير
     */
    public static function getTierByMinutes(int $minutes): ?self
    {
        return self::where('is_active', true)
            ->where('min_minutes', '<=', $minutes)
            ->where('max_minutes', '>=', $minutes)
            ->first();
    }

    /**
     * حساب الخصم
     */
    public static function calculateDeduction(int $lateMinutes): array
    {
        $tier = self::getTierByMinutes($lateMinutes);
        
        if (!$tier) {
            // إذا تجاوز جميع المستويات، استخدم أعلى مستوى
            $tier = self::where('is_active', true)
                ->orderBy('max_minutes', 'desc')
                ->first();
        }
        
        return [
            'tier_id' => $tier?->id,
            'tier_name' => $tier?->name ?? 'غير محدد',
            'points' => $tier?->deduction_points ?? 0,
            'percentage' => $tier?->deduction_percentage ?? 0,
            'late_minutes' => $lateMinutes,
        ];
    }

    /**
     * الحصول على جميع المستويات النشطة مرتبة
     */
    public static function getActiveTiers(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('is_active', true)
            ->orderBy('min_minutes', 'asc')
            ->get();
    }
}
