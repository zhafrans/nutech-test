<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ResponseCode;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ServiceProductController extends Controller
{
    public function list()
    {
        $services = DB::table('service_products')
            ->select([
                'service_code',
                'service_name',
                'service_icon',
                'service_tariff',
            ])
            ->orderBy('service_name')
            ->get();

        $items = $services->map(function ($item) {
            return (object) [
                'service_code' => $item->service_code,
                'service_name' => $item->service_name,
                'service_icon' => $item->service_icon,
                'service_tariff' => $item->service_tariff,
            ];
        });

        return ResponseHelper::generate(ResponseCode::Ok, [
            'items' => $items
        ]);
    }
}
