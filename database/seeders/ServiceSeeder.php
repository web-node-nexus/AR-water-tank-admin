<?php

namespace Database\Seeders;

use App\Models\PricingSlab;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        Setting::set('company_name', 'AR Water Tank Cleaners', 'general');
        Setting::set('company_phone', '+91 9876543210', 'general');
        Setting::set('company_email', 'info@arwatertankcleaners.in', 'general');
        Setting::set('company_address', 'Plot No S-15,16 Rajeev Nagar, North West Delhi, Pincode - 110042', 'general');
        Setting::set('whatsapp_number', '+91 9876543210', 'general');

        Zone::updateOrCreate(
            ['code' => 'NWD'],
            [
                'name' => 'North West Delhi',
                'city' => 'Delhi',
                'pincodes' => ['110042', '110033', '110034', '110035'],
                'is_active' => true,
            ]
        );

        $services = [
            [
                'name' => 'Overhead Tank Cleaning (up to 1000L)',
                'category' => 'Overhead Water Tank',
                'base_price' => 599,
                'is_featured' => true,
                'sort_order' => 1,
                'slabs' => [
                    ['name' => 'Up to 1000L', 'min_capacity' => 0, 'max_capacity' => 1000, 'price' => 599, 'sale_price' => 499],
                ],
            ],
            [
                'name' => 'Overhead Tank Cleaning (1001L – 2000L)',
                'category' => 'Overhead Water Tank',
                'base_price' => 899,
                'is_featured' => true,
                'sort_order' => 2,
                'slabs' => [
                    ['name' => '1001L – 2000L', 'min_capacity' => 1001, 'max_capacity' => 2000, 'price' => 899, 'sale_price' => 799],
                ],
            ],
            [
                'name' => 'Underground Water Tank (upto 1500L)',
                'category' => 'Underground Water Tank',
                'base_price' => 899,
                'is_featured' => true,
                'sort_order' => 3,
                'slabs' => [
                    ['name' => 'Up to 1500L', 'min_capacity' => 0, 'max_capacity' => 1500, 'price' => 899, 'sale_price' => 799],
                ],
            ],
            [
                'name' => 'Underground Water Tank (1501L – 3000L)',
                'category' => 'Underground Water Tank',
                'base_price' => 1099,
                'is_featured' => true,
                'sort_order' => 4,
                'slabs' => [
                    ['name' => '1501L – 3000L', 'min_capacity' => 1501, 'max_capacity' => 3000, 'price' => 1099, 'sale_price' => 999],
                ],
            ],
            [
                'name' => 'Residential Water Tank Cleaning',
                'category' => 'Residential',
                'base_price' => 799,
                'is_featured' => false,
                'sort_order' => 5,
                'slabs' => [],
            ],
            [
                'name' => 'Commercial Water Tank Cleaning',
                'category' => 'Commercial',
                'base_price' => 1499,
                'is_featured' => false,
                'sort_order' => 6,
                'slabs' => [],
            ],
            [
                'name' => 'Industrial Water Tank Cleaning',
                'category' => 'Industrial',
                'base_price' => 2499,
                'is_featured' => false,
                'sort_order' => 7,
                'slabs' => [],
            ],
            [
                'name' => 'Cement Tank Cleaning',
                'category' => 'Cement Tank',
                'base_price' => 999,
                'is_featured' => false,
                'sort_order' => 8,
                'slabs' => [],
            ],
        ];

        foreach ($services as $data) {
            $slabs = $data['slabs'];
            unset($data['slabs']);

            $service = Service::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    ...$data,
                    'slug' => Str::slug($data['name']),
                    'description' => 'Professional water tank cleaning service by AR Cleaners.',
                    'is_active' => true,
                ]
            );

            foreach ($slabs as $slab) {
                PricingSlab::updateOrCreate(
                    ['service_id' => $service->id, 'name' => $slab['name']],
                    $slab
                );
            }
        }
    }
}
