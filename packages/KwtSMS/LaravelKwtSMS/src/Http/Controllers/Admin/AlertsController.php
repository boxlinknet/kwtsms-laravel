<?php

namespace KwtSMS\Laravel\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use KwtSMS\Laravel\Http\Requests\Admin\AlertsUpdateRequest;
use KwtSMS\Laravel\Models\KwtSmsSetting;

class AlertsController extends Controller
{
    public function index(): View
    {
        $alerts = KwtSmsSetting::get('admin_alerts', []);
        $adminPhone = KwtSmsSetting::get('admin_phone', '');

        return view('kwtsms::admin.alerts', compact('alerts', 'adminPhone'));
    }

    public function update(AlertsUpdateRequest $request): RedirectResponse
    {
        KwtSmsSetting::set('admin_alerts', $request->validated()['alerts'] ?? []);

        return redirect()->route('kwtsms.alerts')->with('success', __('kwtsms::kwtsms.alerts_saved'));
    }
}
