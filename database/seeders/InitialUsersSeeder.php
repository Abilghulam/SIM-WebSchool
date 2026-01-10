<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InitialUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Admin sistem
        User::firstOrCreate(
            ['email' => 'admin@school.local'],
            [
                'name' => 'Admin Sistem',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role_label' => 'admin',
                'is_active' => true,
            ]
        );

        // Operator/TU
        User::firstOrCreate(
            ['email' => 'operator@school.local'],
            [
                'name' => 'Operator TU',
                'username' => 'operator',
                'password' => Hash::make('password'),
                'role_label' => 'operator',
                'is_active' => true,
            ]
        );

        // Pimpinan (opsional)
        User::firstOrCreate(
            ['email' => 'pimpinan@school.local'],
            [
                'name' => 'Pimpinan',
                'username' => 'pimpinan',
                'password' => Hash::make('password'),
                'role_label' => 'pimpinan',
                'is_active' => true,
            ]
        );
    }
}
