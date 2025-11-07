<?php

namespace App\Helpers;

class NumberHelper
{
    public static function decimal(int|float $value, bool $shouldRounded = false): string
    {
        return number_format(
            num: $shouldRounded ? explode('.', (string) $value)[0] : $value,
            decimals: 2,
            thousands_separator: ''
        );
    }
}
