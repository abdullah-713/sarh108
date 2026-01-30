<?php

namespace App\Http\Controllers;

use App\Models\WorkZone;
use App\Models\ZoneAccessLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkZoneController extends Controller
{
    /**
     * Display a listing of work zones.
     */
    public function index(Request $request): Response
    {
        $companyId = auth()->user()->company_id;
        
        $zones = WorkZone::forCompany($companyId)
            ->with('branch')
            ->withCount('accessLogs')
            ->orderBy('display_order')
            ->paginate(20);

        return Inertia::render('settings/work-zones', [
            'zones' => $zones,
            'zoneTypes' => WorkZone::$zoneTypes,
        ]);
    }

    /**
     * Store a newly created work zone.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'zone_type' => 'required|in:indoor,outdoor,parking,gate,cafeteria,meeting,restricted,custom',
            'center_latitude' => 'nullable|numeric|between:-90,90',
            'center_longitude' => 'nullable|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:1|max:10000',
            'polygon_coordinates' => 'nullable|array',
            'floor_number' => 'nullable|integer',
            'building' => 'nullable|string|max:255',
            'requires_authentication' => 'boolean',
            'track_time_in_zone' => 'boolean',
            'min_time_minutes' => 'nullable|integer|min:0',
            'max_time_minutes' => 'nullable|integer|min:0',
            'allowed_employees' => 'nullable|array',
            'allowed_departments' => 'nullable|array',
            'allowed_designations' => 'nullable|array',
            'is_active' => 'boolean',
            'color' => 'nullable|string|max:7',
        ]);

        $validated['company_id'] = auth()->user()->company_id;

        $zone = WorkZone::create($validated);

        return back()->with('success', 'تم إنشاء منطقة العمل بنجاح');
    }

    /**
     * Update the specified work zone.
     */
    public function update(Request $request, WorkZone $workZone)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'zone_type' => 'required|in:indoor,outdoor,parking,gate,cafeteria,meeting,restricted,custom',
            'center_latitude' => 'nullable|numeric|between:-90,90',
            'center_longitude' => 'nullable|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:1|max:10000',
            'polygon_coordinates' => 'nullable|array',
            'floor_number' => 'nullable|integer',
            'building' => 'nullable|string|max:255',
            'requires_authentication' => 'boolean',
            'track_time_in_zone' => 'boolean',
            'min_time_minutes' => 'nullable|integer|min:0',
            'max_time_minutes' => 'nullable|integer|min:0',
            'allowed_employees' => 'nullable|array',
            'allowed_departments' => 'nullable|array',
            'allowed_designations' => 'nullable|array',
            'is_active' => 'boolean',
            'color' => 'nullable|string|max:7',
        ]);

        $workZone->update($validated);

        return back()->with('success', 'تم تحديث منطقة العمل بنجاح');
    }

    /**
     * Remove the specified work zone.
     */
    public function destroy(WorkZone $workZone)
    {
        $workZone->delete();

        return back()->with('success', 'تم حذف منطقة العمل بنجاح');
    }

    /**
     * Get zones for a branch (API).
     */
    public function getZonesForBranch(Request $request)
    {
        $branchId = $request->input('branch_id');
        
        $zones = WorkZone::active()
            ->forBranch($branchId)
            ->orderBy('display_order')
            ->get();

        return response()->json($zones);
    }

    /**
     * Log zone access (API).
     */
    public function logAccess(Request $request)
    {
        $validated = $request->validate([
            'zone_id' => 'required|exists:work_zones,id',
            'employee_id' => 'required|exists:employees,id',
            'access_type' => 'required|in:entry,exit',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'nullable|integer',
        ]);

        $zone = WorkZone::findOrFail($validated['zone_id']);
        $employee = \App\Models\Employee::findOrFail($validated['employee_id']);

        $log = $zone->logAccess(
            $employee,
            $validated['access_type'],
            $validated['latitude'],
            $validated['longitude'],
            $validated['accuracy'] ?? null
        );

        // Calculate duration if exit
        if ($validated['access_type'] === 'exit') {
            $duration = ZoneAccessLog::calculateDuration($zone->id, $employee->id);
            if ($duration !== null) {
                $log->update(['duration_minutes' => $duration]);
            }
        }

        return response()->json([
            'success' => true,
            'was_authorized' => $log->was_authorized,
            'log' => $log,
        ]);
    }

    /**
     * Get zone access logs.
     */
    public function accessLogs(Request $request): Response
    {
        $companyId = auth()->user()->company_id;
        
        $query = ZoneAccessLog::whereHas('workZone', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->with(['employee', 'workZone', 'branch']);

        if ($request->has('zone_id')) {
            $query->where('work_zone_id', $request->zone_id);
        }

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->has('date')) {
            $query->whereDate('access_time', $request->date);
        }

        $logs = $query->latest('access_time')->paginate(50);

        return Inertia::render('reports/zone-access-logs', [
            'logs' => $logs,
        ]);
    }

    /**
     * Get unauthorized access attempts.
     */
    public function unauthorizedAttempts(Request $request): Response
    {
        $companyId = auth()->user()->company_id;
        
        $attempts = ZoneAccessLog::getUnauthorizedAttempts(
            $companyId,
            $request->input('date_from'),
            $request->input('date_to')
        );

        return Inertia::render('reports/unauthorized-zone-access', [
            'attempts' => $attempts,
        ]);
    }
}
