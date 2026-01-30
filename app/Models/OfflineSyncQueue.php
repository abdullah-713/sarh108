<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfflineSyncQueue extends Model
{
    use HasFactory;

    protected $table = 'offline_sync_queue';

    protected $fillable = [
        'company_id',
        'user_id',
        'employee_id',
        'device_id',
        'action_type',
        'payload',
        'client_timestamp',
        'sync_status',
        'synced_at',
        'conflict_resolution',
        'sync_attempts',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
        'client_timestamp' => 'datetime',
        'synced_at' => 'datetime',
    ];

    protected $appends = ['action_type_name', 'sync_status_name'];

    // Action types
    public static array $actionTypes = [
        'checkin' => 'تسجيل حضور',
        'checkout' => 'تسجيل انصراف',
        'location_update' => 'تحديث الموقع',
        'form_submit' => 'إرسال نموذج',
        'data_sync' => 'مزامنة بيانات',
    ];

    // Sync statuses
    public static array $syncStatuses = [
        'pending' => 'بانتظار المزامنة',
        'processing' => 'جاري المعالجة',
        'synced' => 'تمت المزامنة',
        'failed' => 'فشل',
        'conflict' => 'تعارض',
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
    public function getActionTypeNameAttribute(): string
    {
        return self::$actionTypes[$this->action_type] ?? $this->action_type;
    }

    public function getSyncStatusNameAttribute(): string
    {
        return self::$syncStatuses[$this->sync_status] ?? $this->sync_status;
    }

    // Mark as synced
    public function markAsSynced(): bool
    {
        return $this->update([
            'sync_status' => 'synced',
            'synced_at' => now(),
        ]);
    }

    // Mark as failed
    public function markAsFailed(string $error): bool
    {
        return $this->update([
            'sync_status' => 'failed',
            'error_message' => $error,
            'sync_attempts' => $this->sync_attempts + 1,
        ]);
    }

    // Mark as conflict
    public function markAsConflict(string $resolution): bool
    {
        return $this->update([
            'sync_status' => 'conflict',
            'conflict_resolution' => $resolution,
        ]);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('sync_status', 'pending');
    }

    public function scopeForDevice($query, string $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }

    // Queue offline action
    public static function queue(
        int $userId,
        string $deviceId,
        string $actionType,
        array $payload,
        \DateTime $clientTimestamp
    ): self {
        $user = User::find($userId);
        
        return self::create([
            'company_id' => $user->company_id,
            'user_id' => $userId,
            'employee_id' => $user->employee?->id,
            'device_id' => $deviceId,
            'action_type' => $actionType,
            'payload' => $payload,
            'client_timestamp' => $clientTimestamp,
        ]);
    }

    // Process pending items
    public static function processPending(int $limit = 100): int
    {
        $processed = 0;
        $items = self::pending()
            ->orderBy('client_timestamp')
            ->limit($limit)
            ->get();

        foreach ($items as $item) {
            $item->update(['sync_status' => 'processing']);

            try {
                $result = $item->processAction();
                if ($result) {
                    $item->markAsSynced();
                    $processed++;
                }
            } catch (\Exception $e) {
                $item->markAsFailed($e->getMessage());
            }
        }

        return $processed;
    }

    // Process the action
    public function processAction(): bool
    {
        switch ($this->action_type) {
            case 'checkin':
            case 'checkout':
                return $this->processAttendance();
            case 'location_update':
                return $this->processLocationUpdate();
            default:
                return true;
        }
    }

    protected function processAttendance(): bool
    {
        // Implementation would create the attendance record from payload
        // This is a simplified version
        return true;
    }

    protected function processLocationUpdate(): bool
    {
        // Implementation would update location tracking
        return true;
    }
}
