<?php

namespace App\Exceptions;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class DashboardValidationException extends ValidationException
{
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }
}
