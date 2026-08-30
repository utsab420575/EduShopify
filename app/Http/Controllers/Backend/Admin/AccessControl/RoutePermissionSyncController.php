<?php

namespace App\Http\Controllers\Backend\Admin\AccessControl;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\RbacAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\PermissionRegistrar;

class RoutePermissionSyncController extends Controller
{
    /**
     * Display an interactive, grouped matrix of registered routes, permissions, and role assignment state.
     */
    public function index(Request $request): View
    {
        $selectedScope = $request->query('scope', 'platform');
        $search = strtolower(trim($request->query('search', '')));
        $statusFilter = $request->query('status', 'all'); // all, existing, missing
        $selectedRoleId = $request->query('role_id');

        // 1. Fetch all existing permissions indexed by name
        $existingPermissions = Permission::all()->keyBy('name');

        // 2. Fetch all system roles with their active permissions
        $roles = Role::with('permissions')
            ->whereNull('account_id')
            ->where('is_active', true)
            ->orderBy('display_name')
            ->get();

        $selectedRole = $roles->firstWhere('id', (int)$selectedRoleId) ?? $roles->firstWhere('name', 'admin') ?? $roles->first();
        $rolePermissionsMap = $roles->mapWithKeys(fn ($r) => [
            $r->id => $r->permissions->pluck('name')->toArray()
        ]);

        // 3. Inspect Laravel's compiled route collection
        $scannedRoutes = [];
        $routes = Route::getRoutes()->getRoutes();

        foreach ($routes as $route) {
            $name = $route->getName();
            if (! $name) {
                continue;
            }

            // Exclude non-portal framework / package routes
            if (Str::startsWith($name, ['sanctum.', 'ignition.', 'livewire.', '_debugbar.', 'filament.'])) {
                continue;
            }

            // Determine Scope
            $scope = null;
            if (Str::startsWith($name, 'admin.')) {
                $scope = 'platform';
            } elseif (Str::startsWith($name, 'supplier.')) {
                $scope = 'supplier';
            } elseif (Str::startsWith($name, 'buyer.')) {
                $scope = 'buyer';
            } else {
                continue; // Skip public/guest routes
            }

            // Filter by selected tab scope if not 'all'
            if ($selectedScope !== 'all' && $scope !== $selectedScope) {
                continue;
            }

            $methods = array_diff($route->methods(), ['HEAD']);
            $httpMethod = implode('|', $methods);
            $uri = $route->uri();

            // Derive Group Name and Suggested Permission
            $group = $this->deriveGroupName($name);
            $suggestedPermission = $this->deriveSuggestedPermission($name, $scope);
            $displayName = $this->deriveDisplayName($name, $suggestedPermission);

            // Check existence in DB
            $isExisting = $existingPermissions->has($suggestedPermission) || $existingPermissions->has($name);
            $dbPermission = $existingPermissions->get($suggestedPermission) ?? $existingPermissions->get($name);

            // Filter by status if specified
            if ($statusFilter === 'existing' && ! $isExisting) {
                continue;
            }
            if ($statusFilter === 'missing' && $isExisting) {
                continue;
            }

            // Filter by search keyword
            if ($search !== '') {
                $searchable = strtolower($name . ' ' . $uri . ' ' . $suggestedPermission . ' ' . $group);
                if (! str_contains($searchable, $search)) {
                    continue;
                }
            }

            $scannedRoutes[] = [
                'route_name'           => $name,
                'uri'                  => $uri,
                'methods'              => $methods,
                'http_method'          => $httpMethod,
                'scope'                => $scope,
                'group'                => $group,
                'suggested_permission' => $suggestedPermission,
                'display_name'         => $displayName,
                'is_existing'          => $isExisting,
                'db_permission'        => $dbPermission,
            ];
        }

        // Sort routes by group and route name
        usort($scannedRoutes, fn ($a, $b) => strcmp($a['group'] . $a['route_name'], $b['group'] . $b['route_name']));

        // Group routes by functional category
        $groupedRoutes = collect($scannedRoutes)->groupBy('group');

        // Metrics for summary cards
        $totalCount = count($scannedRoutes);
        $existingCount = count(array_filter($scannedRoutes, fn ($r) => $r['is_existing']));
        $missingCount = $totalCount - $existingCount;

        return view('backend.admin.access-control.route-permissions.index', [
            'groupedRoutes'      => $groupedRoutes,
            'routes'             => $scannedRoutes,
            'roles'              => $roles,
            'selectedRole'       => $selectedRole,
            'rolePermissionsMap' => $rolePermissionsMap,
            'selectedScope'      => $selectedScope,
            'search'             => $search,
            'statusFilter'       => $statusFilter,
            'totalCount'         => $totalCount,
            'existingCount'      => $existingCount,
            'missingCount'       => $missingCount,
        ]);
    }

