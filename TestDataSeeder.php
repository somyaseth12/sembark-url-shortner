<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create test companies
        $company1 = Company::firstOrCreate(
            ['name' => 'Tech Innovators'],
            ['domain' => 'tech-innovators.local']
        );

        $company2 = Company::firstOrCreate(
            ['name' => 'Digital Solutions'],
            ['domain' => 'digital-solutions.local']
        );

        // Ensure roles exist
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $memberRole = Role::firstOrCreate(['name' => 'Member']);

        // Create Admin users for each company
        User::firstOrCreate(
            ['email' => 'admin1@tech-innovators.com'],
            [
                'name' => 'Admin (Tech Innovators)',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
                'company_id' => $company1->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin2@digital-solutions.com'],
            [
                'name' => 'Admin (Digital Solutions)',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
                'company_id' => $company2->id,
            ]
        );

        // Create Member users for Company 1
        User::firstOrCreate(
            ['email' => 'member1@tech-innovators.com'],
            [
                'name' => 'Member (Tech Innovators)',
                'password' => Hash::make('password'),
                'role_id' => $memberRole->id,
                'company_id' => $company1->id,
            ]
        );

        User::firstOrCreate(
    ['email' => 'member2@tech-innovators.com'],
    [
        'name' => 'Member Two',
        'password' => Hash::make('password'),
        'role_id' => $memberRole->id,
        'company_id' => $company1->id,
    ]
);
  // Create Member users for Company 2
        User::firstOrCreate(
            ['email' => 'member3@digital-solutions.com'],
            [
                'name' => 'Member (Digital Solutions)',
                'password' => Hash::make('password'),
                'role_id' => $memberRole->id,
                'company_id' => $company2->id,
            ]
        );
    }
}