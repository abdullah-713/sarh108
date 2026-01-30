<?php

namespace App\Http\Controllers;

use App\Models\BranchPerformance;
use App\Models\Branch;
use App\Services\BranchRankingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class BranchRankingController extends Controller
{
    protected BranchRankingService $rankingService;

    public function __construct(BranchRankingService $rankingService)
    {
        $this->rankingService = $rankingService;
    }

    /**
     * عرض صفحة ترتيب الفروع
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $companyUserIds = $user->getAllCompanyUserIds();

        $date = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::today();
        $period = $request->get('period', 'today');

        // الحصول على الترتيب
        $rankings = $this->rankingService->getRanking($date, $companyUserIds);

        // الفروع
        $branches = Branch::whereIn('created_by', $companyUserIds)
            ->select('id', 'name')
            ->get();

        return Inertia::render('reports/branch-ranking', [
            'rankings' => $rankings,
            'branches' => $branches,
            'selectedDate' => $date->format('Y-m-d'),
            'period' => $period,
        ]);
    }

    /**
     * الحصول على بيانات ترتيب الفروع (API)
     */
    public function getRankings(Request $request)
    {
        $user = auth()->user();
        $companyUserIds = $user->getAllCompanyUserIds();

        $date = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::today();

        $rankings = $this->rankingService->getRanking($date, $companyUserIds);

        return response()->json([
            'success' => true,
            'data' => $rankings,
            'date' => $date->format('Y-m-d'),
        ]);
    }

    /**
     * الحصول على إحصائيات فرع معين
     */
    public function getBranchStats(Request $request, $branchId)
    {
        $startDate = $request->get('start_date') 
            ? Carbon::parse($request->get('start_date')) 
            : Carbon::now()->startOfMonth();
        $endDate = $request->get('end_date') 
            ? Carbon::parse($request->get('end_date')) 
            : Carbon::now()->endOfMonth();

        $stats = BranchPerformance::getBranchStats($branchId, $startDate, $endDate);

        $branch = Branch::find($branchId);

        return response()->json([
            'success' => true,
            'branch' => [
                'id' => $branch->id,
                'name' => $branch->name,
            ],
            'stats' => $stats,
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * الحصول على أفضل الفروع
     */
    public function getTopBranches(Request $request)
    {
        $user = auth()->user();
        $companyUserIds = $user->getAllCompanyUserIds();

        $limit = $request->get('limit', 5);
        $period = $request->get('period', 'today');

        $topBranches = $this->rankingService->getTopBranches($limit, $period, $companyUserIds);

        return response()->json([
            'success' => true,
            'data' => $topBranches,
        ]);
    }

    /**
     * إعادة حساب الترتيب
     */
    public function recalculate(Request $request)
    {
        $user = auth()->user();
        $companyUserIds = $user->getAllCompanyUserIds();

        $date = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::today();

        $this->rankingService->calculateDailyPerformance($date, $companyUserIds);

        return response()->json([
            'success' => true,
            'message' => 'تم إعادة حساب الترتيب بنجاح',
        ]);
    }
}
