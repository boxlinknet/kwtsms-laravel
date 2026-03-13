<?php

namespace KwtSMS\Laravel\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $template = $this->route('template');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('kwtsms_templates', 'name')
                    ->where('locale', $this->input('locale'))
                    ->ignore($template),
            ],
            'event_type' => ['required', 'string', 'max:60'],
            'locale' => ['required', 'in:en,ar'],
            'body' => ['required', 'string', 'max:1600'],
            'is_active' => ['boolean'],
        ];
    }
}
