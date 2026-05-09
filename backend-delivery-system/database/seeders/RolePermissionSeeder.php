<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Define all permissions ────────────────────────────────────────
        $permissions = [
            // Orders
            'orders.view',
            'orders.create',
            'orders.update',
            'orders.assign',
            'orders.cancel',
            'orders.update_status',   // driver updates delivery status

            // Drivers
            'drivers.view',
            'drivers.create',
            'drivers.update_status',
            'drivers.view_locations',
            'drivers.update_own_location',  // driver self GPS update

            // Routes
            'routes.view',
            'routes.optimize',
            'routes.view_eta',

            // Reports
            'reports.view',
            'reports.generate',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'api']);
        }

        // ── Admin role — full access ──────────────────────────────────────
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $adminRole->syncPermissions([
            'orders.view',
            'orders.create',
            'orders.update',
            'orders.assign',
            'orders.cancel',
            'drivers.view',
            'drivers.create',
            'drivers.update_status',
            'drivers.view_locations',
            'routes.view',
            'routes.optimize',
            'routes.view_eta',
            'reports.view',
            'reports.generate',
        ]);

        // ── Driver role — limited access ──────────────────────────────────
        $driverRole = Role::firstOrCreate(['name' => 'driver', 'guard_name' => 'api']);
        $driverRole->syncPermissions([
            'orders.view',
            'orders.update_status',
            'drivers.update_own_location',
            'routes.view',
            'routes.view_eta',
        ]);

        // ── Seed test users and assign roles ─────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'      => 'Admin User',
                'password'  => 'password',
                'role'      => 'admin',
                'is_active' => true,
            ]
        );
        $admin->syncRoles([$adminRole]);

        $driver = User::firstOrCreate(
            ['email' => 'driver@gmail.com'],
            [
                'name'      => 'Driver User',
                'password'  => 'password',
                'role'      => 'driver',
                'is_active' => true,
            ]
        );
        $driver->syncRoles([$driverRole]);

        $this->command->info('✅ Roles and permissions seeded.');
        $this->command->table(
            ['Role', 'Permissions'],
            [
                ['admin',  implode(', ', $adminRole->permissions->pluck('name')->toArray())],
                ['driver', implode(', ', $driverRole->permissions->pluck('name')->toArray())],
            ]
        );
    }
}
