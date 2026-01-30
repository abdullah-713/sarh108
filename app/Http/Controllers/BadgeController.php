<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\EmployeeBadge;
use App\Models\Employee;
use App\Services\BadgeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BadgeController extends Controller
{
    protected BadgeService $badgeService;

    public function __construct(BadgeService $badgeService)
    {
        $this->badgeService = $badgeService;
    }

    /**
     * عرض صفحة إدارة الشارات
     */
    public function index(Request $request)
    {
        $badges = Badge::orderBy('sort_order')->get();

        return Inertia::render('hr/badges/index', [
            'badges' => $badges->map(function ($badge) {
                return [
                    'id' => $badge->id,
                    'name' => $badge->name,
                    'name_ar' => $badge->name_ar,
                    'slug' => $badge->slug,
                    'description' => $badge->description,
                    'description_ar' => $badge->description_ar,
                    'icon' => $badge->icon,
                    'color' => $badge->color,
                    'background_color' => $badge->background_color,
                    'tier' => $badge->tier,
                    'tier_name' => $badge->tier_name,
                    'tier_color' => $badge->tier_color,
                    'type' => $badge->type,
                    'type_name' => $badge->type_name,
                    'required_days' => $badge->required_days,
                    'required_streak' => $badge->required_streak,
                    'required_rate' => $badge->required_rate,
                    'points' => $badge->points,
                    'is_active' => $badge->is_active,
                    'is_auto_award' => $badge->is_auto_award,
                    'employees_count' => $badge->employees()->count(),
                ];
            }),
        ]);
    }

    /**
     * إنشاء شارة جديدة
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:badges,slug',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'background_color' => 'nullable|string|max:20',
            'tier' => 'required|in:bronze,silver,gold,platinum,diamond',
            'type' => 'required|in:punctuality,attendance,early_bird,streak,perfect_month,mvp,team_player,custom',
            'required_days' => 'nullable|integer|min:1',
            'required_streak' => 'nullable|integer|min:1',
            'required_rate' => 'nullable|numeric|min:0|max:100',
            'points' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_auto_award' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();

        Badge::create($validated);

        return redirect()->back()->with('success', 'تم إنشاء الشارة بنجاح');
    }

    /**
     * تحديث شارة
     */
    public function update(Request $request, Badge $badge)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'background_color' => 'nullable|string|max:20',
            'tier' => 'required|in:bronze,silver,gold,platinum,diamond',
            'type' => 'required|in:punctuality,attendance,early_bird,streak,perfect_month,mvp,team_player,custom',
            'required_days' => 'nullable|integer|min:1',
            'required_streak' => 'nullable|integer|min:1',
            'required_rate' => 'nullable|numeric|min:0|max:100',
            'points' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_auto_award' => 'boolean',
        ]);

        $badge->update($validated);

        return redirect()->back()->with('success', 'تم تحديث الشارة بنجاح');
    }

    /**
     * حذف شارة
     */
    public function destroy(Badge $badge)
    {
        $badge->delete();

        return redirect()->back()->with('success', 'تم حذف الشارة بنجاح');
    }

    /**
     * منح شارة لموظف
     */
    public function awardToEmployee(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'badge_id' => 'required|exists:badges,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $result = $this->badgeService->awardBadgeManually(
            $validated['employee_id'],
            $validated['badge_id'],
            $validated['reason'] ?? null,
            auth()->id()
        );

        if ($result) {
            return redirect()->back()->with('success', 'تم منح الشارة بنجاح');
        }

        return redirect()->back()->with('error', 'فشل في منح الشارة');
    }

    /**
     * الحصول على شارات موظف
     */
    public function getEmployeeBadges($employeeId)
    {
        $badges = $this->badgeService->getEmployeeBadges($employeeId);

        return response()->json([
            'success' => true,
            'data' => $badges,
        ]);
    }

    /**
     * لوحة الصدارة
     */
    public function leaderboard(Request $request)
    {
        $user = auth()->user();
        $companyUserIds = $user->getAllCompanyUserIds();

        $limit = $request->get('limit', 10);
        $leaderboard = $this->badgeService->getLeaderboard($limit, $companyUserIds);

        return response()->json([
            'success' => true,
            'data' => $leaderboard,
        ]);
    }

    /**
     * إنشاء الشارات الافتراضية
     */
    public function createDefaults()
    {
        Badge::createDefaults(auth()->id());

        return redirect()->back()->with('success', 'تم إنشاء الشارات الافتراضية بنجاح');
    }
}
