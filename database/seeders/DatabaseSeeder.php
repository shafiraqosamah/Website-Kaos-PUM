<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(MasterDataSeeder::class);

        $seedUsers = [
            ['name' => 'Admin Utama', 'email' => 'admin@panjium.com', 'role' => User::ROLE_ADMIN],
            ['name' => 'Tim Keuangan', 'email' => 'finance@panjium.com', 'role' => User::ROLE_FINANCE],
            ['name' => 'Tim Produksi', 'email' => 'production@panjium.com', 'role' => User::ROLE_PRODUCTION],
            ['name' => 'Owner', 'email' => 'owner@panjium.com', 'role' => User::ROLE_OWNER],
            ['name' => 'Manager', 'email' => 'manager@panjium.com', 'role' => User::ROLE_MANAGER],
            ['name' => 'Pelanggan Demo', 'email' => 'customer@panjium.com', 'role' => User::ROLE_CUSTOMER],
        ];

        foreach ($seedUsers as $seedUser) {
            User::updateOrCreate(
                ['email' => $seedUser['email']],
                [
                    'name' => $seedUser['name'],
                    'role' => $seedUser['role'],
                    'phone' => '081200000000',
                    'password' => Hash::make('password123'),
                ]
            );
        }
    }
}
