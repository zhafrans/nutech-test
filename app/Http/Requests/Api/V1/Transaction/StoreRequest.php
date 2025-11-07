<?php

namespace App\Http\Requests\Api\V1\Transaction;

use App\Http\Requests\DashboardFormRequest;

class StoreRequest extends DashboardFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_code' => ['required', 'exists:service_products,service_code'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_code.required' => 'Service code wajib diisi.',
            'service_code.exists'   => 'Service code tidak ditemukan dalam sistem.',
        ];
    }
}
