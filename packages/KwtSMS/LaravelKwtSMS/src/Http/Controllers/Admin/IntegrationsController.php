<?php

namespace KwtSMS\Laravel\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use KwtSMS\Laravel\Models\KwtSmsSetting;

class IntegrationsController extends Controller
{
    public function index(): View
    {
        $integrations = KwtSmsSetting::get('integrations', []);

        return view('kwtsms::admin.integrations', compact('integrations'));
    }

    public function update(Request $request): RedirectResponse
    {
        $integrations = $request->input('integrations', []);
        KwtSmsSetting::set('integrations', $integrations);

        return redirect()->route('kwtsms.integrations')->with('success', __('kwtsms::kwtsms.integrations_saved'));
    }
}
