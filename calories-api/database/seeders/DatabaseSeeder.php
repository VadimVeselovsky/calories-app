<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $permissions = [
            'meals',
            'users'
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission
            ]);
        }

        // gets all permissions via Gate::before rule; see AuthServiceProvider
        $adminRole = Role::create(['name' => 'admin']);

        $adminPermissions = [
            'meals',
            'users'
        ];

        foreach ($adminPermissions as $permission) {
            $adminRole->givePermissionTo($permission);
        }

        $userRole = Role::create(['name' => 'user']);

        $userPermissions = [
            'meals',
        ];

        foreach ($userPermissions as $permission) {
            $userRole->givePermissionTo($permission);
        }

        $admin = \App\Models\User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin1234'),
        ]);

        $admin->assignRole('admin');

        $user = \App\Models\User::create([
            'name' => 'Simple User',
            'email' => 'user@example.com',
            'password' => Hash::make('user1234'),
        ]);

        $user->assignRole('user');
    }
}
