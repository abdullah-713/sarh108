<?php

namespace App\Http\Controllers;

use App\Models\DeductionTier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeductionTierController extends Controller
{
    /**
     * عرض مستويات الخصم
     */
    public function index()
    {
        $tiers = DeductionTier::with('creator')
            ->orderBy('min_minutes', 'asc')
            ->paginate(15);

        return Inertia::render('hr/deduction-tiers/index', [
            'tiers' => $tiers,
        ]);
    }

    /**
     * إنشاء مستوى خصم
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'min_minutes' => 'required|integer|min:0',
            'max_minutes' => 'required|integer|gt:min_minutes',
            'deduction_points' => 'required|integer|min:0',
            'deduction_percentage' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();

        DeductionTier::create($validated);

        return back()->with('success', 'تم إنشاء مستوى الخصم بنجاح');
    }

    /**
     * تحديث مستوى خصم
     */
    public function update(Request $request, DeductionTier $deductionTier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'min_minutes' => 'required|integer|min:0',
            'max_minutes' => 'required|integer|gt:min_minutes',
            'deduction_points' => 'required|integer|min:0',
            'deduction_percentage' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $deductionTier->update($validated);

        return back()->with('success', 'تم تحديث مستوى الخصم بنجاح');
    }

    /**
     * حذف مستوى خصم
     */
    public function destroy(DeductionTier $deductionTier)
    {
        $deductionTier->delete();
        return back()->with('success', 'تم حذف مستوى الخصم بنجاح');
    }

    /**
     * حساب الخصم
     */
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'late_minutes' => 'required|integer|min:0',
        ]);

        $deduction = DeductionTier::calculateDeduction($validated['late_minutes']);

        return response()->json($deduction);
    }
}
