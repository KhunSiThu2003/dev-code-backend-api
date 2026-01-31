<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:8'],
            'role'     => ['required', 'string', 'in:admin,learner,instructor'],
            'agree_to_terms' => ['required', 'accepted'],
        ];
    }

    public function messages()
    {
        return [
            'name.required'     => 'Name is required.',
            'email.required'    => 'Email is required.',
            'password.required' => 'Password is required.',
            'password.min'      => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Passwords do not match.',
            'password_confirmation.required' => 'Password confirmation is required.',
            'password_confirmation.min' => 'Password confirmation must be at least 8 characters.',
            'role.required'     => 'Role is required.',
            'agree_to_terms.required' => 'You must agree to the terms and conditions.',
            'agree_to_terms.accepted' => 'You must accept the terms and conditions to register.',
        ];
    }
}
