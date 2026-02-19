<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Define all permissions grouped ────────────────────────
        $permissions = [

            // Dashboard
            'dashboard.read',
            // Project
            'project.read',
            'project.approve',
            'project.update',
            'project.create',
            'project.delete',
           
            // Setup,
            'role.create',
            'role.update',
            'role.read',
            'role.delete',

            // User
            'user.create',
            'user.update',
            'user.read',
            'user.delete',

           
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'api',
            ]);
        }

        $this->command->info('✅ Permissions created: ' . count($permissions));

       
        // ── Assign ALL permissions to super_admin ─────────────────
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->syncPermissions($permissions);
            $this->command->info('✅ All permissions assigned to super_admin');
        }

        // ── Assign permissions to admin ───────────────────────────
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->syncPermissions([
                'dashboard.read',
                'user.create',
                'user.update',
                'user.read',
                'user.delete',
                'role.read',
                'role.create',
                'role.update',
                'project.read',
                'project.approve',
                'project.update',
                'project.create',
                'project.delete',
            ]);
            $this->command->info('✅ Permissions assigned to admin');
        }  

       
        $this->command->info('🎉 PermissionSeeder completed successfully!');
    }
}
