{{--
    One attribute row across every selected column — included inside a
    <template x-for="row in ..."> scope (`row` = {attribute_id, name, unit,
    values: [...]}, one value per column in the same order as `listings`).
    Missing values render as "Not specified" — never invented (spec §32).
--}}
<tr x-show="rowVisible(row.values)" x-cloak
    :class="highlightDiffs && rowDiffers(row.values) ? 'comparison-row-diff' : ''">
    <td class="sticky left-0 z-10 px-4 py-3 text-xs font-medium align-top" style="background:var(--fe-surface);color:var(--fe-text-muted);border-top:1px solid var(--fe-border);">
        <span x-text="row.name"></span>
        <span x-show="row.unit" class="opacity-60" x-text="'(' + row.unit + ')'"></span>
    </td>
    <template x-for="(val, i) in row.values" :key="i">
        <td class="px-4 py-3 text-xs align-top" style="color:var(--fe-text);border-top:1px solid var(--fe-border);border-left:1px solid var(--fe-border);">
            <span x-text="val === null || val === undefined || val === '' ? 'Not specified' : val" :style="(val === null || val === undefined || val === '') ? 'color:var(--fe-text-subtle);' : ''"></span>
        </td>
    </template>
</tr>
