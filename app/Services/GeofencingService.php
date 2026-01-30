<?php

namespace App\Services;

class GeofencingService
{
    /**
     * نصف قطر الأرض بالمتر
     */
    private const EARTH_RADIUS = 6371000;

    /**
     * التحقق مما إذا كان الموقع داخل النطاق الجغرافي
     */
    public function isWithinGeofence(
        float $userLat,
        float $userLng,
        ?float $targetLat,
        ?float $targetLng,
        int $radius = 100
    ): bool {
        if (!$targetLat || !$targetLng) {
            return false;
        }

        $distance = $this->calculateDistance($userLat, $userLng, $targetLat, $targetLng);
        
        return $distance <= $radius;
    }

    /**
     * حساب المسافة بين نقطتين باستخدام صيغة Haversine
     */
    public function calculateDistance(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLng / 2) * sin($deltaLng / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS * $c;
    }

    /**
     * الحصول على معلومات المسافة
     */
    public function getDistanceInfo(
        float $userLat,
        float $userLng,
        float $targetLat,
        float $targetLng,
        int $radius = 100
    ): array {
        $distance = $this->calculateDistance($userLat, $userLng, $targetLat, $targetLng);
        $isWithin = $distance <= $radius;

        return [
            'distance' => round($distance, 2),
            'distance_formatted' => $this->formatDistance($distance),
            'radius' => $radius,
            'is_within' => $isWithin,
            'exceeds_by' => $isWithin ? 0 : round($distance - $radius, 2),
        ];
    }

    /**
     * تنسيق المسافة للعرض
     */
    private function formatDistance(float $meters): string
    {
        if ($meters < 1000) {
            return round($meters) . ' متر';
        }
        
        return round($meters / 1000, 2) . ' كم';
    }

    /**
     * التحقق من صحة الإحداثيات
     */
    public function validateCoordinates(?float $lat, ?float $lng): bool
    {
        if ($lat === null || $lng === null) {
            return false;
        }

        // خطوط العرض بين -90 و 90
        if ($lat < -90 || $lat > 90) {
            return false;
        }

        // خطوط الطول بين -180 و 180
        if ($lng < -180 || $lng > 180) {
            return false;
        }

        return true;
    }

    /**
     * الحصول على الاتجاه بين نقطتين
     */
    public function getBearing(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLng = deg2rad($lng2 - $lng1);

        $x = sin($deltaLng) * cos($lat2Rad);
        $y = cos($lat1Rad) * sin($lat2Rad) - 
             sin($lat1Rad) * cos($lat2Rad) * cos($deltaLng);

        $bearing = rad2deg(atan2($x, $y));
        
        return fmod($bearing + 360, 360);
    }

    /**
     * الحصول على اسم الاتجاه
     */
    public function getDirectionName(float $bearing): string
    {
        $directions = [
            'شمال', 'شمال شرق', 'شرق', 'جنوب شرق',
            'جنوب', 'جنوب غرب', 'غرب', 'شمال غرب'
        ];
        
        $index = round($bearing / 45) % 8;
        
        return $directions[$index];
    }
}
