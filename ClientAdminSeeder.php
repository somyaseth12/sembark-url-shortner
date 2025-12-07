<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure ClientAdmin role exists
        $role = Role::firstOrCreate(['name' => 'ClientAdmin']);

        // Ensure company exists
        $company = Company::firstOrCreate(['name' => 'Tech Innovators']);

        // Create Client Admin user
        User::firstOrCreate(
            ['email' => 'admin1@tech-innovators.com'],
            [
                'name' => 'Admin (Tech Innovators)',
                'password' => Hash::make('password'),
                'role_id' => $role->id,
                'company_id' => $company->id,
            ]
        );
    }
}
