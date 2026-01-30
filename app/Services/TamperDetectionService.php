<?php

namespace App\Services;

use App\Models\TamperLog;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TamperDetectionService
{
    // الحد الأقصى للمسافة المقبولة (بالأمتار)
    const MAX_LOCATION_DISCREPANCY = 100;

    /**
     * كشف تزوير الموقع GPS
     */
    public function detectGPSSpoofing(
        Request $request,
        ?Employee $employee,
        array $reportedLocation,
        array $expectedLocation
    ): ?TamperLog {
        // حساب الفرق في المسافة
        $discrepancy = $this->calculateDistance(
            $reportedLocation['lat'],
            $reportedLocation['lng'],
            $expectedLocation['lat'],
            $expectedLocation['lng']
        );

        if ($discrepancy <= self::MAX_LOCATION_DISCREPANCY) {
            return null;
        }

        // فحص إضافي للموقع
        $additionalChecks = $this->performLocationChecks($request, $reportedLocation);

        return TamperLog::logTamperAttempt('gps_spoof', [
            'employee_id' => $employee?->id,
            'branch_id' => $employee?->branch_id,
            'confidence_score' => min(95, 50 + ($discrepancy / 100)),
            'description' => "فرق في الموقع: {$discrepancy} متر",
            'detection_details' => array_merge([
                'discrepancy_meters' => $discrepancy,
            ], $additionalChecks),
            'reported_latitude' => $reportedLocation['lat'],
            'reported_longitude' => $reportedLocation['lng'],
            'actual_latitude' => $expectedLocation['lat'],
            'actual_longitude' => $expectedLocation['lng'],
            'location_discrepancy_meters' => $discrepancy,
            'ip_address' => $request->ip(),
            'device_id' => $request->header('X-Device-ID'),
            'is_vpn' => $additionalChecks['is_vpn'] ?? false,
            'is_proxy' => $additionalChecks['is_proxy'] ?? false,
            'action_taken' => $discrepancy > 1000 ? 'blocked' : 'alerted',
        ]);
    }

    /**
     * حساب المسافة بين نقطتين (Haversine formula)
     */
    protected function calculateDistance(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $earthRadius = 6371000; // متر

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
            cos($lat1Rad) * cos($lat2Rad) *
            sin($deltaLng / 2) * sin($deltaLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * فحوصات إضافية للموقع
     */
    protected function performLocationChecks(Request $request, array $location): array
    {
        $ip = $request->ip();
        
        // في الإنتاج: استخدام API لفحص IP
        return [
            'is_vpn' => false,
            'is_proxy' => false,
            'is_tor' => false,
            'ip_country' => 'SA', // محاكاة
            'ip_city' => 'Riyadh', // محاكاة
        ];
    }

    /**
     * كشف الجهاز المكسور الحماية
     */
    public function detectRootedDevice(Request $request, ?Employee $employee): ?TamperLog
    {
        $userAgent = $request->userAgent();
        $deviceInfo = json_decode($request->header('X-Device-Info'), true) ?? [];

        $isRooted = $this->checkRootIndicators($deviceInfo);

        if (!$isRooted) {
            return null;
        }

        return TamperLog::logTamperAttempt('rooted_device', [
            'employee_id' => $employee?->id,
            'branch_id' => $employee?->branch_id,
            'confidence_score' => 85,
            'description' => 'تم الكشف عن جهاز مكسور الحماية',
            'detection_details' => [
                'indicators' => $deviceInfo,
            ],
            'device_model' => $deviceInfo['model'] ?? null,
            'os_version' => $deviceInfo['os_version'] ?? null,
            'is_rooted' => true,
            'ip_address' => $request->ip(),
            'action_taken' => 'alerted',
        ]);
    }

    /**
     * فحص مؤشرات الجذر
     */
    protected function checkRootIndicators(array $deviceInfo): bool
    {
        // في الإنتاج: فحص حقيقي
        $rootIndicators = [
            'su_binary' => $deviceInfo['su_binary'] ?? false,
            'magisk' => $deviceInfo['magisk'] ?? false,
            'superuser_app' => $deviceInfo['superuser_app'] ?? false,
            'test_keys' => $deviceInfo['test_keys'] ?? false,
        ];

        return in_array(true, $rootIndicators);
    }

    /**
     * كشف المحاكي
     */
    public function detectEmulator(Request $request, ?Employee $employee): ?TamperLog
    {
        $userAgent = $request->userAgent();
        
        $emulatorIndicators = [
            'sdk_gphone',
            'emulator',
            'simulator',
            'android sdk',
            'goldfish',
            'generic',
        ];

        $isEmulator = false;
        foreach ($emulatorIndicators as $indicator) {
            if (stripos($userAgent, $indicator) !== false) {
                $isEmulator = true;
                break;
            }
        }

        if (!$isEmulator) {
            return null;
        }

        return TamperLog::logTamperAttempt('emulator', [
            'employee_id' => $employee?->id,
            'branch_id' => $employee?->branch_id,
            'confidence_score' => 90,
            'description' => 'تم الكشف عن محاكي',
            'detection_details' => [
                'user_agent' => $userAgent,
            ],
            'is_emulator' => true,
            'ip_address' => $request->ip(),
            'action_taken' => 'blocked',
        ]);
    }

    /**
     * كشف VPN/Proxy
     */
    public function detectVPN(Request $request, ?Employee $employee): ?TamperLog
    {
        $ip = $request->ip();
        
        // في الإنتاج: استخدام API لفحص IP
        $vpnCheck = $this->checkVPNService($ip);

        if (!$vpnCheck['is_vpn'] && !$vpnCheck['is_proxy'] && !$vpnCheck['is_tor']) {
            return null;
        }

        return TamperLog::logTamperAttempt('proxy_vpn', [
            'employee_id' => $employee?->id,
            'branch_id' => $employee?->branch_id,
            'confidence_score' => 80,
            'description' => 'تم الكشف عن استخدام VPN أو Proxy',
            'detection_details' => $vpnCheck,
            'ip_address' => $ip,
            'is_vpn' => $vpnCheck['is_vpn'],
            'is_proxy' => $vpnCheck['is_proxy'],
            'is_tor' => $vpnCheck['is_tor'],
            'ip_country' => $vpnCheck['country'] ?? null,
            'ip_city' => $vpnCheck['city'] ?? null,
            'action_taken' => 'alerted',
        ]);
    }

    /**
     * فحص خدمة VPN
     */
    protected function checkVPNService(string $ip): array
    {
        // في الإنتاج: استخدام API حقيقي مثل IPQualityScore
        return [
            'is_vpn' => false,
            'is_proxy' => false,
            'is_tor' => false,
            'country' => 'SA',
            'city' => 'Riyadh',
        ];
    }

    /**
     * كشف التلاعب بالوقت
     */
    public function detectTimeManipulation(
        Request $request,
        ?Employee $employee,
        string $clientTime
    ): ?TamperLog {
        $serverTime = Carbon::now();
        $clientTimeCarbon = Carbon::parse($clientTime);
        
        $timeDifference = abs($serverTime->diffInMinutes($clientTimeCarbon));

        // السماح بفرق 5 دقائق
        if ($timeDifference <= 5) {
            return null;
        }

        return TamperLog::logTamperAttempt('time_manipulation', [
            'employee_id' => $employee?->id,
            'branch_id' => $employee?->branch_id,
            'confidence_score' => min(95, 60 + $timeDifference),
            'description' => "فرق في الوقت: {$timeDifference} دقيقة",
            'detection_details' => [
                'client_time' => $clientTime,
                'server_time' => $serverTime->toIso8601String(),
                'difference_minutes' => $timeDifference,
            ],
            'ip_address' => $request->ip(),
            'device_id' => $request->header('X-Device-ID'),
            'action_taken' => $timeDifference > 30 ? 'blocked' : 'alerted',
        ]);
    }

    /**
     * إجراء جميع الفحوصات
     */
    public function runAllChecks(
        Request $request,
        ?Employee $employee,
        array $options = []
    ): array {
        $results = [];

        // فحص الموقع
        if (isset($options['reported_location']) && isset($options['expected_location'])) {
            $gpsCheck = $this->detectGPSSpoofing(
                $request,
                $employee,
                $options['reported_location'],
                $options['expected_location']
            );
            if ($gpsCheck) $results['gps_spoof'] = $gpsCheck;
        }

        // فحص المحاكي
        $emulatorCheck = $this->detectEmulator($request, $employee);
        if ($emulatorCheck) $results['emulator'] = $emulatorCheck;

        // فحص الجهاز المكسور
        $rootCheck = $this->detectRootedDevice($request, $employee);
        if ($rootCheck) $results['rooted'] = $rootCheck;

        // فحص VPN
        $vpnCheck = $this->detectVPN($request, $employee);
        if ($vpnCheck) $results['vpn'] = $vpnCheck;

        // فحص الوقت
        if (isset($options['client_time'])) {
            $timeCheck = $this->detectTimeManipulation($request, $employee, $options['client_time']);
            if ($timeCheck) $results['time'] = $timeCheck;
        }

        return [
            'is_clean' => empty($results),
            'issues' => array_keys($results),
            'logs' => $results,
        ];
    }

    /**
     * الحصول على إحصائيات التلاعب
     */
    public function getStats(array $companyUserIds, $startDate = null, $endDate = null): array
    {
        return TamperLog::getStats($companyUserIds, $startDate, $endDate);
    }

    /**
     * الحصول على المخالفين المتكررين
     */
    public function getRepeatOffenders(array $companyUserIds, int $minOccurrences = 3)
    {
        return TamperLog::where(function ($q) use ($companyUserIds) {
                $q->whereHas('employee', function ($eq) use ($companyUserIds) {
                    $eq->whereIn('created_by', $companyUserIds);
                });
            })
            ->selectRaw('employee_id, COUNT(*) as occurrences')
            ->groupBy('employee_id')
            ->having('occurrences', '>=', $minOccurrences)
            ->with('employee:id,first_name,last_name')
            ->orderBy('occurrences', 'desc')
            ->get();
    }
}
