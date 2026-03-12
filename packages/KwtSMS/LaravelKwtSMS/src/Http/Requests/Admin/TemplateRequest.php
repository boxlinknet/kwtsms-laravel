<?php

namespace KwtSMS\Laravel\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'event_type' => ['required', 'string', 'max:60'],
            'locale' => ['required', 'in:en,ar'],
            'body' => ['required', 'string'],
            'is_active' => ['boolean'],
        ];
    }
}
