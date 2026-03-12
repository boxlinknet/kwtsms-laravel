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
        ];
    }
}
