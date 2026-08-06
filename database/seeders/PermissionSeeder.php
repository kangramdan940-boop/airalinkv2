<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = [
            'dashboard' => ['view'],
            'transfer' => ['view', 'add', 'edit', 'delete'],
            'tunai' => ['view', 'add', 'edit', 'delete'],
            'kalkulator' => ['view'],
            'pengeluaran' => ['view', 'add', 'edit', 'delete'],
            'investor' => ['view', 'add', 'edit', 'delete'],
            'uang-cash' => ['view', 'add', 'edit', 'delete'],
            'uang-cash-monitoring' => ['view', 'add', 'edit', 'delete'],
            'transaksi-transfer' => ['view', 'add', 'edit', 'delete'],
            'transaksi-tarik-tunai' => ['view', 'add', 'edit', 'delete'],
            'master-pegawai' => ['view', 'add', 'edit', 'delete'],
            'konfigurasi-users' => ['view', 'add', 'edit', 'delete'],
            'konfigurasi-roles' => ['view', 'add', 'edit', 'delete'],
        ];

        $allPermissions = [];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $permName = "{$module}-{$action}";
                Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
                $allPermissions[] = $permName;
            }
        }

        // Create roles
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Admin gets all permissions
        $admin->syncPermissions($allPermissions);

        // User gets limited permissions
        $userPermissions = array_filter($allPermissions, function ($perm) {
            return !str_contains($perm, 'konfigurasi');
        });
        $user->syncPermissions($userPermissions);
    }
}
