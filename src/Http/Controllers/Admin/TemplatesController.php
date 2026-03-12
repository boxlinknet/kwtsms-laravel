<?php

namespace KwtSMS\Laravel\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use KwtSMS\Laravel\Http\Requests\Admin\TemplateRequest;
use KwtSMS\Laravel\Models\KwtSmsTemplate;

class TemplatesController extends Controller
{
    public function index(): View
    {
        $templates = KwtSmsTemplate::query()->orderBy('event_type')->orderBy('locale')->get();

        return view('kwtsms::admin.templates', compact('templates'));
    }

    public function create(): View
    {
        return view('kwtsms::admin.templates-form', ['template' => null]);
    }

    public function store(TemplateRequest $request): RedirectResponse
    {
        KwtSmsTemplate::create($request->validated());

        return redirect()->route('kwtsms.templates.index')->with('success', __('kwtsms::kwtsms.template_created'));
    }

    public function edit(KwtSmsTemplate $template): View
    {
        return view('kwtsms::admin.templates-form', compact('template'));
    }

    public function update(TemplateRequest $request, KwtSmsTemplate $template): RedirectResponse
    {
        $template->update($request->validated());

        return redirect()->route('kwtsms.templates.index')->with('success', __('kwtsms::kwtsms.template_updated'));
    }

    public function destroy(KwtSmsTemplate $template): RedirectResponse
    {
        $template->delete();

        return redirect()->route('kwtsms.templates.index')->with('success', __('kwtsms::kwtsms.template_deleted'));
    }
}
