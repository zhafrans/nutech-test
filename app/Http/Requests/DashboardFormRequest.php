<?php

namespace App\Http\Requests;

use App\Exceptions\DashboardValidationException;
use App\Traits\FormRequestRulable;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class DashboardFormRequest extends FormRequest
{
    use FormRequestRulable;

    protected function failedValidation(Validator $validator)
    {
        throw new DashboardValidationException($validator);
    }
}
