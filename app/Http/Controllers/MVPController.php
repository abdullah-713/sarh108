<?php

namespace App\Http\Controllers;

use App\Services\MVPService;
use App\Services\BadgeService;
use App\Services\StreakService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class MVPController extends Controller
{
    protected MVPService $mvpService;
    protected BadgeService $badgeService;
    protected StreakService $streakService;

    public function __construct(
        MVPService $mvpService, 
        BadgeService $badgeService,
        StreakService $streakService
    ) {
        $this->mvpService = $mvpService;
        $this->badgeService = $badgeService;
        $this->streakService = $streakService;
    }

    /**
     * عرض لوحة MVP
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $companyUserIds = $user->getAllCompanyUserIds();

        $period = $request->get('period', 'month');

        // تحديد التواريخ حسب الفترة
        switch ($period) {
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'month':
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
        }

        $rankings = $this->mvpService->getMVPRanking(10, $startDate, $endDate, $companyUserIds);
        $topStreaks = $this->streakService->getTopStreaks(5, $companyUserIds);
        $leaderboard = $this->badgeService->getLeaderboard(10, $companyUserIds);

        return Inertia::render('hr/mvp-leaderboard', [
            'rankings' => $rankings,
            'topStreaks' => $topStreaks,
            'badgeLeaderboard' => $leaderboard,
            'period' => $period,
            'periodLabel' => $period === 'week' ? 'هذا الأسبوع' : 'هذا الشهر',
        ]);
    }

    /**
     * الحصول على العشرة الأوائل (API)
     */
    public function getTopTen(Request $request)
    {
        $user = auth()->user();
        $companyUserIds = $user->getAllCompanyUserIds();

        $rankings = $this->mvpService->getTopTen($companyUserIds);

        return response()->json([
            'success' => true,
            'data' => $rankings,
        ]);
    }

    /**
     * الحصول على أداء موظف معين
     */
    public function getEmployeePerformance($employeeId)
    {
        $performance = $this->mvpService->getEmployeePerformance($employeeId);

        return response()->json([
            'success' => true,
            'data' => $performance,
        ]);
    }

    /**
     * الحصول على أعلى السلاسل
     */
    public function getTopStreaks(Request $request)
    {
        $user = auth()->user();
        $companyUserIds = $user->getAllCompanyUserIds();

        $limit = $request->get('limit', 10);
        $streaks = $this->streakService->getTopStreaks($limit, $companyUserIds);

        return response()->json([
            'success' => true,
            'data' => $streaks,
        ]);
    }

    /**
     * الحصول على الأرقام القياسية
     */
    public function getRecordBreakers(Request $request)
    {
        $user = auth()->user();
        $companyUserIds = $user->getAllCompanyUserIds();

        $limit = $request->get('limit', 5);
        $records = $this->streakService->getRecordBreakers($limit, $companyUserIds);

        return response()->json([
            'success' => true,
            'data' => $records,
        ]);
    }

    /**
     * اختيار MVP الشهر
     */
    public function selectMonthlyMVP()
    {
        $user = auth()->user();
        $companyUserIds = $user->getAllCompanyUserIds();

        $mvp = $this->mvpService->selectMonthlyMVP($companyUserIds);

        if ($mvp) {
            return response()->json([
                'success' => true,
                'message' => 'تم اختيار MVP الشهر بنجاح',
                'data' => $mvp,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'لا يوجد موظفين مؤهلين',
        ], 400);
    }
}
