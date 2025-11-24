<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Roles
        $adminRole = Role::create(['name' => 'admin']);
        $managerRole = Role::create(['name' => 'manager']);
        $employeeRole = Role::create(['name' => 'employee']);
        $clientRole = Role::create(['name' => 'client']);

        // Create Permissions (Basic set for now)
        $permissions = [
            'manage users',
            'manage clients',
            'view all files',
            'assign files',
            'edit files',
            'upload documents',
            'view own files'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign Permissions to Roles
        $adminRole->givePermissionTo(Permission::all());
        $managerRole->givePermissionTo(['view all files', 'assign files', 'edit files', 'upload documents']);
        $employeeRole->givePermissionTo(['view own files', 'edit files', 'upload documents']);

        // Create Default Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@auditflow.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole($adminRole);

        // Create Default Manager
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@auditflow.com',
            'password' => Hash::make('password'),
        ]);
        $manager->assignRole($managerRole);

        // Create Default Employee
        $employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@auditflow.com',
            'password' => Hash::make('password'),
        ]);
        $employee->assignRole($employeeRole);
    }
}
