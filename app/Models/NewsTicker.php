<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class NewsTicker extends Model
{
    use HasFactory;

    protected $table = 'news_ticker';

    protected $fillable = [
        'title',
        'content',
        'type',
        'priority',
        'icon',
        'color',
        'background_color',
        'branch_id',
        'target_departments',
        'is_global',
        'starts_at',
        'ends_at',
        'is_active',
        'views_count',
        'clicks_count',
        'action_url',
        'action_text',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'target_departments' => 'array',
        'is_global' => 'boolean',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
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
     * الحصول على الأخبار النشطة
     */
    public static function getActive($branchId = null, $departmentId = null)
    {
        $now = Carbon::now();

        $query = self::where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $now);
            })
            ->orderBy('priority', 'desc')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc');

        // فلترة حسب الفرع
        if ($branchId) {
            $query->where(function ($q) use ($branchId) {
                $q->where('is_global', true)
                    ->orWhere('branch_id', $branchId);
            });
        } else {
            $query->where('is_global', true);
        }

        return $query->limit(10)->get();
    }

    /**
     * الحصول على لون النوع
     */
    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'announcement' => '#3b82f6',
            'achievement' => '#10b981',
            'reminder' => '#f59e0b',
            'warning' => '#ef4444',
            'celebration' => '#8b5cf6',
            'mvp' => '#ff8531',
            'badge' => '#06b6d4',
            'streak' => '#ec4899',
            'custom' => '#6b7280',
            default => '#6b7280',
        };
    }

    /**
     * الحصول على أيقونة النوع
     */
    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'announcement' => 'megaphone',
            'achievement' => 'trophy',
            'reminder' => 'bell',
            'warning' => 'alert-triangle',
            'celebration' => 'party-popper',
            'mvp' => 'star',
            'badge' => 'award',
            'streak' => 'flame',
            'custom' => 'info',
            default => 'info',
        };
    }

    /**
     * الحصول على اسم النوع بالعربية
     */
    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'announcement' => 'إعلان',
            'achievement' => 'إنجاز',
            'reminder' => 'تذكير',
            'warning' => 'تحذير',
            'celebration' => 'احتفال',
            'mvp' => 'موظف مثالي',
            'badge' => 'شارة جديدة',
            'streak' => 'رقم قياسي',
            'custom' => 'مخصص',
            default => 'غير محدد',
        };
    }

    /**
     * زيادة عداد المشاهدات
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * زيادة عداد النقرات
     */
    public function incrementClicks(): void
    {
        $this->increment('clicks_count');
    }

    /**
     * إنشاء خبر MVP
     */
    public static function createMVPNews($employeeName, $period, $createdBy = null): self
    {
        return self::create([
            'title' => "🏆 تهانينا! {$employeeName} موظف الفترة المثالي لـ{$period}",
            'type' => 'mvp',
            'priority' => 'high',
            'is_global' => true,
            'created_by' => $createdBy,
        ]);
    }

    /**
     * إنشاء خبر شارة
     */
    public static function createBadgeNews($employeeName, $badgeName, $createdBy = null): self
    {
        return self::create([
            'title' => "🎖️ {$employeeName} حصل على شارة {$badgeName}",
            'type' => 'badge',
            'priority' => 'normal',
            'is_global' => true,
            'created_by' => $createdBy,
        ]);
    }

    /**
     * إنشاء خبر رقم قياسي
     */
    public static function createStreakNews($employeeName, $streakDays, $createdBy = null): self
    {
        return self::create([
            'title' => "🔥 {$employeeName} حقق {$streakDays} يوم متتالي من الحضور المثالي!",
            'type' => 'streak',
            'priority' => 'high',
            'is_global' => true,
            'created_by' => $createdBy,
        ]);
    }
}
