<?php

namespace App\Http\Controllers;

use App\Models\TimeWindow;
use App\Models\Branch;
use App\Models\Shift;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TimeWindowController extends Controller
{
    /**
     * عرض النوافذ الزمنية
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $companyUserIds = getCompanyAndUsersId();

        $timeWindows = TimeWindow::with(['branch', 'shift', 'creator'])
            ->where(function ($query) use ($companyUserIds) {
                $query->whereIn('created_by', $companyUserIds)
                      ->orWhereNull('branch_id');
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->orderBy('start_time', 'asc')
            ->paginate(15)
            ->withQueryString();

        $branches = Branch::whereIn('created_by', $companyUserIds)
            ->select('id', 'name')
            ->get();

        $shifts = Shift::whereIn('created_by', $companyUserIds)
            ->select('id', 'name')
            ->get();

        return Inertia::render('hr/time-windows/index', [
            'timeWindows' => $timeWindows,
            'branches' => $branches,
            'shifts' => $shifts,
            'filters' => $request->only(['type']),
        ]);
    }

    /**
     * إنشاء نافذة زمنية
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:checkin,checkout',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'grace_period' => 'required|integer|min:0|max:60',
            'branch_id' => 'nullable|exists:branches,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();

        TimeWindow::create($validated);

        return back()->with('success', 'تم إنشاء النافذة الزمنية بنجاح');
    }

    /**
     * تحديث نافذة زمنية
     */
    public function update(Request $request, TimeWindow $timeWindow)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:checkin,checkout',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'grace_period' => 'required|integer|min:0|max:60',
            'branch_id' => 'nullable|exists:branches,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'is_active' => 'boolean',
        ]);

        $timeWindow->update($validated);

        return back()->with('success', 'تم تحديث النافذة الزمنية بنجاح');
    }

    /**
     * حذف نافذة زمنية
     */
    public function destroy(TimeWindow $timeWindow)
    {
        $timeWindow->delete();
        return back()->with('success', 'تم حذف النافذة الزمنية بنجاح');
    }

    /**
     * الحصول على النافذة النشطة الحالية
     */
    public function getCurrentWindow(Request $request)
    {
        $branchId = $request->input('branch_id');
        $type = $request->input('type', 'checkin');

        $window = TimeWindow::getActiveWindow($branchId, $type);

        if (!$window) {
            return response()->json([
                'is_open' => false,
                'message' => 'لا توجد نافذة زمنية نشطة',
            ]);
        }

        return response()->json([
            'is_open' => $window->isCurrentlyOpen(),
            'within_grace' => $window->isWithinGracePeriod(),
            'window' => $window,
            'current_time' => now()->format('H:i:s'),
            'late_minutes' => $window->calculateLateMinutes(),
        ]);
    }
}
