<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'slug',
        'description',
        'description_ar',
        'icon',
        'color',
        'background_color',
        'tier',
        'type',
        'required_days',
        'required_streak',
        'required_rate',
        'points',
        'is_active',
        'is_auto_award',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'required_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'is_auto_award' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_badges')
            ->withPivot(['awarded_date', 'period', 'reason', 'awarded_by', 'is_displayed'])
            ->withTimestamps();
    }

    /**
     * الحصول على الشارات النشطة
     */
    public static function getActive()
    {
        return self::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * الحصول على لون المستوى
     */
    public function getTierColorAttribute(): string
    {
        return match($this->tier) {
            'diamond' => '#b9f2ff',
            'platinum' => '#e5e4e2',
            'gold' => '#ffd700',
            'silver' => '#c0c0c0',
            'bronze' => '#cd7f32',
            default => '#gray',
        };
    }

    /**
     * الحصول على اسم المستوى بالعربية
     */
    public function getTierNameAttribute(): string
    {
        return match($this->tier) {
            'diamond' => 'ماسي',
            'platinum' => 'بلاتيني',
            'gold' => 'ذهبي',
            'silver' => 'فضي',
            'bronze' => 'برونزي',
            default => 'عادي',
        };
    }

    /**
     * الحصول على اسم النوع بالعربية
     */
    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'punctuality' => 'الالتزام بالوقت',
            'attendance' => 'الحضور المنتظم',
            'early_bird' => 'الوصول المبكر',
            'streak' => 'الاستمرارية',
            'perfect_month' => 'شهر مثالي',
            'mvp' => 'موظف مثالي',
            'team_player' => 'لاعب فريق',
            'custom' => 'مخصص',
            default => 'غير محدد',
        };
    }

    /**
     * إنشاء الشارات الافتراضية
     */
    public static function createDefaults($createdBy = null): void
    {
        $badges = [
            [
                'name' => 'Punctuality Star',
                'name_ar' => 'نجم الالتزام',
                'slug' => 'punctuality-star',
                'description' => 'Awarded for 20 days of on-time attendance',
                'description_ar' => 'تمنح لـ20 يوم حضور في الوقت',
                'icon' => 'clock',
                'color' => '#10b981',
                'tier' => 'gold',
                'type' => 'punctuality',
                'required_days' => 20,
                'points' => 100,
            ],
            [
                'name' => 'Early Bird',
                'name_ar' => 'الطائر المبكر',
                'slug' => 'early-bird',
                'description' => 'Awarded for 10 days of early arrival',
                'description_ar' => 'تمنح لـ10 أيام وصول مبكر',
                'icon' => 'sunrise',
                'color' => '#f59e0b',
                'tier' => 'silver',
                'type' => 'early_bird',
                'required_days' => 10,
                'points' => 50,
            ],
            [
                'name' => 'Streak Master',
                'name_ar' => 'سيد الاستمرارية',
                'slug' => 'streak-master',
                'description' => 'Awarded for 30 consecutive days streak',
                'description_ar' => 'تمنح لـ30 يوم متتالي',
                'icon' => 'flame',
                'color' => '#ef4444',
                'tier' => 'platinum',
                'type' => 'streak',
                'required_streak' => 30,
                'points' => 200,
            ],
            [
                'name' => 'Perfect Month',
                'name_ar' => 'الشهر المثالي',
                'slug' => 'perfect-month',
                'description' => 'Awarded for a month with 100% attendance',
                'description_ar' => 'تمنح لشهر بدون غياب أو تأخير',
                'icon' => 'trophy',
                'color' => '#8b5cf6',
                'tier' => 'diamond',
                'type' => 'perfect_month',
                'required_rate' => 100,
                'points' => 500,
            ],
            [
                'name' => 'MVP',
                'name_ar' => 'الموظف المثالي',
                'slug' => 'mvp',
                'description' => 'Monthly top performer',
                'description_ar' => 'أفضل موظف في الشهر',
                'icon' => 'star',
                'color' => '#ff8531',
                'tier' => 'diamond',
                'type' => 'mvp',
                'points' => 1000,
                'is_auto_award' => false,
            ],
        ];

        foreach ($badges as $badge) {
            self::firstOrCreate(
                ['slug' => $badge['slug']],
                array_merge($badge, ['created_by' => $createdBy])
            );
        }
    }
}
