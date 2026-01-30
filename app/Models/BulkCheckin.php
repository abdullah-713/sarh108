<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkCheckin extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'supervisor_id',
        'type',
        'employee_count',
        'employee_ids',
        'checked_at',
        'notes',
    ];

    protected $casts = [
        'employee_ids' => 'array',
        'checked_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * إنشاء تحضير جماعي
     */
    public static function createBulkCheckin(
        int $branchId,
        int $supervisorId,
        array $employeeIds,
        string $type = 'checkin',
        ?string $notes = null
    ): self {
        return self::create([
            'branch_id' => $branchId,
            'supervisor_id' => $supervisorId,
            'type' => $type,
            'employee_count' => count($employeeIds),
            'employee_ids' => $employeeIds,
            'checked_at' => now(),
            'notes' => $notes,
        ]);
    }

    /**
     * الحصول على سجلات اليوم
     */
    public static function getTodayByBranch(int $branchId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('branch_id', $branchId)
            ->whereDate('checked_at', today())
            ->with('supervisor')
            ->orderBy('checked_at', 'desc')
            ->get();
    }
}
