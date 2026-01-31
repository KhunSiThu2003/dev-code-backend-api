<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password'      => ['required', 'string', 'current_password'],
            'password'             => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
            'password_confirmation' => ['required', 'string', 'min:8'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required'         => 'Current password is required.',
            'current_password.current_password'  => 'The current password is incorrect.',
            'password.required'                 => 'New password is required.',
            'password.min'                      => 'New password must be at least 8 characters.',
            'password.confirmed'                => 'New password confirmation does not match.',
            'password.different'                => 'New password must be different from current password.',
            'password_confirmation.required'    => 'Password confirmation is required.',
            'password_confirmation.min'         => 'Password confirmation must be at least 8 characters.',
        ];
    }
}
