<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LivenessCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'attendance_id',
        'check_type',
        'passed',
        'confidence_score',
        'similarity_score',
        'image_path',
        'reference_image_path',
        'face_landmarks',
        'detection_data',
        'is_spoofing_attempt',
        'spoofing_type',
        'spoofing_confidence',
        'device_type',
        'device_id',
        'browser',
        'ip_address',
        'device_fingerprint',
        'processing_time_ms',
        'attempt_number',
    ];

    protected $casts = [
        'passed' => 'boolean',
        'is_spoofing_attempt' => 'boolean',
        'face_landmarks' => 'array',
        'detection_data' => 'array',
        'device_fingerprint' => 'array',
        'confidence_score' => 'decimal:2',
        'similarity_score' => 'decimal:2',
        'spoofing_confidence' => 'decimal:2',
    ];

    // أنواع الفحص
    const CHECK_TYPES = [
        'face' => 'التعرف على الوجه',
        'blink' => 'رمش العين',
        'smile' => 'ابتسامة',
        'turn_head' => 'تدوير الرأس',
        'voice' => 'صوت',
        'gesture' => 'إيماءة',
        'random' => 'عشوائي',
    ];

    // أنواع التزوير
    const SPOOFING_TYPES = [
        'none' => 'لا يوجد',
        'photo' => 'صورة مطبوعة',
        'screen' => 'شاشة',
        'mask' => 'قناع',
        'video' => 'فيديو',
        'deepfake' => 'تزييف عميق',
        'other' => 'أخرى',
    ];

    // العلاقات
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    // Attributes
    public function getCheckTypeNameAttribute(): string
    {
        return self::CHECK_TYPES[$this->check_type] ?? $this->check_type;
    }

    public function getSpoofingTypeNameAttribute(): string
    {
        return self::SPOOFING_TYPES[$this->spoofing_type] ?? $this->spoofing_type;
    }

    public function getStatusTextAttribute(): string
    {
        if ($this->is_spoofing_attempt) {
            return 'محاولة تزوير';
        }
        return $this->passed ? 'ناجح' : 'فاشل';
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->is_spoofing_attempt) {
            return '#ef4444';
        }
        return $this->passed ? '#10b981' : '#f59e0b';
    }

    // Scopes
    public function scopePassed($query)
    {
        return $query->where('passed', true);
    }

    public function scopeFailed($query)
    {
        return $query->where('passed', false);
    }

    public function scopeSpoofingAttempts($query)
    {
        return $query->where('is_spoofing_attempt', true);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    // إنشاء فحص جديد
    public static function createCheck(
        int $employeeId,
        string $checkType,
        bool $passed,
        array $data = []
    ): self {
        return self::create([
            'employee_id' => $employeeId,
            'attendance_id' => $data['attendance_id'] ?? null,
            'check_type' => $checkType,
            'passed' => $passed,
            'confidence_score' => $data['confidence_score'] ?? 0,
            'similarity_score' => $data['similarity_score'] ?? null,
            'image_path' => $data['image_path'] ?? null,
            'reference_image_path' => $data['reference_image_path'] ?? null,
            'face_landmarks' => $data['face_landmarks'] ?? null,
            'detection_data' => $data['detection_data'] ?? null,
            'is_spoofing_attempt' => $data['is_spoofing_attempt'] ?? false,
            'spoofing_type' => $data['spoofing_type'] ?? null,
            'spoofing_confidence' => $data['spoofing_confidence'] ?? null,
            'device_type' => $data['device_type'] ?? null,
            'device_id' => $data['device_id'] ?? null,
            'browser' => $data['browser'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'device_fingerprint' => $data['device_fingerprint'] ?? null,
            'processing_time_ms' => $data['processing_time_ms'] ?? null,
            'attempt_number' => $data['attempt_number'] ?? 1,
        ]);
    }

    // الحصول على إحصائيات الفحوصات
    public static function getStats(array $companyUserIds, $startDate = null, $endDate = null): array
    {
        $query = self::whereHas('employee', function ($q) use ($companyUserIds) {
            $q->whereIn('created_by', $companyUserIds);
        });

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $total = $query->count();
        $passed = (clone $query)->where('passed', true)->count();
        $failed = (clone $query)->where('passed', false)->count();
        $spoofing = (clone $query)->where('is_spoofing_attempt', true)->count();

        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'spoofing_attempts' => $spoofing,
            'pass_rate' => $total > 0 ? round(($passed / $total) * 100, 2) : 0,
            'spoofing_rate' => $total > 0 ? round(($spoofing / $total) * 100, 2) : 0,
        ];
    }
}
