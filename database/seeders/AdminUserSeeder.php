<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@arwatertankcleaners.in'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => UserRole::SuperAdmin,
                'phone' => '9876543210',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'manager@arwatertankcleaners.in'],
            [
                'name' => 'Manager',
                'password' => Hash::make('password'),
                'role' => UserRole::Manager,
                'phone' => '9876543211',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
