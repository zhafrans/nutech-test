<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\ServiceProduct;

class ServiceProductSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'service_code' => 'PAJAK',
                'service_name' => 'Pajak PBB',
                'service_icon' => 'https://picsum.photos/64?random=1',
                'service_tariff' => 40000.00,
            ],
            [
                'service_code' => 'PLN',
                'service_name' => 'Listrik',
                'service_icon' => 'https://picsum.photos/64?random=2',
                'service_tariff' => 10000.00,
            ],
            [
                'service_code' => 'PDAM',
                'service_name' => 'PDAM Berlangganan',
                'service_icon' => 'https://picsum.photos/64?random=3',
                'service_tariff' => 40000.00,
            ],
            [
                'service_code' => 'PULSA',
                'service_name' => 'Pulsa',
                'service_icon' => 'https://picsum.photos/64?random=4',
                'service_tariff' => 40000.00,
            ],
        ];

        foreach ($services as $service) {
            ServiceProduct::create($service);
        }
    }
}
