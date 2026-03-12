<?php

namespace KwtSMS\Laravel\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use KwtSMS\Laravel\Http\Requests\Admin\SettingsUpdateRequest;
use KwtSMS\Laravel\Models\KwtSmsSetting;
use KwtSMS\Laravel\Services\BalanceService;

class SettingsController extends Controller
{
    public function __construct(private readonly BalanceService $balanceService) {}

    public function index(): View
    {
        $senderids = KwtSmsSetting::get('senderids', []);
        $adminPhone = KwtSmsSetting::get('admin_phone', '');
        $lowBalanceThreshold = KwtSmsSetting::get('low_balance_threshold', 50);

        return view('kwtsms::admin.settings', compact('senderids', 'adminPhone', 'lowBalanceThreshold'));
    }

    public function update(SettingsUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (! empty($validated['admin_phone'])) {
            KwtSmsSetting::set('admin_phone', $validated['admin_phone']);
        }
        if (isset($validated['low_balance_threshold'])) {
            KwtSmsSetting::set('low_balance_threshold', (float) $validated['low_balance_threshold']);
        }

        return redirect()->route('kwtsms.settings')->with('success', __('kwtsms::kwtsms.settings_saved'));
    }

    public function connect(): JsonResponse
    {
        $result = $this->balanceService->syncFromApi();

        if (isset($result['error'])) {
            return response()->json(['success' => false, 'message' => $result['error']]);
        }

        return response()->json([
            'success' => true,
            'balance' => $result['available'],
            'message' => 'Connected successfully',
        ]);
    }
}
