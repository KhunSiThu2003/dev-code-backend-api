<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users'],
            'type'  => ['nullable', 'string', 'in:registration,forgot_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email is required.',
            'email.email'    => 'Invalid email format.',
            'email.exists'   => 'User not found.',
            'type.in'        => 'Type must be registration or forgot_password.',
        ];
    }
}
