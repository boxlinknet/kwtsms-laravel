<?php

namespace KwtSMS\Laravel\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AlertsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'alerts' => ['nullable', 'array'],
            'alerts.*' => ['boolean'],
        ];
    }
}
