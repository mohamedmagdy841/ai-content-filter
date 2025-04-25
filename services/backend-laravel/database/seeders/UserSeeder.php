<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'api']);

        $admin = User::firstOrCreate([
            'email' => 'admin@gmail.com',
        ], [
            'name' => 'Admin User',
            'password' => Hash::make('123456789'),
        ]);
        $admin->assignRole($adminRole);

        $user = User::firstOrCreate([
            'email' => 'user@gmail.com',
        ], [
            'name' => 'Normal User',
            'password' => Hash::make('123456789'),
        ]);
        $user->assignRole($userRole);
    }
}
