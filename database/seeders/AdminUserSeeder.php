<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'], // prevent duplicates
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'), // choose a strong password
                'role' => 'Admin',
                // 'email_verified_at' => now(),
            ]
        );
    }
}
