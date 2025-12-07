<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure Member role exists
        $role = Role::firstOrCreate(['name' => 'Member']);

        // Create Member user
       User::firstOrCreate(
        ['email' => 'member1@tech-innovators.com'],
        [
            'name' => 'Member (Tech Innovators)',
            'password' => Hash::make('password'),
            'role_id' => 4, // Member role
            'company_id' => 1,
        ]
);

    }
}
