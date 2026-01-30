<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PwaConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'pwa_enabled',
        'app_name',
        'app_short_name',
        'app_description',
        'theme_color',
        'background_color',
        'display_mode',
        'orientation',
        'start_url',
        'scope',
        'icons',
        'screenshots',
        'shortcuts',
        'enable_push_notifications',
        'enable_offline_mode',
        'offline_pages',
        'cache_strategy',
    ];

    protected $casts = [
        'pwa_enabled' => 'boolean',
        'enable_push_notifications' => 'boolean',
        'enable_offline_mode' => 'boolean',
        'icons' => 'array',
        'screenshots' => 'array',
        'shortcuts' => 'array',
        'offline_pages' => 'array',
        'cache_strategy' => 'array',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // Generate manifest.json
    public function generateManifest(): array
    {
        return [
            'name' => $this->app_name,
            'short_name' => $this->app_short_name,
            'description' => $this->app_description,
            'theme_color' => $this->theme_color,
            'background_color' => $this->background_color,
            'display' => $this->display_mode,
            'orientation' => $this->orientation,
            'start_url' => $this->start_url,
            'scope' => $this->scope,
            'icons' => $this->icons ?: $this->getDefaultIcons(),
            'screenshots' => $this->screenshots,
            'shortcuts' => $this->shortcuts ?: $this->getDefaultShortcuts(),
            'dir' => 'rtl',
            'lang' => 'ar',
        ];
    }

    protected function getDefaultIcons(): array
    {
        return [
            ['src' => '/images/icons/icon-72x72.png', 'sizes' => '72x72', 'type' => 'image/png'],
            ['src' => '/images/icons/icon-96x96.png', 'sizes' => '96x96', 'type' => 'image/png'],
            ['src' => '/images/icons/icon-128x128.png', 'sizes' => '128x128', 'type' => 'image/png'],
            ['src' => '/images/icons/icon-144x144.png', 'sizes' => '144x144', 'type' => 'image/png'],
            ['src' => '/images/icons/icon-152x152.png', 'sizes' => '152x152', 'type' => 'image/png'],
            ['src' => '/images/icons/icon-192x192.png', 'sizes' => '192x192', 'type' => 'image/png'],
            ['src' => '/images/icons/icon-384x384.png', 'sizes' => '384x384', 'type' => 'image/png'],
            ['src' => '/images/icons/icon-512x512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
        ];
    }

    protected function getDefaultShortcuts(): array
    {
        return [
            [
                'name' => 'تسجيل حضور',
                'short_name' => 'حضور',
                'description' => 'تسجيل الحضور بسرعة',
                'url' => '/attendance/quick-checkin',
                'icons' => [['src' => '/images/icons/checkin.png', 'sizes' => '96x96']],
            ],
            [
                'name' => 'لوحة القيادة',
                'short_name' => 'الرئيسية',
                'description' => 'الذهاب للوحة القيادة',
                'url' => '/dashboard',
                'icons' => [['src' => '/images/icons/dashboard.png', 'sizes' => '96x96']],
            ],
        ];
    }

    // Get or create default configuration
    public static function getForCompany(int $companyId): self
    {
        return self::firstOrCreate(
            ['company_id' => $companyId],
            [
                'pwa_enabled' => true,
                'app_name' => 'صرح الإتقان',
                'app_short_name' => 'صرح',
                'theme_color' => '#ff8531',
                'background_color' => '#ffffff',
            ]
        );
    }
}
