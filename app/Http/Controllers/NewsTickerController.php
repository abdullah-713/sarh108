<?php

namespace App\Http\Controllers;

use App\Models\NewsTicker;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NewsTickerController extends Controller
{
    /**
     * عرض صفحة إدارة الأخبار
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $companyUserIds = $user->getAllCompanyUserIds();

        $newsItems = NewsTicker::whereIn('created_by', $companyUserIds)
            ->orWhere('is_global', true)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $branches = Branch::whereIn('created_by', $companyUserIds)
            ->select('id', 'name')
            ->get();

        return Inertia::render('settings/news-ticker', [
            'newsItems' => $newsItems,
            'branches' => $branches,
        ]);
    }

    /**
     * إنشاء خبر جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'type' => 'required|in:announcement,achievement,reminder,warning,celebration,mvp,badge,streak,custom',
            'priority' => 'required|in:low,normal,high,urgent',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'background_color' => 'nullable|string|max:20',
            'branch_id' => 'nullable|exists:branches,id',
            'target_departments' => 'nullable|array',
            'is_global' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
            'action_url' => 'nullable|url|max:500',
            'action_text' => 'nullable|string|max:100',
        ]);

        $validated['created_by'] = auth()->id();

        NewsTicker::create($validated);

        return redirect()->back()->with('success', 'تم إنشاء الخبر بنجاح');
    }

    /**
     * تحديث خبر
     */
    public function update(Request $request, NewsTicker $newsTicker)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'type' => 'required|in:announcement,achievement,reminder,warning,celebration,mvp,badge,streak,custom',
            'priority' => 'required|in:low,normal,high,urgent',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'background_color' => 'nullable|string|max:20',
            'branch_id' => 'nullable|exists:branches,id',
            'target_departments' => 'nullable|array',
            'is_global' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
            'action_url' => 'nullable|url|max:500',
            'action_text' => 'nullable|string|max:100',
        ]);

        $newsTicker->update($validated);

        return redirect()->back()->with('success', 'تم تحديث الخبر بنجاح');
    }

    /**
     * حذف خبر
     */
    public function destroy(NewsTicker $newsTicker)
    {
        $newsTicker->delete();

        return redirect()->back()->with('success', 'تم حذف الخبر بنجاح');
    }

    /**
     * الحصول على الأخبار النشطة (API)
     */
    public function getActive(Request $request)
    {
        $branchId = $request->get('branch_id');
        $departmentId = $request->get('department_id');

        $news = NewsTicker::getActive($branchId, $departmentId);

        return response()->json([
            'success' => true,
            'data' => $news->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'content' => $item->content,
                    'type' => $item->type,
                    'type_name' => $item->type_name,
                    'type_color' => $item->type_color,
                    'type_icon' => $item->type_icon,
                    'priority' => $item->priority,
                    'icon' => $item->icon,
                    'color' => $item->color ?? $item->type_color,
                    'background_color' => $item->background_color,
                    'action_url' => $item->action_url,
                    'action_text' => $item->action_text,
                ];
            }),
        ]);
    }

    /**
     * تسجيل مشاهدة
     */
    public function trackView($id)
    {
        $news = NewsTicker::find($id);
        if ($news) {
            $news->incrementViews();
        }

        return response()->json(['success' => true]);
    }

    /**
     * تسجيل نقرة
     */
    public function trackClick($id)
    {
        $news = NewsTicker::find($id);
        if ($news) {
            $news->incrementClicks();
        }

        return response()->json(['success' => true]);
    }
}
