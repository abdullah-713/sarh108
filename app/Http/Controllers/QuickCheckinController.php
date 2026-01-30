<?php

namespace App\Http\Controllers;

use App\Models\QuickCheckin;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\WifiNetwork;
use App\Models\TimeWindow;
use App\Models\DeductionTier;
use App\Models\EmployeeStatusLog;
use App\Services\GeofencingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuickCheckinController extends Controller
{
    protected $geofencingService;

    public function __construct(GeofencingService $geofencingService)
    {
        $this->geofencingService = $geofencingService;
    }

    /**
     * صفحة الحضور السريع
     */
    public function index()
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'لا يوجد ملف موظف مرتبط بحسابك');
        }

        $branch = $employee->branch;
        $todayCheckin = QuickCheckin::where('employee_id', $employee->id)
            ->where('type', 'checkin')
            ->whereDate('checked_at', today())
            ->first();

        $todayCheckout = QuickCheckin::where('employee_id', $employee->id)
            ->where('type', 'checkout')
            ->whereDate('checked_at', today())
            ->first();

        $timeWindow = TimeWindow::getActiveWindow($branch?->id, 'checkin');
        $wifiNetworks = WifiNetwork::getByBranch($branch?->id ?? 0);

        return Inertia::render('attendance/quick-checkin', [
            'employee' => $employee->load('branch', 'department', 'designation'),
            'branch' => $branch,
            'todayCheckin' => $todayCheckin,
            'todayCheckout' => $todayCheckout,
            'timeWindow' => $timeWindow,
            'wifiNetworks' => $wifiNetworks,
            'currentTime' => now()->format('H:i:s'),
            'canCheckin' => !$todayCheckin && ($timeWindow?->isCurrentlyOpen() || $timeWindow?->isWithinGracePeriod()),
            'canCheckout' => $todayCheckin && !$todayCheckout,
        ]);
    }

    /**
     * تسجيل الحضور
     */
    public function checkin(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'wifi_ssid' => 'nullable|string',
            'wifi_bssid' => 'nullable|string',
        ]);

        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json(['error' => 'لا يوجد ملف موظف'], 422);
        }

        // التحقق من عدم وجود تسجيل سابق اليوم
        if (QuickCheckin::hasTodayCheckin($employee->id, 'checkin')) {
            return response()->json(['error' => 'تم تسجيل حضورك مسبقاً اليوم'], 422);
        }

        $branch = $employee->branch;
        $verificationMethod = 'manual';
        $isVerified = false;

        // التحقق من Wi-Fi أولاً
        if ($validated['wifi_ssid']) {
            $wifiNetwork = WifiNetwork::verifyNetwork(
                $validated['wifi_ssid'],
                $validated['wifi_bssid'] ?? null
            );
            if ($wifiNetwork && $wifiNetwork->branch_id == $branch->id) {
                $verificationMethod = 'wifi';
                $isVerified = true;
            }
        }

        // التحقق من GPS إذا لم يتم التحقق من Wi-Fi
        if (!$isVerified && $validated['latitude'] && $validated['longitude'] && $branch) {
            $isWithinGeofence = $this->geofencingService->isWithinGeofence(
                $validated['latitude'],
                $validated['longitude'],
                $branch->latitude,
                $branch->longitude,
                $branch->geofence_radius
            );
            if ($isWithinGeofence) {
                $verificationMethod = $verificationMethod === 'wifi' ? 'both' : 'gps';
                $isVerified = true;
            }
        }

        // حساب التأخير
        $timeWindow = TimeWindow::getActiveWindow($branch?->id, 'checkin');
        $lateMinutes = $timeWindow ? $timeWindow->calculateLateMinutes() : 0;

        // إنشاء سجل الحضور
        $checkin = QuickCheckin::create([
            'employee_id' => $employee->id,
            'branch_id' => $branch->id ?? 1,
            'type' => 'checkin',
            'checked_at' => now(),
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'wifi_ssid' => $validated['wifi_ssid'],
            'wifi_bssid' => $validated['wifi_bssid'],
            'verification_method' => $verificationMethod,
            'is_verified' => $isVerified,
            'late_minutes' => $lateMinutes,
            'device_info' => $request->userAgent(),
            'ip_address' => $request->ip(),
        ]);

        // حساب الخصم
        $deduction = DeductionTier::calculateDeduction($lateMinutes);

        // تحديث سجل الحالة اليومي
        EmployeeStatusLog::updateOrCreateToday($employee->id, [
            'status' => $lateMinutes > 0 ? 'late' : 'present',
            'checkin_time' => now()->format('H:i:s'),
            'late_minutes' => $lateMinutes,
            'deduction_points' => $deduction['points'],
            'is_perfect_day' => $lateMinutes == 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => $lateMinutes > 0 
                ? "تم تسجيل حضورك بتأخير {$lateMinutes} دقيقة" 
                : 'تم تسجيل حضورك بنجاح',
            'checkin' => $checkin,
            'deduction' => $deduction,
            'is_verified' => $isVerified,
            'verification_method' => $verificationMethod,
        ]);
    }

    /**
     * تسجيل الانصراف
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json(['error' => 'لا يوجد ملف موظف'], 422);
        }

        // التحقق من وجود تسجيل حضور اليوم
        if (!QuickCheckin::hasTodayCheckin($employee->id, 'checkin')) {
            return response()->json(['error' => 'لم يتم تسجيل حضورك اليوم'], 422);
        }

        // التحقق من عدم وجود انصراف سابق
        if (QuickCheckin::hasTodayCheckin($employee->id, 'checkout')) {
            return response()->json(['error' => 'تم تسجيل انصرافك مسبقاً اليوم'], 422);
        }

        $branch = $employee->branch;

        $checkout = QuickCheckin::create([
            'employee_id' => $employee->id,
            'branch_id' => $branch->id ?? 1,
            'type' => 'checkout',
            'checked_at' => now(),
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'verification_method' => 'gps',
            'is_verified' => true,
            'device_info' => $request->userAgent(),
            'ip_address' => $request->ip(),
        ]);

        // تحديث سجل الحالة اليومي
        $todayLog = EmployeeStatusLog::getTodayLog($employee->id);
        if ($todayLog) {
            $checkinTime = $todayLog->checkin_time;
            $checkoutTime = now();
            $workedMinutes = $checkinTime ? $checkoutTime->diffInMinutes($checkinTime) : 0;
            
            $todayLog->update([
                'checkout_time' => $checkoutTime->format('H:i:s'),
                'worked_minutes' => $workedMinutes,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل انصرافك بنجاح',
            'checkout' => $checkout,
        ]);
    }

    /**
     * الحصول على حالة الحضور الحية
     */
    public function liveStatus(Request $request)
    {
        $user = auth()->user();
        $companyUserIds = getCompanyAndUsersId();

        $branchId = $request->input('branch_id');

        $query = Employee::whereIn('created_by', $companyUserIds)
            ->with(['branch', 'department']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $employees = $query->get()->map(function ($employee) {
            $todayLog = EmployeeStatusLog::getTodayLog($employee->id);
            $todayCheckin = QuickCheckin::where('employee_id', $employee->id)
                ->where('type', 'checkin')
                ->whereDate('checked_at', today())
                ->first();

            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'branch' => $employee->branch?->name,
                'department' => $employee->department?->name,
                'status' => $todayLog?->status ?? 'absent',
                'status_color' => $todayLog?->status_color ?? 'gray',
                'status_label' => $todayLog?->status_label ?? 'غائب',
                'checkin_time' => $todayLog?->checkin_time?->format('H:i'),
                'late_minutes' => $todayLog?->late_minutes ?? 0,
                'is_verified' => $todayCheckin?->is_verified ?? false,
            ];
        });

        $stats = [
            'total' => $employees->count(),
            'present' => $employees->where('status', 'present')->count(),
            'late' => $employees->where('status', 'late')->count(),
            'absent' => $employees->where('status', 'absent')->count(),
            'on_leave' => $employees->where('status', 'on_leave')->count(),
        ];

        return response()->json([
            'employees' => $employees,
            'stats' => $stats,
            'updated_at' => now()->format('H:i:s'),
        ]);
    }
}
