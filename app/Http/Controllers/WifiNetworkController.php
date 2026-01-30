<?php

namespace App\Http\Controllers;

use App\Models\WifiNetwork;
use App\Models\Branch;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WifiNetworkController extends Controller
{
    /**
     * عرض قائمة الشبكات
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $companyUserIds = getCompanyAndUsersId();

        $networks = WifiNetwork::with(['branch', 'creator'])
            ->whereHas('branch', function ($query) use ($companyUserIds) {
                $query->whereIn('created_by', $companyUserIds);
            })
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('ssid', 'like', "%{$search}%");
                });
            })
            ->when($request->branch_id, function ($query, $branchId) {
                $query->where('branch_id', $branchId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $branches = Branch::whereIn('created_by', $companyUserIds)
            ->select('id', 'name')
            ->get();

        return Inertia::render('hr/wifi-networks/index', [
            'networks' => $networks,
            'branches' => $branches,
            'filters' => $request->only(['search', 'branch_id']),
        ]);
    }

    /**
     * إنشاء شبكة جديدة
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ssid' => 'required|string|max:255',
            'bssid' => 'nullable|string|max:50',
            'branch_id' => 'required|exists:branches,id',
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();

        // إذا كانت أساسية، إلغاء الأساسية من الشبكات الأخرى
        if ($validated['is_primary'] ?? false) {
            WifiNetwork::where('branch_id', $validated['branch_id'])
                ->update(['is_primary' => false]);
        }

        WifiNetwork::create($validated);

        return back()->with('success', 'تم إضافة الشبكة بنجاح');
    }

    /**
     * تحديث شبكة
     */
    public function update(Request $request, WifiNetwork $wifiNetwork)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ssid' => 'required|string|max:255',
            'bssid' => 'nullable|string|max:50',
            'branch_id' => 'required|exists:branches,id',
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validated['is_primary'] ?? false) {
            WifiNetwork::where('branch_id', $validated['branch_id'])
                ->where('id', '!=', $wifiNetwork->id)
                ->update(['is_primary' => false]);
        }

        $wifiNetwork->update($validated);

        return back()->with('success', 'تم تحديث الشبكة بنجاح');
    }

    /**
     * حذف شبكة
     */
    public function destroy(WifiNetwork $wifiNetwork)
    {
        $wifiNetwork->delete();
        return back()->with('success', 'تم حذف الشبكة بنجاح');
    }

    /**
     * التحقق من شبكة Wi-Fi
     */
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'ssid' => 'required|string',
            'bssid' => 'nullable|string',
        ]);

        $network = WifiNetwork::verifyNetwork(
            $validated['ssid'],
            $validated['bssid'] ?? null
        );

        if ($network) {
            return response()->json([
                'verified' => true,
                'network' => $network->load('branch'),
                'message' => 'تم التحقق من الشبكة بنجاح',
            ]);
        }

        return response()->json([
            'verified' => false,
            'message' => 'الشبكة غير مسجلة في النظام',
        ], 422);
    }
}
