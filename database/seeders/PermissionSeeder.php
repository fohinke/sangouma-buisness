<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $perms = [
            'manage suppliers','manage products','manage purchases','manage sales','process payments','view reports','manage users','configure'
        ];
        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $employe = Role::firstOrCreate(['name' => 'employe']);
        $comptable = Role::firstOrCreate(['name' => 'comptable']);

        $admin->syncPermissions(Permission::all());
        $employe->syncPermissions(Permission::whereIn('name', ['manage sales','process payments','view reports'])->get());
        $comptable->syncPermissions(Permission::whereIn('name', ['process payments','view reports'])->get());

        // Utilisateurs démo
        $u1 = User::firstOrCreate(['email' => 'admin@example.com'], ['name' => 'Admin', 'password' => Hash::make('password')]);
        $u1->assignRole('admin');
        $u2 = User::firstOrCreate(['email' => 'employe@example.com'], ['name' => 'Employé', 'password' => Hash::make('password')]);
        $u2->assignRole('employe');
        $u3 = User::firstOrCreate(['email' => 'comptable@example.com'], ['name' => 'Comptable', 'password' => Hash::make('password')]);
        $u3->assignRole('comptable');
    }
}

