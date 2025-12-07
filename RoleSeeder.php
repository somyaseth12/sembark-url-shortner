<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['id' => 1, 'name' => 'SuperAdmin'],
            ['id' => 2, 'name' => 'Admin'],
            ['id' => 3, 'name' => 'Member'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['id' => $role['id']], ['name' => $role['name']]);
        }
    }
}
