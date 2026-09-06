
<tr x-show="rowVisible(row.values)" x-cloak>
    <td class="sticky left-0 z-10 px-4 py-3 text-xs font-medium align-top" style="background:var(--fe-surface);color:var(--fe-text-muted);border-top:1px solid var(--fe-border);">
        <span x-text="row.name"></span>
        <span x-show="row.unit" class="opacity-60" x-text="'(' + row.unit + ')'"></span>
    </td>
    <template x-for="(val, i) in row.values" :key="i">
        <td class="px-4 py-3 text-xs align-top" style="border-top:1px solid var(--fe-border);border-left:1px solid var(--fe-border);">
            <template x-if="val === null || val === undefined || val === ''">
                <span style="color:var(--fe-danger);" title="Specification not provided" aria-label="Specification not provided">
                    <i class="fa-solid fa-xmark"></i>
                </span>
            </template>
            <template x-if="!(val === null || val === undefined || val === '') && valueHasMatch(row.values, i)">
                <span class="inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-check" style="color:var(--fe-success);"></i>
                    <span x-text="val" style="color:var(--fe-text);"></span>
                </span>
            </template>
            <template x-if="!(val === null || val === undefined || val === '') && !valueHasMatch(row.values, i)">
                <span x-text="val" style="color:var(--fe-text);"></span>
            </template>
        </td>
    </template>
</tr>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\components\marketplace\comparison-row.blade.php ENDPATH**/ ?>