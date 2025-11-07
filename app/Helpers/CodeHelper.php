<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use Carbon\Carbon;

class CodeHelper
{
    public static function generateUserCode(): string
    {
        $prefix = 'USR';
        $datetime = Carbon::now()->format('YmdHis');
        $random = mt_rand(100, 999);

        return $prefix . $datetime . $random;
    }

    public static function generateInvoiceCode(): string
    {
        $prefix = 'INV';
        $datetime = Carbon::now()->format('YmdHis');
        $random = mt_rand(100, 999);

        return $prefix . $datetime . $random;
    }

    public static function generateTopupInvoiceCode(): string
    {
        $prefix = 'TOPUP';
        $datetime = Carbon::now()->format('YmdHis');
        $random = mt_rand(100, 999);

        return $prefix . $datetime . $random;
    }
}
