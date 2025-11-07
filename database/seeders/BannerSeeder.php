<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Banner;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'banner_name' => 'Banner 1',
                'banner_image' => 'https://picsum.photos/64?random=5',
                'description' => 'Lerem Ipsum Dolor sit amet',
            ],
            [
                'banner_name' => 'Banner 2',
                'banner_image' => 'https://picsum.photos/64?random=6',
                'description' => 'Lerem Ipsum Dolor sit amet',
            ],
            [
                'banner_name' => 'Banner 3',
                'banner_image' => 'https://picsum.photos/64?random=7',
                'description' => 'Lerem Ipsum Dolor sit amet',
            ],
            [
                'banner_name' => 'Banner 4',
                'banner_image' => 'https://picsum.photos/64?random=8',
                'description' => 'Lerem Ipsum Dolor sit amet',
            ],
            [
                'banner_name' => 'Banner 5',
                'banner_image' => 'https://picsum.photos/64?random=9',
                'description' => 'Lerem Ipsum Dolor sit amet',
            ],
            [
                'banner_name' => 'Banner 6',
                'banner_image' => 'https://picsum.photos/64?random=10',
                'description' => 'Lerem Ipsum Dolor sit amet',
            ],
        ];

        foreach ($banners as $banner) {
            Banner::create($banner);
        }
    }
}
