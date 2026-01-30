<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LockdownAttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'lockdown_event_id',
        'employee_id',
        'action_type',
        'attempted_at',
        'latitude',
        'longitude',
        'device_id',
        'was_allowed',
        'override_reason',
        'overridden_by',
    ];

    protected $casts = [
        'attempted_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'was_allowed' => 'boolean',
    ];

    protected $appends = ['action_type_name'];

    // Action types
    public static array $actionTypes = [
        'blocked_checkin' => 'تم منع التسجيل',
        'blocked_checkout' => 'تم منع الخروج',
        'emergency_checkin' => 'تسجيل طوارئ',
        'emergency_checkout' => 'خروج طوارئ',
        'exempt_access' => 'وصول معفى',
    ];

    // Relationships
    public function lockdownEvent(): BelongsTo
    {
        return $this->belongsTo(LockdownEvent::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function overriddenByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'overridden_by');
    }

    // Accessors
    public function getActionTypeNameAttribute(): string
    {
        return self::$actionTypes[$this->action_type] ?? $this->action_type;
    }
}
