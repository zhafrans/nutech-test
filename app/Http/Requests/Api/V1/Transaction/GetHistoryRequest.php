<?php

namespace App\Http\Requests\Api\V1\Transaction;

use App\Http\Requests\DashboardFormRequest;

class GetHistoryRequest extends DashboardFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'offset' => ['required', 'integer'],
            'limit' => ['required', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'offset.required' => 'Parameter offset wajib diisi.',
            'offset.integer' => 'Parameter offset harus berupa angka.',
            'limit.required' => 'Parameter limit wajib diisi.',
            'limit.integer' => 'Parameter limit harus berupa angka.',
        ];
    }
}
