<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SentimentAnalysis extends Model
{
    use HasFactory;

    protected $table = 'sentiment_analyses';

    protected $fillable = [
        'employee_id',
        'branch_id',
        'department_id',
        'source_type',
        'source_id',
        'sentiment',
        'sentiment_score',
        'confidence_score',
        'emotions',
        'primary_emotion',
        'engagement_level',
        'satisfaction_score',
        'is_concerning',
        'risk_indicators',
        'concerns_summary',
        'recommendations',
        'action_items',
        'requires_followup',
        'assigned_to',
        'followup_date',
        'followup_status',
        'followup_notes',
        'analysis_date',
        'period_type',
        'created_by',
    ];

    protected $casts = [
        'emotions' => 'array',
        'risk_indicators' => 'array',
        'recommendations' => 'array',
        'is_concerning' => 'boolean',
        'requires_followup' => 'boolean',
        'analysis_date' => 'date',
        'followup_date' => 'datetime',
        'sentiment_score' => 'decimal:2',
        'confidence_score' => 'decimal:2',
        'engagement_level' => 'decimal:2',
        'satisfaction_score' => 'decimal:2',
    ];

    // مستويات المشاعر
    const SENTIMENTS = [
        'very_positive' => ['name' => 'إيجابي جداً', 'color' => '#10b981', 'emoji' => '😊'],
        'positive' => ['name' => 'إيجابي', 'color' => '#22c55e', 'emoji' => '🙂'],
        'neutral' => ['name' => 'محايد', 'color' => '#6b7280', 'emoji' => '😐'],
        'negative' => ['name' => 'سلبي', 'color' => '#f97316', 'emoji' => '😕'],
        'very_negative' => ['name' => 'سلبي جداً', 'color' => '#ef4444', 'emoji' => '😞'],
    ];

    // المشاعر الأساسية
    const EMOTIONS = [
        'happiness' => 'السعادة',
        'satisfaction' => 'الرضا',
        'motivation' => 'التحفيز',
        'stress' => 'التوتر',
        'frustration' => 'الإحباط',
        'burnout' => 'الإرهاق',
        'anxiety' => 'القلق',
        'engagement' => 'التفاعل',
    ];

    // مصادر التحليل
    const SOURCE_TYPES = [
        'attendance_pattern' => 'نمط الحضور',
        'performance_review' => 'تقييم الأداء',
        'survey_response' => 'استجابة استبيان',
        'feedback' => 'ملاحظات',
        'complaint' => 'شكوى',
        'manual_entry' => 'إدخال يدوي',
        'ai_analysis' => 'تحليل ذكي',
    ];

    // العلاقات
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

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Attributes
    public function getSentimentNameAttribute(): string
    {
        return self::SENTIMENTS[$this->sentiment]['name'] ?? $this->sentiment;
    }

    public function getSentimentColorAttribute(): string
    {
        return self::SENTIMENTS[$this->sentiment]['color'] ?? '#6b7280';
    }

    public function getSentimentEmojiAttribute(): string
    {
        return self::SENTIMENTS[$this->sentiment]['emoji'] ?? '😐';
    }

    public function getSourceTypeNameAttribute(): string
    {
        return self::SOURCE_TYPES[$this->source_type] ?? $this->source_type;
    }

    public function getPrimaryEmotionNameAttribute(): string
    {
        return self::EMOTIONS[$this->primary_emotion] ?? $this->primary_emotion;
    }

    // Scopes
    public function scopeConcerning($query)
    {
        return $query->where('is_concerning', true);
    }

    public function scopeRequiresFollowup($query)
    {
        return $query->where('requires_followup', true)
            ->whereIn('followup_status', ['pending', 'in_progress']);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeNegative($query)
    {
        return $query->whereIn('sentiment', ['negative', 'very_negative']);
    }

    // تحليل نمط الحضور للموظف
    public static function analyzeAttendancePattern(Employee $employee): self
    {
        // جمع البيانات
        $lateCount = $employee->attendances()
            ->where('status', 'late')
            ->where('date', '>=', Carbon::now()->subDays(30))
            ->count();

        $absentCount = $employee->attendances()
            ->where('status', 'absent')
            ->where('date', '>=', Carbon::now()->subDays(30))
            ->count();

        $currentStreak = $employee->current_streak ?? 0;

        // حساب النتيجة
        $score = 50; // محايد
        $sentiment = 'neutral';
        $emotions = [];

        if ($lateCount == 0 && $absentCount == 0 && $currentStreak >= 10) {
            $score = 85;
            $sentiment = 'very_positive';
            $emotions = ['engagement' => 0.9, 'motivation' => 0.85, 'satisfaction' => 0.8];
        } elseif ($lateCount <= 2 && $absentCount <= 1) {
            $score = 65;
            $sentiment = 'positive';
            $emotions = ['engagement' => 0.7, 'satisfaction' => 0.65];
        } elseif ($lateCount >= 5 || $absentCount >= 3) {
            $score = 25;
            $sentiment = 'negative';
            $emotions = ['stress' => 0.6, 'frustration' => 0.5];
        } elseif ($lateCount >= 8 || $absentCount >= 5) {
            $score = 10;
            $sentiment = 'very_negative';
            $emotions = ['burnout' => 0.7, 'stress' => 0.8];
        }

        $primaryEmotion = !empty($emotions) ? array_key_first($emotions) : 'neutral';
        $isConcerning = in_array($sentiment, ['negative', 'very_negative']);

        return self::create([
            'employee_id' => $employee->id,
            'branch_id' => $employee->branch_id,
            'department_id' => $employee->department_id,
            'source_type' => 'attendance_pattern',
            'sentiment' => $sentiment,
            'sentiment_score' => $score,
            'confidence_score' => 75,
            'emotions' => $emotions,
            'primary_emotion' => $primaryEmotion,
            'engagement_level' => $emotions['engagement'] ?? 0.5,
            'satisfaction_score' => $emotions['satisfaction'] ?? 0.5,
            'is_concerning' => $isConcerning,
            'risk_indicators' => $isConcerning ? [
                'late_frequency' => $lateCount,
                'absent_frequency' => $absentCount,
                'streak_broken' => $currentStreak == 0,
            ] : null,
            'concerns_summary' => $isConcerning 
                ? "ملاحظة نمط حضور غير منتظم: {$lateCount} تأخير، {$absentCount} غياب خلال 30 يوم"
                : null,
            'requires_followup' => $isConcerning,
            'analysis_date' => Carbon::today(),
            'period_type' => 'monthly',
        ]);
    }

    // الحصول على ملخص المشاعر للشركة
    public static function getCompanySummary(array $companyUserIds, $startDate = null, $endDate = null): array
    {
        $query = self::whereHas('employee', function ($q) use ($companyUserIds) {
            $q->whereIn('created_by', $companyUserIds);
        });

        if ($startDate) {
            $query->where('analysis_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('analysis_date', '<=', $endDate);
        }

        $total = $query->count();
        $bySentiment = (clone $query)
            ->selectRaw('sentiment, COUNT(*) as count')
            ->groupBy('sentiment')
            ->pluck('count', 'sentiment')
            ->toArray();

        $avgScore = (clone $query)->avg('sentiment_score') ?? 50;
        $concerningCount = (clone $query)->concerning()->count();
        $requiresFollowup = (clone $query)->requiresFollowup()->count();

        return [
            'total_analyses' => $total,
            'by_sentiment' => $bySentiment,
            'average_score' => round($avgScore, 2),
            'concerning_count' => $concerningCount,
            'requires_followup' => $requiresFollowup,
            'overall_sentiment' => self::getOverallSentiment($avgScore),
        ];
    }

    protected static function getOverallSentiment(float $score): string
    {
        if ($score >= 80) return 'very_positive';
        if ($score >= 60) return 'positive';
        if ($score >= 40) return 'neutral';
        if ($score >= 20) return 'negative';
        return 'very_negative';
    }
}
