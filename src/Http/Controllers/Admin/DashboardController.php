<?php

namespace KwtSMS\Laravel\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use KwtSMS\Laravel\Models\KwtSmsLog;
use KwtSMS\Laravel\Models\KwtSmsSetting;

class DashboardController extends Controller
{
    public function index(): View
    {
        $balance = KwtSmsSetting::get('balance_available');
        $senderids = KwtSmsSetting::get('senderids', []);
        $coverage = KwtSmsSetting::get('coverage', []);
        $lastSync = KwtSmsSetting::get('last_sync_at');
        $recentLogs = KwtSmsLog::query()->latest()->limit(10)->get();
        $totalSent7Days = KwtSmsLog::query()
            ->where('status', 'sent')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        $totalSent30Days = KwtSmsLog::query()
            ->where('status', 'sent')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
        $isConfigured = ! empty(config('kwtsms.username')) && ! empty(config('kwtsms.password'));
        $isEnabled = (bool) config('kwtsms.enabled', true);
        $isTestMode = (bool) config('kwtsms.test_mode', false);

        return view('kwtsms::admin.dashboard', compact(
            'balance', 'senderids', 'coverage', 'lastSync',
            'recentLogs', 'totalSent7Days', 'totalSent30Days',
            'isConfigured', 'isEnabled', 'isTestMode'
        ));
    }
}
