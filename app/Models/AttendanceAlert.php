<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceAlert extends BaseModel
{
    use HasFactory;

    protected $table = 'attendance_alerts';

    protected $fillable = [
        'employee_id',
        'manager_id',
        'alert_type',
        'message',
        'alert_time',
        'is_read',
        'read_at',
        'severity',
        'action_required',
        'resolution_notes',
        'is_resolved',
        'resolved_at',
        'metadata',
    ];

    protected $casts = [
        'alert_time' => 'datetime',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the employee associated with this alert.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the manager who received this alert.
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Mark alert as read.
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark alert as resolved.
     */
    public function markAsResolved($notes = null)
    {
        $this->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }

    /**
     * Scope to get unread alerts.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to get unresolved alerts.
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    /**
     * Scope to get alerts by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('alert_type', $type);
    }

    /**
     * Scope to get critical alerts.
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    /**
     * Scope to get alerts for a specific manager.
     */
    public function scopeForManager($query, $managerId)
    {
        return $query->where('manager_id', $managerId);
    }
}
