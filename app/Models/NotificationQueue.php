<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationQueue extends Model
{
    use HasFactory;

    protected $table = 'notification_queue';

    protected $fillable = [
        'company_id',
        'user_id',
        'employee_id',
        'channel',
        'title',
        'title_ar',
        'body',
        'body_ar',
        'icon',
        'action_url',
        'data',
        'priority',
        'status',
        'scheduled_at',
        'sent_at',
        'retry_count',
        'error_message',
    ];

    protected $casts = [
        'data' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    protected $appends = ['priority_name', 'status_name'];

    // Priorities
    public static array $priorities = [
        'low' => 'منخفضة',
        'normal' => 'عادية',
        'high' => 'عالية',
        'urgent' => 'عاجلة',
    ];

    // Statuses
    public static array $statuses = [
        'pending' => 'بانتظار الإرسال',
        'sending' => 'جاري الإرسال',
        'sent' => 'تم الإرسال',
        'failed' => 'فشل',
        'cancelled' => 'ملغي',
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

    // Accessors
    public function getPriorityNameAttribute(): string
    {
        return self::$priorities[$this->priority] ?? $this->priority;
    }

    public function getStatusNameAttribute(): string
    {
        return self::$statuses[$this->status] ?? $this->status;
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->title_ar ?: $this->title;
    }

    public function getDisplayBodyAttribute(): string
    {
        return $this->body_ar ?: $this->body;
    }

    // Mark as sent
    public function markAsSent(): bool
    {
        return $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    // Mark as failed
    public function markAsFailed(string $error): bool
    {
        return $this->update([
            'status' => 'failed',
            'error_message' => $error,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '<=', now());
            });
    }

    // Create notification
    public static function queue(
        int $companyId,
        ?int $userId,
        string $title,
        string $body,
        string $channel = 'push',
        array $options = []
    ): self {
        return self::create([
            'company_id' => $companyId,
            'user_id' => $userId,
            'employee_id' => $options['employee_id'] ?? null,
            'channel' => $channel,
            'title' => $title,
            'title_ar' => $options['title_ar'] ?? null,
            'body' => $body,
            'body_ar' => $options['body_ar'] ?? null,
            'icon' => $options['icon'] ?? null,
            'action_url' => $options['action_url'] ?? null,
            'data' => $options['data'] ?? null,
            'priority' => $options['priority'] ?? 'normal',
            'scheduled_at' => $options['scheduled_at'] ?? null,
        ]);
    }
}
