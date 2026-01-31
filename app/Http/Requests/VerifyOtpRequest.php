<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
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
            'otp_code' => ['required', 'numeric'],
            'email' => ['required', 'email', 'exists:users'],       
        ];
    }

    public function messages()
    {
        return [
            'otp_code.required' => 'OTP is required.',
            'otp_code.numeric' => 'OTP must be a number.',
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email format.',
            'email.exists' => 'User not found.',
        ];
    }
}
