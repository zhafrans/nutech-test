<?php

namespace App\Http\Requests\Api\V1\Transaction;

use App\Http\Requests\DashboardFormRequest;

class TopupRequest extends DashboardFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'top_up_amount' => ['required', 'numeric', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'top_up_amount.required' => 'Nominal top up wajib diisi.',
            'top_up_amount.numeric'  => 'Nominal top up harus berupa angka.',
        ];
    }
}
