<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:40',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'timezone' => ['required', 'timezone'],
            'theme_preference' => ['required', Rule::in(['light', 'dark', 'system'])],
            'preferred_funding_provider' => ['nullable', Rule::in(['paystack', 'kora', 'squad'])],
            'settings' => ['nullable', 'array'],
            'settings.security_alerts' => ['nullable', 'boolean'],
            'settings.monthly_reports' => ['nullable', 'boolean'],
            'settings.marketing_emails' => ['nullable', 'boolean'],
            'settings.login_with_google' => ['nullable', 'boolean'],
        ];
    }
}
