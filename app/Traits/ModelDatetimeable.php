<?php

namespace App\Traits;

use App\Helpers\DateHelper;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait ModelDatetimeable
{
    protected function createdAt(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => DateHelper::datetime($value));
    }

    protected function updatedAt(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => DateHelper::datetime($value));
    }
}
