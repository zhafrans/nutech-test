<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\DashboardFormRequest;

class LoginRequest extends DashboardFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['bail', 'required', 'string', 'email'],
            'password' => ['bail', 'required'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Parameter email wajib diisi.',
            'email.string' => 'Parameter email harus berupa teks.',
            'email.email' => 'Format email tidak sesuai.',
            'password.required' => 'Parameter password wajib diisi.',
        ];
    }
}
