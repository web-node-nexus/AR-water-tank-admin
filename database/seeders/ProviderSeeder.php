<?php

namespace Database\Seeders;

use App\Models\ServiceProvider;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        $zone = Zone::first();

        ServiceProvider::updateOrCreate(
            ['phone' => '9876543210'],
            [
                'name' => 'Rahul Sharma',
                'email' => 'rahul@arwatertankcleaners.in',
                'password' => 'Provider@123',
                'zone_id' => $zone?->id,
                'service_area' => 'North West Delhi',
                'availability_status' => 'available',
                'rating_avg' => 4.5,
                'total_jobs' => 0,
                'total_earnings' => 0,
                'is_active' => true,
            ]
        );

        ServiceProvider::updateOrCreate(
            ['phone' => '9876543211'],
            [
                'name' => 'Amit Kumar',
                'email' => 'amit@arwatertankcleaners.in',
                'password' => 'Provider@123',
                'zone_id' => $zone?->id,
                'service_area' => 'North West Delhi',
                'availability_status' => 'available',
                'rating_avg' => 4.8,
                'total_jobs' => 0,
                'total_earnings' => 0,
                'is_active' => true,
            ]
        );
    }
}
