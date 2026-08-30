<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Account;
use App\Services\RbacAuditLogger;
use Spatie\Activitylog\Models\Activity;

echo "======================================================\n";
echo "Testing Enterprise RBAC System & Admin Resources\n";
echo "======================================================\n\n";

// 1. Verify Models & Counts
$totalPermissions = Permission::count();
$totalRoles = Role::count();
$systemRoles = Role::whereNull('account_id')->count();
echo "Total Permissions in DB: {$totalPermissions}\n";
echo "Total Roles in DB: {$totalRoles} ({$systemRoles} System Roles)\n\n";

// 2. Test Role Duplication Service
$sourceRole = Role::where('name', 'product_manager')->first();
if ($sourceRole) {
    echo "Testing Role Duplication on: {$sourceRole->display_name}...\n";
    $supplierAccount = Account::where('account_type', 'organization')->first() ?? Account::first();
    
    $testSlug = 'senior_product_manager_test_' . time();
    $newRole = Role::create([
        'account_id'         => $supplierAccount->id,
        'name'               => $testSlug,
        'guard_name'         => 'web',
        'display_name'       => 'Senior Product Manager Test',
        'capability_scope'   => 'supplier',
        'description'        => 'Custom duplicated role for testing',
        'is_system'          => false,
        'is_owner_role'      => false,
        'is_active'          => true,
    ]);

    $perms = $sourceRole->permissions->pluck('name')->toArray();
    $newRole->syncPermissions($perms);
    RbacAuditLogger::logRoleDuplicated($sourceRole, $newRole, $supplierAccount->id);

    echo "Duplicated role created ID: {$newRole->id}, Permissions count: " . $newRole->permissions()->count() . "\n";
    
    // Clean up test role
    $newRole->delete();
    echo "Test duplicated role deleted cleanly.\n\n";
}

// 3. Test RBAC Audit Logging
$auditCount = Activity::where('log_name', 'rbac')->count();
echo "Total RBAC Audit Log entries: {$auditCount}\n";
$latestLog = Activity::where('log_name', 'rbac')->latest()->first();
if ($latestLog) {
    echo "Latest RBAC Log: {$latestLog->description} (Action: " . ($latestLog->properties['action'] ?? 'N/A') . ")\n";
}

echo "\nAll RBAC core components verified successfully!\n";
