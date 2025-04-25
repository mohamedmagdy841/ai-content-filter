<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Post permissions
            'create_post',
            'view_post',
            'update_own_post',
            'delete_own_post',
            'restore_own_post',
            'update_any_post',
            'delete_any_post',
            'restore_any_post',

            // Comment permissions
            'create_comment',
            'view_comment',
            'update_own_comment',
            'delete_own_comment',
            'restore_own_comment',
            'update_any_comment',
            'delete_any_comment',
            'restore_any_comment',

            // Moderation
            'moderate_content',

            // Admin
            'manage_users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }

        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'api']);
        $user->syncPermissions([
            'create_post',
            'view_post',
            'update_own_post',
            'delete_own_post',
            'restore_own_post',

            'create_comment',
            'view_comment',
            'update_own_comment',
            'delete_own_comment',
            'restore_own_comment',
        ]);

        $moderator = Role::firstOrCreate(['name' => 'moderator', 'guard_name' => 'api']);
        $moderator->syncPermissions([
            'create_post',
            'view_post',
            'update_own_post',
            'delete_own_post',
            'restore_own_post',
            'update_any_post',
            'delete_any_post',
            'restore_any_post',

            'create_comment',
            'view_comment',
            'update_own_comment',
            'delete_own_comment',
            'restore_own_comment',
            'update_any_comment',
            'delete_any_comment',
            'restore_any_comment',

            'moderate_content',
        ]);

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $admin->syncPermissions(Permission::all());
    }
}
