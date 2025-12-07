<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure Admin role exists
        $role = Role::firstOrCreate(['name' => 'Admin']);

        // Create Admin user
        User::firstOrCreate(
            ['email' => 'admin1@tech-innovators.com'],
            [
                'name' => 'Admin (Tech Innovators)',
                'password' => Hash::make('password'),
                'role_id' => $role->id,
                'company_id' => 1,
            ]
        );
    }
}
