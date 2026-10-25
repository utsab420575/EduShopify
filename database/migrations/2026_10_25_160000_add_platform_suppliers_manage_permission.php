<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\PermissionRegistrar;

/**
 * platform.suppliers.manage — admin edits a supplier's Business Profile and
 * Catalog listings (ManageSupplierAccount). Created here as well as in
 * PermissionSeeder so `php artisan migrate` alone enables it on deploy,
 * without re-running RoleSeeder (whose syncPermissions would reset any
 * role edits made in the admin UI). Only adds; never removes anything.
 */
return new class extends Migration
{
    private const NAME = 'platform.suppliers.manage';

    private const ROLES = ['super_admin', 'admin', 'admin_staff'];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::updateOrCreate(
            ['name' => self::NAME, 'guard_name' => 'web'],
            [
                'display_name' => "Edit a supplier's business profile and products on their behalf",
                'group_name' => 'Platform · Accounts',
                'capability_scope' => 'platform',
                'is_sensitive' => true,
                'is_owner_only' => false,
                'is_assignable' => true,
                'is_active' => true,
            ]
        );

        Role::whereIn('name', self::ROLES)->whereNull('account_id')->where('guard_name', 'web')->get()
            ->each(fn (Role $role) => $role->hasPermissionTo($permission) || $role->givePermissionTo($permission));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::where('name', self::NAME)->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
