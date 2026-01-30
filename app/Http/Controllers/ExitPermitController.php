<?php

namespace App\Http\Controllers;

use App\Models\ExitPermit;
use App\Models\ExitPermitSetting;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExitPermitController extends Controller
{
    /**
     * Display a listing of exit permits.
     */
    public function index(Request $request): Response
    {
        $companyId = auth()->user()->company_id;
        
        $query = ExitPermit::forCompany($companyId)
            ->with(['employee', 'branch', 'department', 'approvedByUser']);

        // Filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('permit_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('permit_date', '<=', $request->date_to);
        }

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $permits = $query->latest('permit_date')->paginate(20);

        // Stats
        $stats = [
            'pending' => ExitPermit::forCompany($companyId)->pending()->count(),
            'today' => ExitPermit::forCompany($companyId)->forToday()->count(),
            'approved_today' => ExitPermit::forCompany($companyId)->forToday()->approved()->count(),
        ];

        return Inertia::render('hr/exit-permits/index', [
            'permits' => $permits,
            'stats' => $stats,
            'permitTypes' => ExitPermit::$permitTypes,
            'statuses' => ExitPermit::$statuses,
            'filters' => $request->only(['status', 'date_from', 'date_to', 'employee_id']),
        ]);
    }

    /**
     * Show the form for creating a new exit permit.
     */
    public function create(): Response
    {
        $companyId = auth()->user()->company_id;
        
        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'active')
            ->select('id', 'first_name', 'last_name', 'branch_id', 'department_id')
            ->with(['branch:id,name', 'department:id,name'])
            ->get();

        $settings = ExitPermitSetting::getForCompany($companyId);

        return Inertia::render('hr/exit-permits/create', [
            'employees' => $employees,
            'permitTypes' => ExitPermit::$permitTypes,
            'settings' => $settings,
        ]);
    }

    /**
     * Store a newly created exit permit.
     */
    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;
        
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'permit_type' => 'required|in:personal,official,medical,emergency,other',
            'permit_date' => 'required|date|after_or_equal:today',
            'exit_time' => 'required|date_format:H:i',
            'expected_return_time' => 'required|date_format:H:i|after:exit_time',
            'reason' => 'required|string|max:1000',
            'destination' => 'nullable|string|max:255',
            'requires_vehicle' => 'boolean',
            'vehicle_number' => 'nullable|string|max:50',
            'affects_attendance' => 'boolean',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $settings = ExitPermitSetting::getForCompany($companyId);

        // Calculate duration
        $exitTime = \Carbon\Carbon::createFromFormat('H:i', $validated['exit_time']);
        $returnTime = \Carbon\Carbon::createFromFormat('H:i', $validated['expected_return_time']);
        $durationMinutes = $returnTime->diffInMinutes($exitTime);

        // Validate against settings
        $errors = $settings->validateRequest($employee, $validated['permit_type'], $durationMinutes);
        if (!empty($errors)) {
            return back()->withErrors(['general' => implode(', ', $errors)]);
        }

        $validated['company_id'] = $companyId;
        $validated['branch_id'] = $employee->branch_id;
        $validated['department_id'] = $employee->department_id;

        // Auto-approve official permits if setting enabled
        if ($settings->auto_approve_official && $validated['permit_type'] === 'official') {
            $validated['status'] = 'approved';
            $validated['approved_by'] = auth()->id();
            $validated['approved_at'] = now();
        }

        // No approval required
        if (!$settings->require_approval) {
            $validated['status'] = 'approved';
            $validated['approved_by'] = auth()->id();
            $validated['approved_at'] = now();
        }

        $permit = ExitPermit::create($validated);

        return redirect()->route('hr.exit-permits.index')
            ->with('success', 'تم إنشاء تصريح الخروج بنجاح');
    }

    /**
     * Display the specified exit permit.
     */
    public function show(ExitPermit $exitPermit): Response
    {
        $exitPermit->load(['employee', 'branch', 'department', 'approvedByUser', 'extensionApprovedByUser']);

        return Inertia::render('hr/exit-permits/show', [
            'permit' => $exitPermit,
        ]);
    }

    /**
     * Approve the exit permit.
     */
    public function approve(Request $request, ExitPermit $exitPermit)
    {
        if ($exitPermit->status !== 'pending') {
            return back()->withErrors(['general' => 'لا يمكن الموافقة على هذا التصريح']);
        }

        $note = $request->input('approval_note');
        $exitPermit->approve(auth()->id(), $note);

        return back()->with('success', 'تمت الموافقة على التصريح');
    }

    /**
     * Reject the exit permit.
     */
    public function reject(Request $request, ExitPermit $exitPermit)
    {
        if ($exitPermit->status !== 'pending') {
            return back()->withErrors(['general' => 'لا يمكن رفض هذا التصريح']);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $exitPermit->reject(auth()->id(), $validated['rejection_reason']);

        return back()->with('success', 'تم رفض التصريح');
    }

    /**
     * Mark permit as used (employee left).
     */
    public function markUsed(ExitPermit $exitPermit)
    {
        if (!$exitPermit->canBeUsed()) {
            return back()->withErrors(['general' => 'لا يمكن استخدام هذا التصريح']);
        }

        $exitPermit->markAsUsed();

        return back()->with('success', 'تم تسجيل الخروج');
    }

    /**
     * Record return.
     */
    public function recordReturn(ExitPermit $exitPermit)
    {
        if ($exitPermit->status !== 'used') {
            return back()->withErrors(['general' => 'لا يمكن تسجيل العودة لهذا التصريح']);
        }

        $exitPermit->recordReturn();

        return back()->with('success', 'تم تسجيل العودة');
    }

    /**
     * Request extension.
     */
    public function requestExtension(Request $request, ExitPermit $exitPermit)
    {
        $validated = $request->validate([
            'extended_return_time' => 'required|date_format:H:i',
            'extension_reason' => 'required|string|max:500',
        ]);

        $exitPermit->requestExtension(
            $validated['extended_return_time'],
            $validated['extension_reason']
        );

        return back()->with('success', 'تم طلب التمديد');
    }

    /**
     * Approve extension.
     */
    public function approveExtension(ExitPermit $exitPermit)
    {
        if (!$exitPermit->is_extended || $exitPermit->extension_approved_by) {
            return back()->withErrors(['general' => 'لا يمكن الموافقة على هذا التمديد']);
        }

        $exitPermit->update([
            'extension_approved_by' => auth()->id(),
            'expected_return_time' => $exitPermit->extended_return_time,
        ]);

        return back()->with('success', 'تمت الموافقة على التمديد');
    }

    /**
     * Get pending permits count (API).
     */
    public function getPendingCount()
    {
        $companyId = auth()->user()->company_id;
        $count = ExitPermit::forCompany($companyId)->pending()->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Verify permit by QR code (API).
     */
    public function verifyByQr(Request $request)
    {
        $validated = $request->validate([
            'qr_code' => 'required|string',
        ]);

        $permit = ExitPermit::where('qr_code', $validated['qr_code'])->first();

        if (!$permit) {
            return response()->json(['valid' => false, 'message' => 'تصريح غير موجود'], 404);
        }

        if (!$permit->canBeUsed()) {
            return response()->json([
                'valid' => false,
                'message' => 'التصريح غير صالح للاستخدام',
                'status' => $permit->status,
            ]);
        }

        $permit->load(['employee', 'branch']);

        return response()->json([
            'valid' => true,
            'permit' => $permit,
        ]);
    }

    /**
     * Exit permit settings.
     */
    public function settings(): Response
    {
        $companyId = auth()->user()->company_id;
        $settings = ExitPermitSetting::getForCompany($companyId);

        return Inertia::render('settings/exit-permit-settings', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update settings.
     */
    public function updateSettings(Request $request)
    {
        $companyId = auth()->user()->company_id;
        
        $validated = $request->validate([
            'require_approval' => 'boolean',
            'max_permits_per_day' => 'required|integer|min:1|max:10',
            'max_permits_per_month' => 'required|integer|min:1|max:50',
            'max_duration_minutes' => 'required|integer|min:15|max:480',
            'min_advance_hours' => 'required|integer|min:0|max:48',
            'allow_same_day_request' => 'boolean',
            'notify_manager' => 'boolean',
            'notify_hr' => 'boolean',
            'auto_approve_official' => 'boolean',
            'exempt_employees' => 'nullable|array',
            'exempt_designations' => 'nullable|array',
        ]);

        ExitPermitSetting::updateOrCreate(
            ['company_id' => $companyId],
            $validated
        );

        return back()->with('success', 'تم تحديث الإعدادات بنجاح');
    }
}
