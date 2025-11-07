<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ResponseCode;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BannerController extends Controller
{
    public function list()
    {
        $banners = DB::table('banners')
            ->select([
                'banner_name',
                'banner_image',
                'description',
            ])
            ->orderBy('banner_name')
            ->get();

        $items = $banners->map(function ($item) {
            return (object) [
                'banner_name' => $item->banner_name,
                'banner_image' => $item->banner_image,
                'description' => $item->description,
            ];
        });

        return ResponseHelper::generate(ResponseCode::Ok, [
            'items' => $items
        ]);
    }
}
