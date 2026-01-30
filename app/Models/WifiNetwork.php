<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WifiNetwork extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ssid',
        'bssid',
        'branch_id',
        'is_primary',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * التحقق من صحة الشبكة
     */
    public static function verifyNetwork(string $ssid, ?string $bssid = null): ?self
    {
        $query = self::where('ssid', $ssid)->where('is_active', true);
        
        if ($bssid) {
            $query->where(function ($q) use ($bssid) {
                $q->where('bssid', $bssid)->orWhereNull('bssid');
            });
        }
        
        return $query->first();
    }

    /**
     * الحصول على شبكات الفرع
     */
    public static function getByBranch(int $branchId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('branch_id', $branchId)
            ->where('is_active', true)
            ->orderBy('is_primary', 'desc')
            ->get();
    }
}
