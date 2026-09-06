<?php $__env->startSection('title', 'Route Permission Discovery & Role Sync'); ?>
<?php $__env->startSection('breadcrumb', 'Roles & Permission / Route Discovery'); ?>

<?php $__env->startSection('body'); ?>

    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Route &amp; Permission Discovery</h1>
            <p class="text-sm text-gray-500 mt-1">Live inspection of Laravel registered route collection. Group-wise mapping, category selection, and instant role synchronization.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?php echo e(route('admin.access-control.permissions.index')); ?>" class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors inline-flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-arrow-left text-gray-400"></i> All Permissions
            </a>
            <a href="<?php echo e(route('admin.access-control.roles-in-permission.index')); ?>" class="btn-primary px-3.5 py-2 text-xs font-semibold rounded-xl inline-flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-table-cells"></i> Roles in Permission
            </a>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="p-4 mb-6 text-sm text-green-800 rounded-xl bg-green-50 border border-green-200 flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-green-600 text-base"></i>
            <div><?php echo e(session('success')); ?></div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div class="p-4 mb-6 text-sm text-red-800 rounded-xl bg-red-50 border border-red-200 flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-red-600 text-base"></i>
            <div><?php echo e(session('error')); ?></div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="p-4 mb-6 text-sm text-red-800 rounded-xl bg-red-50 border border-red-200">
            <div class="font-bold mb-1 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-red-600"></i> Please resolve the following errors:
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($err); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Scanned Routes</p>
                <p class="text-2xl font-extrabold text-gray-900 mt-1"><?php echo e($totalCount); ?></p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-route"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Existing in Database</p>
                <p class="text-2xl font-extrabold text-emerald-600 mt-1"><?php echo e($existingCount); ?></p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Unseeded in Database</p>
                <p class="text-2xl font-extrabold text-amber-600 mt-1"><?php echo e($missingCount); ?></p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>
    </div>

    
    <div class="flex items-center gap-2 border-b border-gray-200 mb-6 overflow-x-auto pb-2">
        <a href="<?php echo e(route('admin.access-control.route-permissions.index', ['scope' => 'platform', 'role_id' => $selectedRole?->id, 'status' => $statusFilter])); ?>"
           class="px-4 py-2 text-xs font-bold rounded-lg transition-all <?php echo e($selectedScope === 'platform' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'); ?>">
            <i class="fa-solid fa-shield-cat mr-1.5"></i> Platform Admin (admin.*)
        </a>
        <a href="<?php echo e(route('admin.access-control.route-permissions.index', ['scope' => 'supplier', 'role_id' => $selectedRole?->id, 'status' => $statusFilter])); ?>"
           class="px-4 py-2 text-xs font-bold rounded-lg transition-all <?php echo e($selectedScope === 'supplier' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'); ?>">
            <i class="fa-solid fa-boxes-packing mr-1.5"></i> Supplier Portal (supplier.*)
        </a>
        <a href="<?php echo e(route('admin.access-control.route-permissions.index', ['scope' => 'buyer', 'role_id' => $selectedRole?->id, 'status' => $statusFilter])); ?>"
           class="px-4 py-2 text-xs font-bold rounded-lg transition-all <?php echo e($selectedScope === 'buyer' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'); ?>">
            <i class="fa-solid fa-cart-shopping mr-1.5"></i> Buyer Portal (buyer.*)
        </a>
        <a href="<?php echo e(route('admin.access-control.route-permissions.index', ['scope' => 'all', 'role_id' => $selectedRole?->id, 'status' => $statusFilter])); ?>"
           class="px-4 py-2 text-xs font-bold rounded-lg transition-all <?php echo e($selectedScope === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'); ?>">
            <i class="fa-solid fa-list mr-1.5"></i> All Routes
        </a>
    </div>

    
    <div class="bg-white rounded-2xl border border-indigo-100 shadow-md p-5 mb-6 bg-gradient-to-r from-indigo-50/40 via-white to-purple-50/30">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            
            
            <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                        <i class="fa-solid fa-user-gear"></i>
                    </div>
                    <div>
                        <label for="role-selector" class="block text-xs font-bold text-gray-900">Manage Role Permissions:</label>
                        <p class="text-[11px] text-gray-500">Select a role to preview &amp; assign permissions</p>
                    </div>
                </div>

                <select id="role-selector" onchange="onRoleChanged(this.value)" class="focus-accent text-xs font-bold rounded-xl border border-indigo-200 px-3.5 py-2 bg-white text-indigo-900 shadow-sm min-w-[220px]">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($r->id); ?>" <?php if($selectedRole?->id === $r->id): echo 'selected'; endif; ?>>
                            <?php echo e($r->display_name ?? $r->name); ?> (<?php echo e(ucfirst($r->capability_scope)); ?>)
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>

            
            <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto justify-end">
                
                
                <label class="flex items-center gap-2 px-3.5 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 cursor-pointer text-xs font-bold text-gray-700 shadow-sm">
                    <input type="checkbox" id="master-toggle" onchange="toggleMasterAll(this.checked)" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                    <span>Permission All</span>
                </label>

                
                <button type="button" onclick="submitRolePermissions()" class="btn-primary px-4 py-2 text-xs font-bold rounded-xl inline-flex items-center gap-2 shadow-md">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Save Permissions for <strong id="btn-role-name"><?php echo e($selectedRole?->display_name ?? 'Role'); ?></strong></span>
                    <span id="selected-counter" class="ml-1 px-2 py-0.5 rounded-full bg-white/20 text-[11px]">0</span>
                </button>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($missingCount > 0): ?>
                    <button type="button" onclick="submitCreateMissing()" class="px-3.5 py-2 text-xs font-bold text-amber-800 bg-amber-100 hover:bg-amber-200 border border-amber-300 rounded-xl transition-all inline-flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-plus-circle"></i> Create Unseeded in DB (<span id="missing-counter">0</span>)
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div class="flex items-center justify-between pt-3 mt-3 border-t border-indigo-100/60 text-xs text-gray-500">
            <div class="flex items-center gap-4">
                <span class="font-medium">Currently viewing role: <strong class="text-indigo-700" id="current-role-badge"><?php echo e($selectedRole?->display_name); ?></strong></span>
                <span>&bull;</span>
                <button type="button" onclick="resetToRoleDefault()" class="text-indigo-600 hover:underline font-semibold">
                    <i class="fa-solid fa-rotate-left mr-1"></i> Reset to Role Defaults
                </button>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="selectOnlyMissing()" class="text-amber-700 hover:underline font-medium">
                    Select All Missing
                </button>
                <span>&bull;</span>
                <button type="button" onclick="clearAllSelections()" class="text-gray-500 hover:underline">
                    Deselect All
                </button>
            </div>
        </div>
    </div>

    
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <form method="GET" class="flex flex-wrap items-center gap-2 flex-1 max-w-md">
            <input type="hidden" name="scope" value="<?php echo e($selectedScope); ?>">
            <input type="hidden" name="role_id" value="<?php echo e($selectedRole?->id); ?>">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Filter by route name, URI or permission slug..." class="focus-accent w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-gray-300 bg-white">
            </div>
            <select name="status" onchange="this.form.submit()" class="focus-accent text-xs rounded-xl border border-gray-300 px-3 py-2 bg-white">
                <option value="all" <?php if($statusFilter === 'all'): echo 'selected'; endif; ?>>All Statuses</option>
                <option value="missing" <?php if($statusFilter === 'missing'): echo 'selected'; endif; ?>>Only Missing in DB (<?php echo e($missingCount); ?>)</option>
                <option value="existing" <?php if($statusFilter === 'existing'): echo 'selected'; endif; ?>>Only Existing in DB (<?php echo e($existingCount); ?>)</option>
            </select>
        </form>

        <div class="text-xs text-gray-500">
            Showing <strong class="text-gray-900"><?php echo e($totalCount); ?></strong> routes across <strong class="text-gray-900"><?php echo e(count($groupedRoutes)); ?></strong> functional categories
        </div>
    </div>

    
    <div class="space-y-6">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $groupedRoutes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupName => $routesInGroup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden group-box" data-group-name="<?php echo e($groupName); ?>">
                
                
                <div class="px-5 py-3.5 bg-gray-50/80 border-b border-gray-200 flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox"
                                   class="group-master-cb w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300"
                                   onchange="toggleGroupAll('<?php echo e(addslashes($groupName)); ?>', this.checked)">
                            <h3 class="text-sm font-bold text-gray-900"><?php echo e($groupName); ?></h3>
                        </label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-gray-200/70 text-gray-700">
                            <?php echo e(count($routesInGroup)); ?> routes
                        </span>
                    </div>

                    <div class="flex items-center gap-2 text-xs">
                        <button type="button" onclick="toggleGroupAll('<?php echo e(addslashes($groupName)); ?>', true)" class="text-indigo-600 hover:underline font-medium">Select Group</button>
                        <span class="text-gray-300">|</span>
                        <button type="button" onclick="toggleGroupAll('<?php echo e(addslashes($groupName)); ?>', false)" class="text-gray-500 hover:underline">Clear Group</button>
                    </div>
                </div>

                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 bg-white text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                                <th class="px-5 py-2.5 w-12 text-center">Enable</th>
                                <th class="px-5 py-2.5">Route &amp; URI</th>
                                <th class="px-5 py-2.5 w-24">Method</th>
                                <th class="px-5 py-2.5">Permission Slug</th>
                                <th class="px-5 py-2.5 w-32 text-center">DB Status</th>
                                <th class="px-5 py-2.5 w-32 text-center">Role Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $routesInGroup; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php ($method = $r['http_method']); ?>
                                <tr class="hover:bg-indigo-50/20 transition-colors route-row" data-group="<?php echo e($groupName); ?>">
                                    
                                    
                                    <td class="px-5 py-3 text-center">
                                        <input type="checkbox"
                                               class="perm-cb w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 cursor-pointer"
                                               data-group="<?php echo e($groupName); ?>"
                                               data-name="<?php echo e($r['suggested_permission']); ?>"
                                               data-display="<?php echo e($r['display_name']); ?>"
                                               data-scope="<?php echo e($r['scope']); ?>"
                                               data-missing="<?php echo e($r['is_existing'] ? '0' : '1'); ?>"
                                               onchange="onCheckboxChanged()">
                                    </td>

                                    
                                    <td class="px-5 py-3">
                                        <div class="font-bold text-gray-900 font-mono text-[11.5px]"><?php echo e($r['route_name']); ?></div>
                                        <div class="text-[11px] text-gray-400 font-mono mt-0.5"><?php echo e($r['uri']); ?></div>
                                    </td>

                                    
                                    <td class="px-5 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase font-mono
                                            <?php echo e(str_contains($method, 'GET') ? 'bg-blue-100 text-blue-800' : (str_contains($method, 'POST') ? 'bg-emerald-100 text-emerald-800' : (str_contains($method, 'DELETE') ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800'))); ?>">
                                            <?php echo e($method); ?>

                                        </span>
                                    </td>

                                    
                                    <td class="px-5 py-3">
                                        <div class="font-mono text-xs text-indigo-800 font-medium"><?php echo e($r['suggested_permission']); ?></div>
                                        <div class="text-[11px] text-gray-400 mt-0.5"><?php echo e($r['display_name']); ?></div>
                                    </td>

                                    
                                    <td class="px-5 py-3 text-center">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($r['is_existing']): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <i class="fa-solid fa-check text-[9px]"></i> In Database
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                <i class="fa-solid fa-plus text-[9px]"></i> Missing in DB
                                            </span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>

                                    
                                    <td class="px-5 py-3 text-center">
                                        <span class="role-status-badge inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold" data-perm-name="<?php echo e($r['suggested_permission']); ?>">
                                            <!-- Dynamically filled by JS -->
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center text-gray-400">
                <i class="fa-solid fa-route text-4xl mb-3 text-gray-300"></i>
                <h4 class="text-base font-bold text-gray-700">No routes found matching current filter</h4>
                <p class="text-xs text-gray-400 mt-1">Try switching tabs or clearing your search keywords.</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <form id="form-sync-role" method="POST" action="<?php echo e(route('admin.access-control.route-permissions.assign-to-role')); ?>" class="hidden">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="role_id" id="sync-role-id" value="<?php echo e($selectedRole?->id); ?>">
        <input type="hidden" name="scope" value="<?php echo e($selectedScope); ?>">
        <div id="sync-role-inputs"></div>
    </form>

    
    <form id="form-create-missing" method="POST" action="<?php echo e(route('admin.access-control.route-permissions.create-permissions')); ?>" class="hidden">
        <?php echo csrf_field(); ?>
        <div id="create-missing-inputs"></div>
    </form>

    
    <script>
        // Pre-loaded role permissions map from server
        const rolePermissionsMap = <?php echo json_encode($rolePermissionsMap, 15, 512) ?>;
        const rolesList = <?php echo json_encode($roles->keyBy('id'), 15, 512) ?>;
        let currentRoleId = <?php echo e((int)($selectedRole?->id ?? 0)); ?>;

        document.addEventListener('DOMContentLoaded', () => {
            applyRolePermissions(currentRoleId);
        });

        function onRoleChanged(roleId) {
            currentRoleId = parseInt(roleId);
            const role = rolesList[currentRoleId];
            if (role) {
                document.getElementById('btn-role-name').innerText = role.display_name || role.name;
                document.getElementById('current-role-badge').innerText = role.display_name || role.name;
                document.getElementById('sync-role-id').value = currentRoleId;
            }
            applyRolePermissions(currentRoleId);
        }

        function applyRolePermissions(roleId) {
            const activePerms = new Set(rolePermissionsMap[roleId] || []);

            document.querySelectorAll('.perm-cb').forEach(cb => {
                const permName = cb.getAttribute('data-name');
                const isAssigned = activePerms.has(permName);
                cb.checked = isAssigned;
            });

            updateBadgesAndCounters();
            updateGroupMasterCheckboxes();
        }

        function resetToRoleDefault() {
            applyRolePermissions(currentRoleId);
        }

        function onCheckboxChanged() {
            updateBadgesAndCounters();
            updateGroupMasterCheckboxes();
        }

        function updateBadgesAndCounters() {
            const activePerms = new Set(rolePermissionsMap[currentRoleId] || []);
            let totalChecked = 0;
            let missingChecked = 0;

            document.querySelectorAll('.perm-cb').forEach(cb => {
                const permName = cb.getAttribute('data-name');
                const isMissing = cb.getAttribute('data-missing') === '1';

                if (cb.checked) {
                    totalChecked++;
                    if (isMissing) missingChecked++;
                }

                // Update All Row Badges with this permission name
                document.querySelectorAll(`.role-status-badge[data-perm-name="${permName}"]`).forEach(badge => {
                    if (cb.checked) {
                        badge.className = 'role-status-badge inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200';
                        badge.innerHTML = '<i class="fa-solid fa-check text-[9px]"></i> Assigned';
                    } else {
                        badge.className = 'role-status-badge inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-gray-50 text-gray-400 border border-gray-200';
                        badge.innerHTML = 'Unassigned';
                    }
                });
            });

            document.getElementById('selected-counter').innerText = totalChecked;
            const missingEl = document.getElementById('missing-counter');
            if (missingEl) missingEl.innerText = missingChecked;
        }

        function updateGroupMasterCheckboxes() {
            document.querySelectorAll('.group-box').forEach(box => {
                const groupName = box.getAttribute('data-group-name');
                const checkboxes = box.querySelectorAll('.perm-cb');
                const master = box.querySelector('.group-master-cb');

                if (checkboxes.length > 0 && master) {
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    const someChecked = Array.from(checkboxes).some(cb => cb.checked);
                    master.checked = allChecked;
                    master.indeterminate = (!allChecked && someChecked);
                }
            });

            // Master overall toggle
            const allBoxes = document.querySelectorAll('.perm-cb');
            const masterToggle = document.getElementById('master-toggle');
            if (allBoxes.length > 0 && masterToggle) {
                const allChecked = Array.from(allBoxes).every(cb => cb.checked);
                const someChecked = Array.from(allBoxes).some(cb => cb.checked);
                masterToggle.checked = allChecked;
                masterToggle.indeterminate = (!allChecked && someChecked);
            }
        }

        function toggleGroupAll(groupName, isChecked) {
            document.querySelectorAll(`.perm-cb[data-group="${groupName}"]`).forEach(cb => {
                cb.checked = isChecked;
            });
            updateBadgesAndCounters();
            updateGroupMasterCheckboxes();
        }

        function toggleMasterAll(isChecked) {
            document.querySelectorAll('.perm-cb').forEach(cb => {
                cb.checked = isChecked;
            });
            updateBadgesAndCounters();
            updateGroupMasterCheckboxes();
        }

        function selectOnlyMissing() {
            document.querySelectorAll('.perm-cb').forEach(cb => {
                cb.checked = (cb.getAttribute('data-missing') === '1');
            });
            updateBadgesAndCounters();
            updateGroupMasterCheckboxes();
        }

        function clearAllSelections() {
            document.querySelectorAll('.perm-cb').forEach(cb => {
                cb.checked = false;
            });
            updateBadgesAndCounters();
            updateGroupMasterCheckboxes();
        }

        function submitRolePermissions() {
            const checkedBoxes = document.querySelectorAll('.perm-cb:checked');
            const role = rolesList[currentRoleId];
            const roleName = role ? (role.display_name || role.name) : 'Selected Role';
            const count = checkedBoxes.length;

            const uniqueNames = new Set(Array.from(checkedBoxes).map(cb => cb.getAttribute('data-name')));

            Swal.fire({
                title: 'Save Role Permissions?',
                html: `You are about to assign <strong>${uniqueNames.size} distinct permissions</strong> (covering ${count} routes) to role <span class="text-indigo-600 font-bold">${roleName}</span>.<br><br>Do you want to proceed?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fa-solid fa-check mr-1"></i> Yes, Save Permissions',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const container = document.getElementById('sync-role-inputs');
                    const payload = [];
                    const seen = new Set();

                    checkedBoxes.forEach((cb) => {
                        const name = cb.getAttribute('data-name');
                        if (!seen.has(name)) {
                            seen.add(name);
                            payload.push({
                                name: name,
                                display_name: cb.getAttribute('data-display'),
                                group_name: cb.getAttribute('data-group'),
                                scope: cb.getAttribute('data-scope')
                            });
                        }
                    });

                    container.innerHTML = `
                        <input type="hidden" name="permissions_json" value="${encodeURIComponent(JSON.stringify(payload))}">
                    `;

                    // Also decode on client-side before sending plain form
                    const hiddenInput = container.querySelector('input[name="permissions_json"]');
                    hiddenInput.value = JSON.stringify(payload);

                    document.getElementById('form-sync-role').submit();
                }
            });
        }

        function submitCreateMissing() {
            const missingChecked = document.querySelectorAll('.perm-cb:checked[data-missing="1"]');
            if (missingChecked.length === 0) {
                Swal.fire({
                    title: 'No Missing Permissions Selected',
                    text: 'Please select at least one missing/unseeded permission to create in the database.',
                    icon: 'warning',
                    confirmButtonColor: '#4f46e5'
                });
                return;
            }

            const uniqueNames = new Set(Array.from(missingChecked).map(cb => cb.getAttribute('data-name')));

            Swal.fire({
                title: 'Create Missing Permissions?',
                html: `You are about to create <strong>${uniqueNames.size} new permissions</strong> in the database.<br><br>These permissions will be added to the catalogue without altering existing role assignments.`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fa-solid fa-plus-circle mr-1"></i> Yes, Create in DB',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const container = document.getElementById('create-missing-inputs');
                    const payload = [];
                    const seen = new Set();

                    missingChecked.forEach((cb) => {
                        const name = cb.getAttribute('data-name');
                        if (!seen.has(name)) {
                            seen.add(name);
                            payload.push({
                                name: name,
                                display_name: cb.getAttribute('data-display'),
                                group_name: cb.getAttribute('data-group'),
                                scope: cb.getAttribute('data-scope')
                            });
                        }
                    });

                    container.innerHTML = `
                        <input type="hidden" name="permissions_json" value="">
                    `;

                    const hiddenInput = container.querySelector('input[name="permissions_json"]');
                    hiddenInput.value = JSON.stringify(payload);

                    document.getElementById('form-create-missing').submit();
                }
            });
        }
    </script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\access-control\route-permissions\index.blade.php ENDPATH**/ ?>