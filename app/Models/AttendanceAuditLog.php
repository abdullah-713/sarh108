<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jenssegers\Agent\Agent;

class AttendanceAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'employee_id',
        'auditable_type',
        'auditable_id',
        'action',
        'action_label',
        'action_label_ar',
        'old_values',
        'new_values',
        'changed_fields',
        'description',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'os',
        'latitude',
        'longitude',
        'location_name',
        'session_id',
        'is_suspicious',
        'suspicious_reason',
        'severity',
        'requires_review',
        'reviewed',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_fields' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_suspicious' => 'boolean',
        'requires_review' => 'boolean',
        'reviewed' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    protected $appends = ['action_name', 'severity_color'];

    // Actions with Arabic labels
    public static array $actions = [
        'create' => 'إنشاء',
        'update' => 'تحديث',
        'delete' => 'حذف',
        'restore' => 'استعادة',
        'login' => 'تسجيل دخول',
        'logout' => 'تسجيل خروج',
        'checkin' => 'تسجيل حضور',
        'checkout' => 'تسجيل انصراف',
        'approve' => 'موافقة',
        'reject' => 'رفض',
        'override' => 'تجاوز',
        'export' => 'تصدير',
        'import' => 'استيراد',
        'bulk_action' => 'إجراء جماعي',
        'settings_change' => 'تغيير الإعدادات',
        'permission_change' => 'تغيير الصلاحيات',
    ];

    // Severity levels
    public static array $severities = [
        'info' => ['name' => 'معلومات', 'color' => 'bg-blue-100 text-blue-800'],
        'low' => ['name' => 'منخفض', 'color' => 'bg-green-100 text-green-800'],
        'medium' => ['name' => 'متوسط', 'color' => 'bg-yellow-100 text-yellow-800'],
        'high' => ['name' => 'عالي', 'color' => 'bg-orange-100 text-orange-800'],
        'critical' => ['name' => 'حرج', 'color' => 'bg-red-100 text-red-800'],
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Accessors
    public function getActionNameAttribute(): string
    {
        return $this->action_label_ar ?: self::$actions[$this->action] ?? $this->action;
    }

    public function getSeverityColorAttribute(): string
    {
        return self::$severities[$this->severity]['color'] ?? 'bg-gray-100 text-gray-800';
    }

    public function getSeverityNameAttribute(): string
    {
        return self::$severities[$this->severity]['name'] ?? $this->severity;
    }

    // Create audit log
    public static function log(
        string $action,
        Model $auditable,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        ?int $employeeId = null
    ): self {
        $user = auth()->user();
        $request = request();
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        // Determine changed fields
        $changedFields = null;
        if ($oldValues && $newValues) {
            $changedFields = array_keys(array_diff_assoc($newValues, $oldValues));
        }

        // Determine severity
        $severity = self::determineSeverity($action, $changedFields);

        // Check if suspicious
        [$isSuspicious, $suspiciousReason] = self::checkIfSuspicious($action, $request);

        return self::create([
            'company_id' => $user?->company_id,
            'user_id' => $user?->id,
            'employee_id' => $employeeId,
            'auditable_type' => get_class($auditable),
            'auditable_id' => $auditable->id,
            'action' => $action,
            'action_label' => self::$actions[$action] ?? $action,
            'action_label_ar' => self::$actions[$action] ?? null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changed_fields' => $changedFields,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $agent->isMobile() ? 'mobile' : ($agent->isTablet() ? 'tablet' : 'desktop'),
            'browser' => $agent->browser(),
            'os' => $agent->platform(),
            'session_id' => session()->getId(),
            'is_suspicious' => $isSuspicious,
            'suspicious_reason' => $suspiciousReason,
            'severity' => $severity,
            'requires_review' => $isSuspicious || $severity === 'critical',
        ]);
    }

    protected static function determineSeverity(string $action, ?array $changedFields): string
    {
        // Critical actions
        if (in_array($action, ['delete', 'permission_change'])) {
            return 'critical';
        }

        // High severity
        if (in_array($action, ['override', 'bulk_action', 'settings_change'])) {
            return 'high';
        }

        // Check if sensitive fields changed
        $sensitiveFields = ['salary', 'password', 'role', 'permissions', 'email'];
        if ($changedFields && array_intersect($sensitiveFields, $changedFields)) {
            return 'high';
        }

        // Medium severity
        if (in_array($action, ['update', 'approve', 'reject'])) {
            return 'medium';
        }

        // Low severity
        if (in_array($action, ['create', 'login', 'logout', 'checkin', 'checkout'])) {
            return 'low';
        }

        return 'info';
    }

    protected static function checkIfSuspicious(string $action, $request): array
    {
        $isSuspicious = false;
        $reason = null;

        // Check for multiple logins from different IPs
        if ($action === 'login') {
            $recentLogins = self::where('user_id', auth()->id())
                ->where('action', 'login')
                ->where('created_at', '>=', now()->subHours(1))
                ->pluck('ip_address')
                ->unique()
                ->count();

            if ($recentLogins > 3) {
                $isSuspicious = true;
                $reason = 'تسجيلات دخول متعددة من عناوين IP مختلفة';
            }
        }

        // Check for unusual time activity
        $hour = now()->hour;
        if ($hour < 5 || $hour > 23) {
            if (in_array($action, ['delete', 'permission_change', 'settings_change'])) {
                $isSuspicious = true;
                $reason = 'نشاط حساس في وقت غير عادي';
            }
        }

        return [$isSuspicious, $reason];
    }

    // Mark as reviewed
    public function markAsReviewed(int $reviewedBy): bool
    {
        return $this->update([
            'reviewed' => true,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
        ]);
    }

    // Scopes
    public function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', true);
    }

    public function scopeRequiresReview($query)
    {
        return $query->where('requires_review', true)->where('reviewed', false);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    // Get daily summary
    public static function getDailySummary(int $companyId, ?string $date = null): array
    {
        $date = $date ?: today()->toDateString();

        $logs = self::forCompany($companyId)
            ->whereDate('created_at', $date)
            ->get();

        return [
            'total_actions' => $logs->count(),
            'by_action' => $logs->groupBy('action')->map->count(),
            'by_severity' => $logs->groupBy('severity')->map->count(),
            'suspicious_count' => $logs->where('is_suspicious', true)->count(),
            'unique_users' => $logs->pluck('user_id')->unique()->count(),
            'unique_devices' => $logs->pluck('device_type')->unique()->count(),
        ];
    }
}
