<?php

namespace KwtSMS\Laravel\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SettingsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'admin_phone' => ['nullable', 'string', 'max:30'],
            'low_balance_threshold' => ['nullable', 'numeric', 'min:0'],
            'rate_limit_per_phone' => ['nullable', 'integer', 'min:1', 'max:100'],
            'rate_limit_per_ip' => ['nullable', 'integer', 'min:1', 'max:500'],
        ];
    }
}
