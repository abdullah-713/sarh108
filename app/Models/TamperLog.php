<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TamperLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'user_id',
        'branch_id',
        'tamper_type',
        'severity',
        'confidence_score',
        'detection_details',
        'description',
        'device_id',
        'device_model',
        'os_version',
        'is_rooted',
        'is_emulator',
        'reported_latitude',
        'reported_longitude',
        'actual_latitude',
        'actual_longitude',
        'location_discrepancy_meters',
        'ip_address',
        'ip_country',
        'ip_city',
        'is_vpn',
        'is_proxy',
        'is_tor',
        'action_taken',
        'review_status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'detection_details' => 'array',
        'is_rooted' => 'boolean',
        'is_emulator' => 'boolean',
        'is_vpn' => 'boolean',
        'is_proxy' => 'boolean',
        'is_tor' => 'boolean',
        'reviewed_at' => 'datetime',
        'confidence_score' => 'decimal:2',
        'reported_latitude' => 'decimal:8',
        'reported_longitude' => 'decimal:8',
        'actual_latitude' => 'decimal:8',
        'actual_longitude' => 'decimal:8',
        'location_discrepancy_meters' => 'decimal:2',
    ];

    // أنواع التلاعب
    const TAMPER_TYPES = [
        'gps_spoof' => 'تزوير الموقع',
        'photo_spoof' => 'تزوير الصورة',
        'time_manipulation' => 'التلاعب بالوقت',
        'device_clone' => 'استنساخ الجهاز',
        'proxy_vpn' => 'استخدام VPN',
        'multiple_accounts' => 'حسابات متعددة',
        'rooted_device' => 'جهاز مكسور الحماية',
        'emulator' => 'محاكي',
        'automation' => 'أتمتة',
        'other' => 'أخرى',
    ];

    // مستويات الخطورة
    const SEVERITIES = [
        'low' => ['name' => 'منخفضة', 'color' => '#10b981'],
        'medium' => ['name' => 'متوسطة', 'color' => '#f59e0b'],
        'high' => ['name' => 'عالية', 'color' => '#f97316'],
        'critical' => ['name' => 'حرجة', 'color' => '#ef4444'],
    ];

    // الإجراءات
    const ACTIONS = [
        'none' => 'لا إجراء',
        'logged' => 'تم التسجيل',
        'blocked' => 'تم الحظر',
        'alerted' => 'تم التنبيه',
        'suspended' => 'تم الإيقاف',
        'reported' => 'تم الإبلاغ',
    ];

    // العلاقات
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Attributes
    public function getTamperTypeNameAttribute(): string
    {
        return self::TAMPER_TYPES[$this->tamper_type] ?? $this->tamper_type;
    }

    public function getSeverityNameAttribute(): string
    {
        return self::SEVERITIES[$this->severity]['name'] ?? $this->severity;
    }

    public function getSeverityColorAttribute(): string
    {
        return self::SEVERITIES[$this->severity]['color'] ?? '#6b7280';
    }

    public function getActionNameAttribute(): string
    {
        return self::ACTIONS[$this->action_taken] ?? $this->action_taken;
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('review_status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('review_status', 'confirmed');
    }

    public function scopeHighSeverity($query)
    {
        return $query->whereIn('severity', ['high', 'critical']);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    // تسجيل محاولة تلاعب
    public static function logTamperAttempt(
        string $tamperType,
        array $data = []
    ): self {
        $severity = self::determineSeverity($tamperType, $data);

        return self::create([
            'employee_id' => $data['employee_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'branch_id' => $data['branch_id'] ?? null,
            'tamper_type' => $tamperType,
            'severity' => $severity,
            'confidence_score' => $data['confidence_score'] ?? 80,
            'detection_details' => $data['detection_details'] ?? null,
            'description' => $data['description'] ?? null,
            'device_id' => $data['device_id'] ?? null,
            'device_model' => $data['device_model'] ?? null,
            'os_version' => $data['os_version'] ?? null,
            'is_rooted' => $data['is_rooted'] ?? false,
            'is_emulator' => $data['is_emulator'] ?? false,
            'reported_latitude' => $data['reported_latitude'] ?? null,
            'reported_longitude' => $data['reported_longitude'] ?? null,
            'actual_latitude' => $data['actual_latitude'] ?? null,
            'actual_longitude' => $data['actual_longitude'] ?? null,
            'location_discrepancy_meters' => $data['location_discrepancy_meters'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'ip_country' => $data['ip_country'] ?? null,
            'ip_city' => $data['ip_city'] ?? null,
            'is_vpn' => $data['is_vpn'] ?? false,
            'is_proxy' => $data['is_proxy'] ?? false,
            'is_tor' => $data['is_tor'] ?? false,
            'action_taken' => $data['action_taken'] ?? 'logged',
            'review_status' => 'pending',
        ]);
    }

    // تحديد مستوى الخطورة
    protected static function determineSeverity(string $tamperType, array $data): string
    {
        // محاولات التزوير الخطيرة
        $criticalTypes = ['automation', 'deepfake'];
        $highTypes = ['gps_spoof', 'photo_spoof', 'device_clone'];
        $mediumTypes = ['proxy_vpn', 'emulator', 'rooted_device'];

        if (in_array($tamperType, $criticalTypes)) return 'critical';
        if (in_array($tamperType, $highTypes)) return 'high';
        if (in_array($tamperType, $mediumTypes)) return 'medium';

        // تحقق من عوامل إضافية
        if (isset($data['is_repeat_offender']) && $data['is_repeat_offender']) {
            return 'high';
        }

        return 'low';
    }

    // الحصول على إحصائيات التلاعب
    public static function getStats(array $companyUserIds, $startDate = null, $endDate = null): array
    {
        $query = self::where(function ($q) use ($companyUserIds) {
            $q->whereHas('employee', function ($eq) use ($companyUserIds) {
                $eq->whereIn('created_by', $companyUserIds);
            })->orWhereIn('user_id', $companyUserIds);
        });

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $total = $query->count();
        $pending = (clone $query)->pending()->count();
        $confirmed = (clone $query)->confirmed()->count();
        $highSeverity = (clone $query)->highSeverity()->count();

        $byType = (clone $query)
            ->selectRaw('tamper_type, COUNT(*) as count')
            ->groupBy('tamper_type')
            ->pluck('count', 'tamper_type')
            ->toArray();

        return [
            'total' => $total,
            'pending' => $pending,
            'confirmed' => $confirmed,
            'high_severity' => $highSeverity,
            'by_type' => $byType,
        ];
    }
}
