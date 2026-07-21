<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceProvider;
use Illuminate\Database\Seeder;

class BookingDemoSeeder extends Seeder
{
    public function run(): void
    {
        $provider = ServiceProvider::where('phone', '9876543210')->first();
        $service = Service::first();

        if (! $provider || ! $service) {
            return;
        }

        $customer = Customer::firstOrCreate(
            ['phone' => '9988776655'],
            ['name' => 'Rajesh Kumar', 'address' => 'House 42, Rajeev Nagar, Delhi', 'pincode' => '110042']
        );

        Booking::updateOrCreate(
            ['booking_number' => 'ARDEMO001'],
            [
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'provider_id' => $provider->id,
                'customer_name' => 'Rajesh Kumar',
                'customer_phone' => '9988776655',
                'customer_address' => 'House 42, Rajeev Nagar, North West Delhi',
                'pincode' => '110042',
                'tank_type' => 'Overhead',
                'tank_size' => '1000L',
                'scheduled_date' => today(),
                'scheduled_time' => '10:00',
                'status' => BookingStatus::Assigned,
                'amount' => 499,
                'assigned_at' => now(),
            ]
        );

        Booking::updateOrCreate(
            ['booking_number' => 'ARDEMO002'],
            [
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'provider_id' => $provider->id,
                'customer_name' => 'Priya Singh',
                'customer_phone' => '9876501234',
                'customer_address' => 'Flat 12, Sector 7, Rohini, Delhi',
                'pincode' => '110085',
                'tank_type' => 'Underground',
                'tank_size' => '1500L',
                'scheduled_date' => today()->addDay(),
                'scheduled_time' => '14:00',
                'status' => BookingStatus::Assigned,
                'amount' => 799,
                'assigned_at' => now(),
            ]
        );
    }
}
