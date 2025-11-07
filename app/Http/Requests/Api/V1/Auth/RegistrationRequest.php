<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\DashboardFormRequest;

class RegistrationRequest extends DashboardFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['bail', 'required', 'string', 'email', 'unique:users,email'],
            'password' => ['bail', 'required', 'string', 'min:8'],
            'first_name' => ['bail', 'required', 'string', 'max:100'],
            'last_name' => ['bail', 'required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Parameter email wajib diisi.',
            'email.email' => 'Parameter email tidak sesuai format.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Parameter password wajib diisi.',
            'password.min' => 'Password minimal harus terdiri dari :min karakter.',
            'first_name.required' => 'Parameter nama depan wajib diisi.',
            'last_name.required' => 'Parameter nama belakang wajib diisi.',
        ];
    }
}
