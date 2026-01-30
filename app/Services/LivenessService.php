<?php

namespace App\Services;

use App\Models\LivenessCheck;
use App\Models\TamperLog;
use App\Models\Employee;
use Illuminate\Http\Request;

class LivenessService
{
    // الحد الأدنى لنسبة التشابه
    const MIN_SIMILARITY_THRESHOLD = 70;
    
    // الحد الأدنى لثقة الكشف
    const MIN_CONFIDENCE_THRESHOLD = 60;

    /**
     * إجراء فحص الحيوية
     */
    public function performCheck(
        Employee $employee,
        string $checkType,
        array $imageData,
        Request $request
    ): LivenessCheck {
        $deviceInfo = $this->extractDeviceInfo($request);
        
        // محاكاة التحقق من الوجه (في الإنتاج، سيتم استخدام AI API)
        $verificationResult = $this->verifyFace($employee, $imageData);
        
        // فحص محاولة التزوير
        $spoofingCheck = $this->detectSpoofing($imageData);
        
        $passed = $verificationResult['passed'] && !$spoofingCheck['is_spoofing'];
        
        $check = LivenessCheck::createCheck(
            $employee->id,
            $checkType,
            $passed,
            array_merge($verificationResult, $spoofingCheck, $deviceInfo, [
                'attempt_number' => $this->getAttemptNumber($employee->id),
            ])
        );

        // إذا كانت محاولة تزوير، سجلها
        if ($spoofingCheck['is_spoofing']) {
            $this->logTamperAttempt($employee, $spoofingCheck, $deviceInfo);
        }

        return $check;
    }

    /**
     * التحقق من الوجه
     */
    protected function verifyFace(Employee $employee, array $imageData): array
    {
        // في الإنتاج: استدعاء API للتعرف على الوجه
        // هنا محاكاة النتيجة
        
        $confidence = rand(60, 99) / 100 * 100; // 60-99%
        $similarity = rand(65, 98) / 100 * 100; // 65-98%
        
        $passed = $confidence >= self::MIN_CONFIDENCE_THRESHOLD 
            && $similarity >= self::MIN_SIMILARITY_THRESHOLD;

        return [
            'confidence_score' => $confidence,
            'similarity_score' => $similarity,
            'passed' => $passed,
            'face_landmarks' => [
                'left_eye' => ['x' => rand(100, 200), 'y' => rand(100, 150)],
                'right_eye' => ['x' => rand(250, 350), 'y' => rand(100, 150)],
                'nose' => ['x' => rand(175, 225), 'y' => rand(175, 225)],
                'mouth' => ['x' => rand(175, 225), 'y' => rand(275, 325)],
            ],
            'detection_data' => [
                'face_detected' => true,
                'face_count' => 1,
                'face_quality' => rand(70, 95),
                'lighting_score' => rand(60, 90),
            ],
            'processing_time_ms' => rand(100, 500),
        ];
    }

    /**
     * كشف محاولات التزوير
     */
    protected function detectSpoofing(array $imageData): array
    {
        // في الإنتاج: استدعاء AI API لكشف التزوير
        // هنا محاكاة - نادراً ما نكتشف تزوير
        
        $isSpoofing = rand(1, 100) <= 3; // 3% احتمال التزوير للمحاكاة
        
        if (!$isSpoofing) {
            return [
                'is_spoofing_attempt' => false,
                'spoofing_type' => null,
                'spoofing_confidence' => null,
            ];
        }

        $spoofingTypes = ['photo', 'screen', 'mask', 'video'];
        $spoofingType = $spoofingTypes[array_rand($spoofingTypes)];

        return [
            'is_spoofing_attempt' => true,
            'spoofing_type' => $spoofingType,
            'spoofing_confidence' => rand(70, 95),
        ];
    }

