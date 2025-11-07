<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\DashboardFormRequest;

class UpdateProfileRequest extends DashboardFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Parameter first_name wajib diisi.',
            'first_name.string' => 'Parameter first_name harus berupa teks.',
            'first_name.max' => 'Parameter first_name tidak boleh lebih dari 100 karakter.',
            'last_name.required' => 'Parameter last_name wajib diisi.',
            'last_name.string' => 'Parameter last_name harus berupa teks.',
            'last_name.max' => 'Parameter last_name tidak boleh lebih dari 100 karakter.',
        ];
    }
}
