<?php

namespace Database\Seeders;

use App\Models\Users;
use App\Enums\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $users = [
            [
                'name' => 'Super Administrator',
                'username' => 'superadmin',
                'role' => Role::SUPER_ADMIN,
                'password' => $password,
            ],
            [
                'name' => 'Staff Sales',
                'username' => 'sales',
                'role' => Role::SALES,
                'password' => $password,
            ],
            [
                'name' => 'Staff Purchasing',
                'username' => 'purchase',
                'role' => Role::PURCHASE,
                'password' => $password,
            ],
            [
                'name' => 'General Manager',
                'username' => 'manager',
                'role' => Role::MANAGER,
                'password' => $password,
            ],
        ];

        foreach ($users as $user) {
            Users::create($user);
        }
    }
}