    /**
     * استخراج معلومات الجهاز
     */
    protected function extractDeviceInfo(Request $request): array
    {
        $userAgent = $request->userAgent();
        
        return [
            'device_type' => $this->detectDeviceType($userAgent),
            'device_id' => $request->header('X-Device-ID'),
            'browser' => $this->detectBrowser($userAgent),
            'ip_address' => $request->ip(),
            'device_fingerprint' => [
                'user_agent' => $userAgent,
                'screen_resolution' => $request->header('X-Screen-Resolution'),
                'timezone' => $request->header('X-Timezone'),
                'language' => $request->header('Accept-Language'),
            ],
        ];
    }

    /**
     * كشف نوع الجهاز
     */
    protected function detectDeviceType(string $userAgent): string
    {
        if (preg_match('/mobile|android|iphone|ipad/i', $userAgent)) {
            return 'mobile';
        }
        if (preg_match('/tablet|ipad/i', $userAgent)) {
            return 'tablet';
        }
        return 'desktop';
    }

    /**
     * كشف المتصفح
     */
    protected function detectBrowser(string $userAgent): string
    {
        if (preg_match('/chrome/i', $userAgent)) return 'Chrome';
        if (preg_match('/firefox/i', $userAgent)) return 'Firefox';
        if (preg_match('/safari/i', $userAgent)) return 'Safari';
        if (preg_match('/edge/i', $userAgent)) return 'Edge';
        return 'Unknown';
    }

    /**
     * الحصول على رقم المحاولة
     */
    protected function getAttemptNumber(int $employeeId): int
    {
        return LivenessCheck::where('employee_id', $employeeId)
            ->whereDate('created_at', today())
            ->count() + 1;
    }

    /**
     * تسجيل محاولة تلاعب
     */
    protected function logTamperAttempt(Employee $employee, array $spoofingCheck, array $deviceInfo): TamperLog
    {
        return TamperLog::logTamperAttempt('photo_spoof', [
            'employee_id' => $employee->id,
            'branch_id' => $employee->branch_id,
            'confidence_score' => $spoofingCheck['spoofing_confidence'],
            'description' => 'محاولة تزوير مكتشفة أثناء فحص الحيوية',
            'detection_details' => $spoofingCheck,
            'device_id' => $deviceInfo['device_id'],
            'ip_address' => $deviceInfo['ip_address'],
            'action_taken' => 'blocked',
        ]);
    }

    /**
     * التحقق من صلاحية الفحص
     */
    public function isCheckValid(LivenessCheck $check): bool
    {
        // يجب أن يكون الفحص خلال آخر 5 دقائق
        if ($check->created_at->diffInMinutes(now()) > 5) {
            return false;
        }

        return $check->passed && !$check->is_spoofing_attempt;
    }

    /**
     * الحصول على إحصائيات الفحوصات
     */
    public function getStats(array $companyUserIds, $startDate = null, $endDate = null): array
    {
        return LivenessCheck::getStats($companyUserIds, $startDate, $endDate);
    }

    /**
     * فحوصات الموظف الأخيرة
     */
    public function getEmployeeRecentChecks(int $employeeId, int $limit = 10)
    {
        return LivenessCheck::forEmployee($employeeId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * التحقق من الجهاز
     */
    public function verifyDevice(Request $request): array
    {
        $isRooted = $this->detectRootedDevice($request);
        $isEmulator = $this->detectEmulator($request);
        $isVPN = $this->detectVPN($request);

        $issues = [];
        if ($isRooted) $issues[] = 'rooted_device';
        if ($isEmulator) $issues[] = 'emulator';
        if ($isVPN) $issues[] = 'vpn';

        return [
            'is_valid' => empty($issues),
            'is_rooted' => $isRooted,
            'is_emulator' => $isEmulator,
            'is_vpn' => $isVPN,
            'issues' => $issues,
        ];
    }

    protected function detectRootedDevice(Request $request): bool
    {
        // في الإنتاج: فحص حقيقي للجهاز
        return false;
    }

    protected function detectEmulator(Request $request): bool
    {
        $userAgent = $request->userAgent();
        return preg_match('/emulator|simulator|sdk_gphone/i', $userAgent) === 1;
    }

    protected function detectVPN(Request $request): bool
    {
        // في الإنتاج: استخدام خدمة كشف VPN
        return false;
    }
}
