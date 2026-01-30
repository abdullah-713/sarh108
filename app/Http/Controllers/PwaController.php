<?php

namespace App\Http\Controllers;

use App\Models\PwaConfiguration;
use App\Models\PushSubscription;
use App\Models\NotificationQueue;
use App\Models\OfflineSyncQueue;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PwaController extends Controller
{
    /**
     * Get PWA manifest.
     */
    public function manifest(Request $request)
    {
        $companyId = auth()->user()?->company_id;
        
        if ($companyId) {
            $config = PwaConfiguration::getForCompany($companyId);
            $manifest = $config->generateManifest();
        } else {
            // Default manifest
            $manifest = [
                'name' => 'صرح الإتقان',
                'short_name' => 'صرح',
                'description' => 'نظام إدارة الموارد البشرية',
                'theme_color' => '#ff8531',
                'background_color' => '#ffffff',
                'display' => 'standalone',
                'orientation' => 'portrait',
                'start_url' => '/dashboard',
                'scope' => '/',
                'dir' => 'rtl',
                'lang' => 'ar',
                'icons' => [
                    ['src' => '/images/icons/icon-192x192.png', 'sizes' => '192x192', 'type' => 'image/png'],
                    ['src' => '/images/icons/icon-512x512.png', 'sizes' => '512x512', 'type' => 'image/png'],
                ],
            ];
        }

        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json');
    }

    /**
     * PWA settings page.
     */
    public function settings(): Response
    {
        $companyId = auth()->user()->company_id;
        $config = PwaConfiguration::getForCompany($companyId);

        return Inertia::render('settings/pwa-settings', [
            'config' => $config,
        ]);
    }

    /**
     * Update PWA settings.
     */
    public function updateSettings(Request $request)
    {
        $companyId = auth()->user()->company_id;
        
        $validated = $request->validate([
            'pwa_enabled' => 'boolean',
            'app_name' => 'required|string|max:255',
            'app_short_name' => 'required|string|max:50',
            'app_description' => 'nullable|string|max:500',
            'theme_color' => 'required|string|max:7',
            'background_color' => 'required|string|max:7',
            'display_mode' => 'required|in:standalone,fullscreen,minimal-ui,browser',
            'orientation' => 'required|in:any,portrait,landscape',
            'enable_push_notifications' => 'boolean',
            'enable_offline_mode' => 'boolean',
        ]);

        PwaConfiguration::updateOrCreate(
            ['company_id' => $companyId],
            $validated
        );

        return back()->with('success', 'تم تحديث إعدادات التطبيق');
    }

    /**
     * Subscribe to push notifications.
     */
    public function subscribePush(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string|max:500',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        $agent = new \Jenssegers\Agent\Agent();
        $agent->setUserAgent($request->userAgent());

        // Delete existing subscription with same endpoint
        PushSubscription::where('endpoint', $validated['endpoint'])->delete();

        PushSubscription::create([
            'company_id' => auth()->user()->company_id,
            'user_id' => auth()->id(),
            'endpoint' => $validated['endpoint'],
            'public_key' => $validated['keys']['p256dh'],
            'auth_token' => $validated['keys']['auth'],
            'device_type' => $agent->isMobile() ? 'mobile' : 'desktop',
            'browser' => $agent->browser(),
            'os' => $agent->platform(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Unsubscribe from push notifications.
     */
    public function unsubscribePush(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
        ]);

        PushSubscription::where('endpoint', $validated['endpoint'])
            ->where('user_id', auth()->id())
            ->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Get push subscription status.
     */
    public function pushStatus()
    {
        $subscription = PushSubscription::active()
            ->forUser(auth()->id())
            ->first();

        return response()->json([
            'subscribed' => $subscription !== null,
            'subscription' => $subscription,
        ]);
    }

    /**
     * Sync offline data.
     */
    public function syncOffline(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'actions' => 'required|array',
            'actions.*.type' => 'required|in:checkin,checkout,location_update,form_submit,data_sync',
            'actions.*.payload' => 'required|array',
            'actions.*.timestamp' => 'required|date',
        ]);

        $results = [];
        
        foreach ($validated['actions'] as $action) {
            $queueItem = OfflineSyncQueue::queue(
                auth()->id(),
                $validated['device_id'],
                $action['type'],
                $action['payload'],
                new \DateTime($action['timestamp'])
            );

            // Try to process immediately
            try {
                $queueItem->update(['sync_status' => 'processing']);
                $success = $queueItem->processAction();
                
                if ($success) {
                    $queueItem->markAsSynced();
                    $results[] = ['id' => $queueItem->id, 'status' => 'synced'];
                }
            } catch (\Exception $e) {
                $queueItem->markAsFailed($e->getMessage());
                $results[] = ['id' => $queueItem->id, 'status' => 'failed', 'error' => $e->getMessage()];
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Get pending sync items for device.
     */
    public function getPendingSync(Request $request)
    {
        $deviceId = $request->input('device_id');
        
        $pending = OfflineSyncQueue::pending()
            ->forDevice($deviceId)
            ->orderBy('client_timestamp')
            ->get();

        return response()->json($pending);
    }

    /**
     * Get offline-available data.
     */
    public function getOfflineData()
    {
        $user = auth()->user();
        $companyId = $user->company_id;

        // Data needed for offline operation
        $data = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'employee' => $user->employee ? [
                'id' => $user->employee->id,
                'name' => $user->employee->first_name . ' ' . $user->employee->last_name,
                'branch_id' => $user->employee->branch_id,
            ] : null,
            'branches' => \App\Models\Branch::where('company_id', $companyId)
                ->where('is_active', true)
                ->select('id', 'name', 'latitude', 'longitude', 'geo_radius')
                ->get(),
            'settings' => [
                'require_photo' => true,
                'require_location' => true,
            ],
            'last_sync' => now()->toIso8601String(),
        ];

        return response()->json($data);
    }

    /**
     * Service worker script.
     */
    public function serviceWorker()
    {
        $content = view('pwa.service-worker')->render();
        
        return response($content)
            ->header('Content-Type', 'application/javascript')
            ->header('Service-Worker-Allowed', '/');
    }
}
