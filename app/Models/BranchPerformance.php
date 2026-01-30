<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchPerformance extends Model
{
    use HasFactory;

    protected $table = 'branch_performance';

    protected $fillable = [
        'branch_id',
        'date',
        'total_employees',
        'present_count',
        'late_count',
        'absent_count',
        'on_leave_count',
        'attendance_rate',
        'punctuality_rate',
        'early_arrival_rate',
        'total_late_minutes',
        'avg_late_minutes',
        'performance_score',
        'rank',
        'rank_change',
        'perfect_days_count',
        'streak_days',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'attendance_rate' => 'decimal:2',
        'punctuality_rate' => 'decimal:2',
        'early_arrival_rate' => 'decimal:2',
        'avg_late_minutes' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * حساب درجة الأداء
     */
    public static function calculateScore(array $data): int
    {
        $score = 0;
        
        // نسبة الحضور (40 نقطة)
        $score += ($data['attendance_rate'] ?? 0) * 0.4;
        
        // نسبة الالتزام بالوقت (35 نقطة)
        $score += ($data['punctuality_rate'] ?? 0) * 0.35;
        
        // نسبة الوصول المبكر (15 نقطة)
        $score += ($data['early_arrival_rate'] ?? 0) * 0.15;
        
        // مكافأة الأيام المثالية (10 نقاط)
        $perfectBonus = min(10, ($data['perfect_days_count'] ?? 0) * 2);
        $score += $perfectBonus;
        
        return min(100, max(0, round($score)));
    }

    /**
     * الحصول على ترتيب الفروع لتاريخ معين
     */
    public static function getRankingForDate($date, $companyUserIds = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = self::where('date', $date)
            ->with('branch')
            ->orderBy('performance_score', 'desc')
            ->orderBy('punctuality_rate', 'desc');

        if ($companyUserIds) {
            $query->whereHas('branch', function ($q) use ($companyUserIds) {
                $q->whereIn('created_by', $companyUserIds);
            });
        }

        return $query->get();
    }

    /**
     * تحديث ترتيب الفروع
     */
    public static function updateRankings($date): void
    {
        $performances = self::where('date', $date)
            ->orderBy('performance_score', 'desc')
            ->get();

        $rank = 1;
        foreach ($performances as $performance) {
            // حساب التغير عن اليوم السابق
            $yesterday = self::where('branch_id', $performance->branch_id)
                ->where('date', $performance->date->subDay())
                ->first();
            
            $previousRank = $yesterday ? $yesterday->rank : $rank;
            $rankChange = $previousRank - $rank;

            $performance->update([
                'rank' => $rank,
                'rank_change' => $rankChange,
            ]);
            
            $rank++;
        }
    }

    /**
     * الحصول على إحصائيات الفرع للفترة
     */
    public static function getBranchStats($branchId, $startDate, $endDate): array
    {
        $performances = self::where('branch_id', $branchId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        if ($performances->isEmpty()) {
            return [
                'avg_attendance_rate' => 0,
                'avg_punctuality_rate' => 0,
                'avg_score' => 0,
                'total_late_minutes' => 0,
                'perfect_days' => 0,
                'best_rank' => null,
                'current_streak' => 0,
            ];
        }

        return [
            'avg_attendance_rate' => round($performances->avg('attendance_rate'), 2),
            'avg_punctuality_rate' => round($performances->avg('punctuality_rate'), 2),
            'avg_score' => round($performances->avg('performance_score'), 0),
            'total_late_minutes' => $performances->sum('total_late_minutes'),
            'perfect_days' => $performances->where('late_count', 0)->where('absent_count', 0)->count(),
            'best_rank' => $performances->min('rank'),
            'current_streak' => $performances->last()?->streak_days ?? 0,
        ];
    }

    /**
     * الحصول على لون الترتيب
     */
    public function getRankColorAttribute(): string
    {
        return match(true) {
            $this->rank === 1 => 'gold',
            $this->rank === 2 => 'silver',
            $this->rank === 3 => 'bronze',
            $this->rank <= 5 => 'green',
            $this->rank <= 10 => 'blue',
            default => 'gray',
        };
    }

    /**
     * الحصول على أيقونة التغير
     */
    public function getRankChangeIconAttribute(): string
    {
        return match(true) {
            $this->rank_change > 0 => 'arrow-up',
            $this->rank_change < 0 => 'arrow-down',
            default => 'minus',
        };
    }
}
