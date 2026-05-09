<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Reset permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Wipe only the users table (and pivot tables that depend on it)
        DB::table('model_has_roles')->delete();
        DB::table('model_has_permissions')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $adminRole  = Role::where('name', 'admin')->where('guard_name', 'api')->first();
        $driverRole = Role::where('name', 'driver')->where('guard_name', 'api')->first();

        // Create admin user
        $admin = User::create([
            'name'      => 'Admin User',
            'email'     => 'admin@gmail.com',
            'password'  => 'password',   // auto-hashed by cast
            'phone'     => '0812345678',
            'role'      => 'admin',
            'is_active' => true,
        ]);
        if ($adminRole) $admin->syncRoles([$adminRole]);

        // Create driver user
        $driver = User::create([
            'name'      => 'Driver User',
            'email'     => 'driver@gmail.com',
            'password'  => 'password',
            'phone'     => '0898765432',
            'role'      => 'driver',
            'is_active' => true,
        ]);
        if ($driverRole) $driver->syncRoles([$driverRole]);

        $this->command->info('✅ Users table refreshed.');
        $this->command->table(
            ['ID', 'Name', 'Email', 'Role', 'Password'],
            [
                [$admin->id,  $admin->name,  $admin->email,  $admin->role,  'password'],
                [$driver->id, $driver->name, $driver->email, $driver->role, 'password'],
            ]
        );
    }
}
