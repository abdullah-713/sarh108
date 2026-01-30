<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class RiskPrediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'branch_id',
        'risk_type',
        'severity',
        'confidence_score',
        'risk_score',
        'factors',
        'historical_data',
        'predicted_date',
        'prediction_reason',
        'recommended_action',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'action_taken',
        'was_accurate',
        'outcome_date',
        'outcome_notes',
        'created_by',
    ];

    protected $casts = [
        'factors' => 'array',
        'historical_data' => 'array',
        'predicted_date' => 'date',
        'reviewed_at' => 'datetime',
        'outcome_date' => 'datetime',
        'was_accurate' => 'boolean',
        'confidence_score' => 'decimal:2',
        'risk_score' => 'decimal:2',
    ];

    // أنواع المخاطر
    const RISK_TYPES = [
        'absence' => 'غياب متوقع',
        'late' => 'تأخير متوقع',
        'resignation' => 'استقالة محتملة',
        'burnout' => 'إرهاق',
        'pattern_break' => 'كسر النمط',
    ];

    // مستويات الخطورة
    const SEVERITIES = [
        'low' => ['name' => 'منخفضة', 'color' => '#10b981'],
        'medium' => ['name' => 'متوسطة', 'color' => '#f59e0b'],
        'high' => ['name' => 'عالية', 'color' => '#f97316'],
        'critical' => ['name' => 'حرجة', 'color' => '#ef4444'],
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Attributes
    public function getRiskTypeNameAttribute(): string
    {
        return self::RISK_TYPES[$this->risk_type] ?? $this->risk_type;
    }

    public function getSeverityNameAttribute(): string
    {
        return self::SEVERITIES[$this->severity]['name'] ?? $this->severity;
    }

    public function getSeverityColorAttribute(): string
    {
        return self::SEVERITIES[$this->severity]['color'] ?? '#6b7280';
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeHighRisk($query)
    {
        return $query->whereIn('severity', ['high', 'critical']);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeUpcoming($query, $days = 7)
    {
        return $query->whereBetween('predicted_date', [
            Carbon::today(),
            Carbon::today()->addDays($days),
        ]);
    }

    // توليد توقع جديد
    public static function generatePrediction(Employee $employee, string $riskType, array $factors): self
    {
        $riskScore = self::calculateRiskScore($factors);
        $severity = self::determineSeverity($riskScore);

        return self::create([
            'employee_id' => $employee->id,
            'branch_id' => $employee->branch_id,
            'risk_type' => $riskType,
            'severity' => $severity,
            'confidence_score' => $factors['confidence'] ?? 50,
            'risk_score' => $riskScore,
            'factors' => $factors,
            'predicted_date' => $factors['predicted_date'] ?? Carbon::today()->addDays(rand(1, 7)),
            'prediction_reason' => self::generateReason($riskType, $factors),
            'recommended_action' => self::generateRecommendation($riskType, $severity),
            'status' => 'pending',
        ]);
    }

    // حساب درجة المخاطرة
    protected static function calculateRiskScore(array $factors): float
    {
        $score = 0;
        $weights = [
            'absence_rate' => 30,
            'late_rate' => 20,
            'streak_break' => 15,
            'pattern_deviation' => 20,
            'historical_issues' => 15,
        ];

        foreach ($weights as $factor => $weight) {
            if (isset($factors[$factor])) {
                $score += ($factors[$factor] / 100) * $weight;
            }
        }

        return min(100, max(0, $score));
    }

    // تحديد مستوى الخطورة
    protected static function determineSeverity(float $riskScore): string
    {
        if ($riskScore >= 80) return 'critical';
        if ($riskScore >= 60) return 'high';
        if ($riskScore >= 40) return 'medium';
        return 'low';
    }

    // توليد سبب التوقع
    protected static function generateReason(string $riskType, array $factors): string
    {
        $reasons = [
            'absence' => 'استناداً إلى نمط الغياب السابق، يُتوقع غياب قريب',
            'late' => 'نمط التأخير يشير إلى احتمالية تأخير مستقبلي',
            'resignation' => 'مؤشرات عدم الرضا ونمط الانخفاض في الأداء',
            'burnout' => 'ساعات عمل إضافية متكررة مع انخفاض في الإنتاجية',
            'pattern_break' => 'تغيير ملحوظ في نمط الحضور المعتاد',
        ];

        return $reasons[$riskType] ?? 'توقع بناءً على تحليل البيانات';
    }

    // توليد توصية
    protected static function generateRecommendation(string $riskType, string $severity): string
    {
        $recommendations = [
            'absence' => [
                'low' => 'مراقبة الوضع',
                'medium' => 'التواصل مع الموظف للتأكد من عدم وجود مشاكل',
                'high' => 'اجتماع عاجل مع الموظف',
                'critical' => 'إجراء فوري مطلوب - تواصل مع المدير المباشر',
            ],
            'late' => [
                'low' => 'تذكير بأهمية الالتزام',
                'medium' => 'مناقشة أسباب التأخير',
                'high' => 'إنذار رسمي',
                'critical' => 'اجتماع تأديبي',
            ],
            'resignation' => [
                'low' => 'مراقبة رضا الموظف',
                'medium' => 'استبيان رضا الموظفين',
                'high' => 'مقابلة احتفاظ عاجلة',
                'critical' => 'تدخل إداري فوري مع عرض حوافز',
            ],
        ];

        return $recommendations[$riskType][$severity] 
            ?? 'مراجعة الوضع واتخاذ الإجراء المناسب';
    }

    // الحصول على التوقعات النشطة للشركة
    public static function getActiveForCompany(array $companyUserIds, ?string $status = null, ?string $severity = null)
    {
        $query = self::whereHas('employee', function ($q) use ($companyUserIds) {
            $q->whereIn('created_by', $companyUserIds);
        })->with(['employee', 'branch']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($severity) {
            $query->where('severity', $severity);
        }

        return $query->orderBy('risk_score', 'desc')
            ->orderBy('predicted_date')
            ->get();
    }
}
