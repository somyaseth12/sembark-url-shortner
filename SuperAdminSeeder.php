<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Ensure SuperAdmin role exists
        $role = Role::firstOrCreate(['name' => 'SuperAdmin']);

        // Create SuperAdmin user
        User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'SuperAdmin',
                'password' => Hash::make('password'), // Change for production
                'role_id' => $role->id,
            ]
        );
    }
}