    /**
     * Create selected missing permissions in the database (without modifying role assignments).
     */
    public function createPermissions(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*.name' => ['required', 'string', 'max:120'],
            'permissions.*.display_name' => ['required', 'string', 'max:150'],
            'permissions.*.group_name' => ['required', 'string', 'max:100'],
            'permissions.*.scope' => ['required', 'string', 'in:platform,supplier,buyer,common,both'],
        ]);

        $createdCount = 0;
        foreach ($validated['permissions'] as $item) {
            $perm = Permission::firstOrCreate(
                ['name' => $item['name'], 'guard_name' => 'web'],
                [
                    'display_name'     => $item['display_name'],
                    'group_name'       => $item['group_name'],
                    'capability_scope' => $item['scope'],
                    'is_active'        => true,
                    'is_assignable'    => true,
                ]
            );

            if ($perm->wasRecentlyCreated) {
                $createdCount++;
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', "{$createdCount} new permission(s) created in database successfully.");
    }

    /**
     * Explicitly sync all selected permissions to a specific role with updateOrCreate safety.
     */
    public function assignToRole(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role_id'     => ['required', 'exists:roles,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*.name' => ['required', 'string'],
            'permissions.*.display_name' => ['nullable', 'string'],
            'permissions.*.group_name' => ['nullable', 'string'],
            'permissions.*.scope' => ['nullable', 'string'],
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $oldPermissions = $role->permissions->pluck('name')->toArray();

        // 1. Ensure every checked permission exists in database (create if missing)
        $selectedNames = [];
        if (! empty($validated['permissions'])) {
            foreach ($validated['permissions'] as $item) {
                $perm = Permission::firstOrCreate(
                    ['name' => $item['name'], 'guard_name' => 'web'],
                    [
                        'display_name'     => $item['display_name'] ?? ucwords(str_replace(['.', '_', '-'], ' ', $item['name'])),
                        'group_name'       => $item['group_name'] ?? 'General',
                        'capability_scope' => $item['scope'] ?? $role->capability_scope,
                        'is_active'        => true,
                        'is_assignable'    => true,
                    ]
                );
                $selectedNames[] = $perm->name;
            }
        }

        // 2. Sync the role's permissions
        $role->syncPermissions($selectedNames);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 3. Log RBAC audit trail
        RbacAuditLogger::logPermissionsSynced($role, $oldPermissions, $selectedNames);

        return redirect()->route('admin.access-control.route-permissions.index', [
            'scope'   => $request->input('scope', 'platform'),
            'role_id' => $role->id,
        ])->with('success', "Permissions for role '{$role->display_name}' updated successfully (" . count($selectedNames) . " active permissions).");
    }

    /**
     * Derive functional Group Name from route name.
     */
    private function deriveGroupName(string $routeName): string
    {
        $parts = explode('.', $routeName);
        $section = $parts[1] ?? 'general';

        return match ($section) {
            'users', 'accounts', 'buyers', 'suppliers', 'account-members', 'capabilities', 'conversions', 'closures' => 'Users & Accounts',
            'catalog', 'categories', 'attributes', 'brands', 'units', 'listings' => 'Catalog & Listings',
            'procurement', 'rfqs', 'quotations', 'awards', 'purchase-orders', 'opportunities' => 'Procurement & Trade',
            'billing', 'subscriptions', 'plans', 'payments' => 'Billing & Finance',
            'communication', 'messages', 'notifications', 'tickets', 'contact-inquiries' => 'Communication & Support',
            'reviews', 'reports' => 'Moderation & Reviews',
            'access-control', 'roles', 'permissions', 'roles-in-permission', 'user-roles', 'audit-logs', 'route-permissions' => 'Roles & Permission',
            'system', 'settings', 'theme', 'audit', 'logs', 'backups', 'geography', 'currencies' => 'System & Settings',
            'profile', 'locations', 'company', 'documents', 'service-areas', 'business-hours', 'gallery', 'exhibitions' => 'Business Profile',
            'saved-items' => 'Saved Items',
            'ownership' => 'Ownership',
            default => ucwords(str_replace(['-', '_'], ' ', $section)),
        };
    }

    /**
     * Derive suggested standard permission slug.
     */
    private function deriveSuggestedPermission(string $routeName, string $scope): string
    {
        $prefix = match ($scope) {
            'platform' => 'platform.',
            'supplier' => 'supplier.',
            'buyer'    => 'buyer.',
            default    => '',
        };

        $parts = explode('.', $routeName);
        array_shift($parts); // Remove portal prefix (admin, supplier, buyer)

        $action = end($parts);
        $actionNormalized = match ($action) {
            'index', 'show' => 'view',
            'create', 'store' => 'create',
            'edit', 'update' => 'edit',
            'destroy' => 'delete',
            default => $action,
        };

        if (count($parts) > 1) {
            array_pop($parts);
            $resource = implode('.', $parts);
            return $prefix . $resource . '.' . $actionNormalized;
        }

        return $prefix . ($parts[0] ?? 'general') . '.' . $actionNormalized;
    }

    /**
     * Derive clean human-readable display name.
     */
    private function deriveDisplayName(string $routeName, string $permissionSlug): string
    {
        $parts = explode('.', $permissionSlug);
        $action = array_pop($parts);
        $resource = implode(' ', $parts);

        return ucwords($action . ' ' . str_replace(['_', '-'], ' ', $resource));
    }
}
