<?php

namespace App\Http\Controllers;

use App\Models\LockdownEvent;
use App\Models\LockdownAttendanceLog;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LockdownController extends Controller
{
    /**
     * Display lockdown events.
     */
    public function index(Request $request): Response
    {
        $companyId = auth()->user()->company_id;
        
        $query = LockdownEvent::forCompany($companyId)
            ->with(['branch', 'initiator', 'ender']);

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $events = $query->latest('created_at')->paginate(20);

        // Get active lockdown if any
        $activeLockdown = LockdownEvent::getActiveLockdown($companyId);

        return Inertia::render('security/lockdown', [
            'events' => $events,
            'activeLockdown' => $activeLockdown,
            'lockdownTypes' => LockdownEvent::$lockdownTypes,
            'statuses' => LockdownEvent::$statuses,
        ]);
    }

    /**
     * Create new lockdown event.
     */
    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        // Check if there's already an active lockdown
        $existing = LockdownEvent::getActiveLockdown($companyId, $request->branch_id);
        if ($existing) {
            return back()->withErrors(['general' => 'يوجد بالفعل إغلاق نشط']);
        }

        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'title' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'lockdown_type' => 'required|in:full,partial,checkin_only,checkout_only,emergency',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
            'allow_emergency_checkin' => 'boolean',
            'allow_emergency_checkout' => 'boolean',
            'exempt_employees' => 'nullable|array',
            'exempt_departments' => 'nullable|array',
            'exempt_designations' => 'nullable|array',
            'notification_message' => 'nullable|string|max:1000',
            'notification_message_ar' => 'nullable|string|max:1000',
            'notify_employees' => 'boolean',
            'notify_managers' => 'boolean',
        ]);

        $validated['company_id'] = $companyId;
        $validated['initiated_by'] = auth()->id();

        $lockdown = LockdownEvent::createLockdown($validated);

        // TODO: Send notifications if enabled

        return back()->with('success', 'تم إنشاء الإغلاق بنجاح');
    }

    /**
     * Show lockdown details.
     */
    public function show(LockdownEvent $lockdownEvent): Response
    {
        $lockdownEvent->load([
            'branch',
            'initiator',
            'ender',
            'attendanceLogs.employee',
            'attendanceLogs.overriddenByUser',
        ]);

        // Stats
        $stats = [
            'blocked_checkins' => $lockdownEvent->attendanceLogs()->where('action_type', 'blocked_checkin')->count(),
            'blocked_checkouts' => $lockdownEvent->attendanceLogs()->where('action_type', 'blocked_checkout')->count(),
            'emergency_accesses' => $lockdownEvent->attendanceLogs()->whereIn('action_type', ['emergency_checkin', 'emergency_checkout'])->count(),
            'exempt_accesses' => $lockdownEvent->attendanceLogs()->where('action_type', 'exempt_access')->count(),
        ];

        return Inertia::render('security/lockdown-details', [
            'lockdown' => $lockdownEvent,
            'stats' => $stats,
        ]);
    }

    /**
     * End lockdown.
     */
    public function end(Request $request, LockdownEvent $lockdownEvent)
    {
        if ($lockdownEvent->status !== 'active') {
            return back()->withErrors(['general' => 'لا يمكن إنهاء هذا الإغلاق']);
        }

        $reason = $request->input('end_reason');
        $lockdownEvent->end(auth()->id(), $reason);

        return back()->with('success', 'تم إنهاء الإغلاق بنجاح');
    }

    /**
     * Cancel scheduled lockdown.
     */
    public function cancel(Request $request, LockdownEvent $lockdownEvent)
    {
        if ($lockdownEvent->status !== 'scheduled') {
            return back()->withErrors(['general' => 'لا يمكن إلغاء هذا الإغلاق']);
        }

        $reason = $request->input('cancel_reason');
        $lockdownEvent->cancel(auth()->id(), $reason);

        return back()->with('success', 'تم إلغاء الإغلاق المجدول');
    }

    /**
     * Check if attendance action is allowed (API).
     */
    public function checkAction(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'action' => 'required|in:checkin,checkout',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $lockdown = LockdownEvent::getActiveLockdown($employee->company_id, $employee->branch_id);

        if (!$lockdown) {
            return response()->json(['allowed' => true, 'lockdown' => null]);
        }

        $isAllowed = $lockdown->isActionAllowed($validated['action'], $employee);

        // Log the attempt
        $lockdown->logAttempt(
            $employee,
            $validated['action'],
            $isAllowed,
            [
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
            ]
        );

        return response()->json([
            'allowed' => $isAllowed,
            'lockdown' => [
                'id' => $lockdown->id,
                'type' => $lockdown->lockdown_type,
                'type_name' => $lockdown->lockdown_type_name,
                'title' => $lockdown->display_title,
                'message' => $lockdown->notification_message_ar ?: $lockdown->notification_message,
            ],
            'is_exempt' => $lockdown->isEmployeeExempt($employee),
        ]);
    }

    /**
     * Emergency override (API).
     */
    public function emergencyOverride(Request $request, LockdownEvent $lockdownEvent)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'action' => 'required|in:checkin,checkout',
            'override_reason' => 'required|string|max:500',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        // Log the override
        $lockdownEvent->logAttempt(
            $employee,
            $validated['action'],
            true,
            null,
            $validated['override_reason'],
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'message' => 'تم تجاوز الإغلاق بنجاح',
        ]);
    }

    /**
     * Get active lockdown status (API).
     */
    public function getActiveStatus()
    {
        $companyId = auth()->user()->company_id;
        $branchId = auth()->user()->employee?->branch_id;

        $lockdown = LockdownEvent::getActiveLockdown($companyId, $branchId);

        if (!$lockdown) {
            return response()->json(['active' => false]);
        }

        return response()->json([
            'active' => true,
            'lockdown' => [
                'id' => $lockdown->id,
                'type' => $lockdown->lockdown_type,
                'type_name' => $lockdown->lockdown_type_name,
                'title' => $lockdown->display_title,
                'message' => $lockdown->notification_message_ar ?: $lockdown->notification_message,
                'started_at' => $lockdown->start_time->toIso8601String(),
            ],
        ]);
    }
}
