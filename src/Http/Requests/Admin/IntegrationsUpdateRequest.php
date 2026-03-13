<?php

namespace KwtSMS\Laravel\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class IntegrationsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'integrations' => ['nullable', 'array'],
            'integrations.*' => ['boolean'],
        ];
    }
}
