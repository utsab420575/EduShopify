@props([
    'groups' => [],
    'selected' => [],
    'name' => 'permissions',
    'roleScope' => null,
])

@php
    $selected = collect($selected)->map(fn ($v) => (string) $v);
    $groups = collect($groups);
    $totalCount = $groups->sum(fn ($perms) => $perms->count());

    // A group is "relevant" to the role's own scope if any permission in it
    // shares that scope, is 'common', or is 'both' — those groups start
    // expanded; everything else starts collapsed (not hidden) so a platform
    // role isn't opened straight into a wall of buyer/supplier checkboxes,
    // while a genuinely cross-scope permission is still one click away.
    $isGroupRelevant = function ($perms) use ($roleScope) {
        if (! $roleScope || $roleScope === 'both') {
            return true;
        }
        $groupScope = $perms->first()?->capability_scope;

        return in_array($groupScope, [$roleScope, 'common', 'both'], true);
    };
@endphp

<div
    {{ $attributes }}
    x-data="{
        search: '',
        selectedCount: {{ $selected->count() }},
        totalCount: {{ $totalCount }},
        updateCount() {
            this.selectedCount = this.$el.querySelectorAll('.perm-matrix-checkbox:checked').length;
        },
        setAll(checked) {
            this.$el.querySelectorAll('.perm-matrix-checkbox').forEach(cb => cb.checked = checked);
            this.updateCount();
        },
        setGroup(groupEl, checked) {
            groupEl.querySelectorAll('.perm-matrix-checkbox').forEach(cb => cb.checked = checked);
            this.updateCount();
        },
        groupMatchesSearch(haystack) {
            return this.search === '' || haystack.toLowerCase().includes(this.search.toLowerCase());
        },
    }"
    x-init="updateCount()"
>
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 border-b border-gray-100 pb-4 mb-4">
        <div class="relative flex-1 min-w-[200px]">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input type="text" x-model="search" placeholder="Search permissions by name…"
                class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <span class="text-xs font-semibold text-gray-500" x-text="selectedCount + ' of ' + totalCount + ' selected'"></span>
            <button type="button" @click="setAll(true)" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Select All</button>
            <span class="text-gray-300">|</span>
            <button type="button" @click="setAll(false)" class="text-xs font-semibold text-gray-500 hover:text-gray-700">Clear All</button>
        </div>
    </div>

    <div class="space-y-4">
        @foreach($groups as $groupName => $groupPermissions)
            @php
                $groupHaystack = $groupPermissions->map(fn ($p) => $p->name.' '.($p->display_name ?? ''))->implode(' ');
                $relevant = $isGroupRelevant($groupPermissions);
                $groupSelectedCount = $groupPermissions->filter(fn ($p) => $selected->contains($p->name))->count();
            @endphp
            <div
                x-data="{ expanded: {{ $relevant ? 'true' : 'false' }} }"
                x-show="groupMatchesSearch(@js($groupHaystack))"
                class="perm-matrix-group rounded-xl border border-gray-200/80 bg-gray-50/70 overflow-hidden"
            >
                <button type="button" @click="expanded = !expanded"
                    class="w-full flex items-center justify-between gap-3 px-4 py-3 text-left hover:bg-gray-100/60 transition-colors">
                    <span class="flex items-center gap-2 min-w-0">
                        <i class="fa-solid fa-chevron-right text-[10px] text-gray-400 transition-transform shrink-0" :class="expanded && 'rotate-90'"></i>
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-800 truncate">{{ $groupName ?: 'General' }}</span>
                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-gray-200 text-gray-600 shrink-0">{{ $groupSelectedCount }}/{{ $groupPermissions->count() }}</span>
                    </span>
                    <label class="inline-flex items-center gap-1.5 text-[11px] text-gray-500 hover:text-gray-800 cursor-pointer shrink-0" @click.stop>
                        <input type="checkbox" @change="setGroup($el.closest('.perm-matrix-group'), $event.target.checked)" class="w-3.5 h-3.5 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                        Select all in group
                    </label>
                </button>

                <div x-show="expanded" class="px-4 pb-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                        @foreach($groupPermissions as $permission)
                            @php($checked = $selected->contains($permission->name))
                            <label
                                x-show="groupMatchesSearch(@js($permission->name.' '.($permission->display_name ?? '')))"
                                class="flex items-start gap-2 text-xs text-gray-700 bg-white border border-gray-200 rounded-lg p-2.5 hover:border-indigo-300 cursor-pointer transition-colors"
                            >
                                <input type="checkbox" name="{{ $name }}[]" value="{{ $permission->name }}" @checked($checked)
                                    @change="updateCount()"
                                    class="perm-matrix-checkbox w-4 h-4 mt-0.5 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                <div class="min-w-0">
                                    <div class="font-medium text-gray-900 flex items-center gap-1.5 flex-wrap">
                                        {{ $permission->display_name ?? $permission->name }}
                                        @if($permission->is_sensitive)
                                            <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700">Sensitive</span>
                                        @endif
                                    </div>
                                    <div class="text-[10px] text-gray-400 font-mono truncate">{{ $permission->name }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
