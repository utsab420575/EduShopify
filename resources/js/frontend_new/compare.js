/*
 * /v2/compare page renderer. Storage, add/remove, badges and toasts are all
 * owned by the shared window.EdushopifyCompare / fnToggleCompare / fnShowToast
 * / fnSyncAllCompareButtons globals defined in resources/js/frontend_new.js —
 * this module only ever reads that state and renders the comparison table,
 * mirroring resources/js/frontend/comparison.js's comparePage logic for the
 * Alpine-free v2 bundle. Fetches through
 * App\Http\Controllers\FrontendNew\CompareController, which stays the only
 * authoritative source for price/specs/supplier/availability.
 */

const COMPARE_STORAGE_KEY = 'edushopify_compare';

function escapeHtml(value) {
    if (value === null || value === undefined) return '';
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function maxItems() {
    return Number(window.EdushopifyCompare?.maxItems) || 5;
}

/*
 * The shared EdushopifyCompare helper (frontend_new.js) has no setVariant —
 * only this page's per-column variant dropdown needs it, so it's kept local,
 * writing the SAME storage key/shape directly rather than duplicating the
 * whole add/remove/read API.
 */
function setVariantInStorage(listingId, newVariantId) {
    let state;
    try {
        const raw = localStorage.getItem(COMPARE_STORAGE_KEY);
        state = raw ? JSON.parse(raw) : null;
    } catch (e) {
        state = null;
    }
    if (!state || !Array.isArray(state.items)) return;

    const lId = Number(listingId);
    const vId = newVariantId ? Number(newVariantId) : null;
    const idx = state.items.findIndex((i) => Number(i.listing_id) === lId);
    if (idx === -1) return;

    state.items[idx] = { listing_id: lId, variant_id: vId };
    try {
        localStorage.setItem(COMPARE_STORAGE_KEY, JSON.stringify(state));
    } catch (e) {
        // Storage full/unavailable — nothing more we can safely do.
    }
    // Same event resources/js/frontend_new.js's own write path dispatches —
    // its listener re-runs fnSyncAllCompareButtons for us.
    window.dispatchEvent(new CustomEvent('compare:changed', { detail: { count: state.items.length } }));
}

function rowAllSame(values) {
    const nonNull = values.filter((v) => v !== null && v !== undefined && v !== '');
    if (nonNull.length !== values.length) return false;
    const unique = new Set(nonNull.map((v) => JSON.stringify(v)));
    return unique.size === 1;
}

function valueHasMatch(values, index) {
    const val = values[index];
    if (val === null || val === undefined || val === '') return false;
    const key = JSON.stringify(val);
    let matches = 0;
    for (const v of values) {
        if (v !== null && v !== undefined && v !== '' && JSON.stringify(v) === key) matches++;
    }
    return matches >= 2;
}

function stockLabel(status) {
    return ({ in_stock: 'In Stock', limited: 'Limited Stock', on_request: 'Made to Order', out_of_stock: 'Out of Stock' })[status] || 'Available';
}

function starsHtml(rating, size) {
    let html = '';
    const rounded = Math.round(rating || 0);
    for (let i = 1; i <= 5; i++) {
        html += `<i class="fa-solid fa-star ${size}" style="color:${i <= rounded ? '#f59e0b' : '#e2e8f0'}"></i>`;
    }
    return html;
}

function renderCompareHeaderCell(item) {
    const isAuthed = document.body.dataset.authed === '1';
    const requestUrl = isAuthed
        ? `/buyer/rfqs/create?listing=${item.listing_id}`
        : `/v2/handoff/request-quote/${item.slug}`;

    const priceHtml = (item.pricing_type === 'fixed' && item.price !== null)
        ? `<div class="mb-2">
                <p class="text-base font-bold text-gray-900">${escapeHtml(item.currency_code)} ${Number(item.price).toFixed(2)}</p>
                ${item.compare_at_price ? `<p class="text-[11px] line-through text-gray-400">${escapeHtml(item.currency_code)} ${Number(item.compare_at_price).toFixed(2)}</p>` : ''}
           </div>`
        : `<p class="text-sm font-semibold mb-2 text-emerald-600">Request Quote</p>`;

    const variantOptions = (item.variants || []).length > 1
        ? `<select onchange="fnCompareChangeVariant(${item.listing_id}, this.value)" class="w-full text-xs rounded-lg border border-gray-200 px-2 py-1.5 mb-2">
                <option value="" ${!item.variant_id ? 'selected' : ''}>Base listing</option>
                ${item.variants.map((v) => `<option value="${v.id}" ${v.id === item.variant_id ? 'selected' : ''}>${escapeHtml(v.label)}</option>`).join('')}
           </select>`
        : '';

    return `
        <th class="align-top px-4 py-4 text-left border-l border-gray-200 bg-white" style="width:220px;min-width:220px;">
            <div class="relative">
                <button type="button" onclick="fnCompareRemove(${item.listing_id}, ${item.variant_id ?? 'null'})"
                        class="comparison-hide-print absolute -top-1 -right-1 w-7 h-7 rounded-full flex items-center justify-center text-xs bg-gray-50 text-gray-500 hover:text-gray-800"
                        title="Remove from comparison" aria-label="Remove from comparison">
                    <i class="fa-solid fa-xmark"></i>
                </button>

                <a href="/v2/product/${item.slug}" class="block mb-2">
                    <div class="aspect-square rounded-xl overflow-hidden flex items-center justify-center bg-gray-50">
                        ${item.thumb_url
                            ? `<img src="${escapeHtml(item.thumb_url)}" class="w-full h-full object-contain p-2" alt="${escapeHtml(item.name)}">`
                            : `<i class="fa-solid fa-box text-3xl text-gray-300"></i>`}
                    </div>
                </a>

                <a href="/v2/product/${item.slug}" class="block text-sm font-semibold leading-snug mb-1 text-gray-900 hover:underline">${escapeHtml(item.name)}</a>
                ${item.brand ? `<p class="text-xs mb-1 text-gray-500">${escapeHtml(item.brand)}</p>` : ''}
                <a href="${item.supplier_slug ? '/v2/supplier/' + item.supplier_slug : '#'}" class="text-xs font-medium hover:underline block mb-2 text-emerald-600">${escapeHtml(item.supplier_name || 'Supplier')}</a>

                ${variantOptions}
                ${priceHtml}

                ${item.moq ? `<p class="text-[11px] mb-1 text-gray-500">MOQ: ${escapeHtml(item.moq)}${item.unit ? ' ' + escapeHtml(item.unit) : ''}</p>` : ''}

                ${item.is_product ? `<p class="text-[11px] mb-3 font-semibold ${item.stock_status === 'out_of_stock' ? 'text-red-500' : 'text-emerald-600'}">
                        <i class="fa-solid ${item.stock_status === 'out_of_stock' ? 'fa-circle-xmark' : 'fa-circle-check'}"></i>
                        ${stockLabel(item.stock_status)}
                   </p>` : ''}

                <div class="comparison-hide-print space-y-1.5">
                    <a href="/v2/product/${item.slug}" class="block text-center px-2 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 text-gray-700 hover:border-gray-300">View Product</a>
                    <a href="${requestUrl}" class="block text-center px-2 py-1.5 rounded-lg text-xs font-semibold bg-emerald-500 hover:bg-emerald-600 text-white">Request Quotation</a>
                </div>
            </div>
        </th>`;
}

function renderAddProductCell() {
    return `
        <th class="comparison-hide-print px-4 py-4 align-top" style="width:180px;min-width:180px;background:#f8fafc;border-left:1px solid #e5e7eb;">
            <div class="relative">
                <button type="button" onclick="fnCompareToggleAddPanel(this)" class="w-full flex flex-col items-center justify-center gap-1.5 text-xs font-semibold py-6 rounded-xl border-2 border-dashed border-gray-300 text-gray-500 hover:border-gray-400">
                    <i class="fa-solid fa-plus text-base"></i> Add Product
                </button>
                <div data-compare-add-panel class="hidden absolute z-30 mt-2 left-0 w-64 bg-white border border-gray-200 rounded-xl shadow-lg p-3">
                    <input type="text" data-compare-add-input oninput="fnCompareSearch(this)" placeholder="Search products…" class="w-full h-10 px-3 rounded-lg border border-gray-200 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    <div data-compare-add-results class="max-h-56 overflow-y-auto space-y-1"></div>
                    <p data-compare-add-empty class="hidden text-xs py-2 text-center text-gray-500">No matching products.</p>
                </div>
            </div>
        </th>`;
}

function renderRatingRow(label, items, field, countField) {
    return `
        <tr>
            <td class="sticky left-0 z-10 px-4 py-3 text-xs font-medium align-top bg-white text-gray-500 border-t border-gray-200">${label}</td>
            ${items.map((item) => `
                <td class="px-4 py-3 text-xs align-top border-t border-l border-gray-200">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="flex items-center gap-0.5">${starsHtml(item[field], 'text-[10px]')}</span>
                        <span class="font-semibold text-gray-900">${Number(item[field] || 0).toFixed(1)}</span>
                        <span class="text-gray-500">(${item[countField] || 0})</span>
                    </div>
                </td>`).join('')}
        </tr>`;
}

function renderSpecRow(row, showDiffsOnly) {
    if (showDiffsOnly && rowAllSame(row.values)) return '';

    return `
        <tr>
            <td class="sticky left-0 z-10 px-4 py-3 text-xs font-medium align-top bg-white text-gray-500 border-t border-gray-200">
                ${escapeHtml(row.name)}${row.unit ? ` <span class="opacity-60">(${escapeHtml(row.unit)})</span>` : ''}
            </td>
            ${row.values.map((val, i) => {
                let cell;
                if (val === null || val === undefined || val === '') {
                    cell = `<span class="text-red-500" title="Specification not provided"><i class="fa-solid fa-xmark"></i></span>`;
                } else if (valueHasMatch(row.values, i)) {
                    cell = `<span class="inline-flex items-center gap-1.5"><i class="fa-solid fa-check text-emerald-500"></i><span class="text-gray-900">${escapeHtml(val)}</span></span>`;
                } else {
                    cell = `<span class="text-gray-900">${escapeHtml(val)}</span>`;
                }
                return `<td class="px-4 py-3 text-xs align-top border-t border-l border-gray-200">${cell}</td>`;
            }).join('')}
        </tr>`;
}

const comparePageState = {
    listings: [],
    matrix: { key_specs: [], additional_groups: [] },
    showAdditional: false,
    showDiffsOnly: false,
};

function renderComparePage() {
    const root = document.getElementById('v2-compare-page');
    if (!root) return;

    const emptyEl = root.querySelector('[data-compare-empty]');
    const loadedEl = root.querySelector('[data-compare-loaded]');
    const countLabel = root.querySelector('[data-compare-count]');
    const headerRow = root.querySelector('[data-compare-header-row]');
    const tbody = root.querySelector('[data-compare-body]');
    const rfqLink = root.querySelector('[data-compare-rfq-link]');
    const rfqCount = root.querySelector('[data-compare-rfq-count]');

    const count = window.EdushopifyCompare.count();
    const limit = maxItems();

    if (count === 0) {
        emptyEl.classList.remove('hidden');
        loadedEl.classList.add('hidden');
        return;
    }

    emptyEl.classList.add('hidden');
    loadedEl.classList.remove('hidden');
    countLabel.textContent = `(${count} of ${limit})`;

    const listings = comparePageState.listings;
    const isAuthed = document.body.dataset.authed === '1';
    if (rfqLink) {
        rfqLink.href = isAuthed
            ? '/buyer/rfqs/create?listings=' + listings.map((l) => l.listing_id).join(',')
            : '/v2/handoff/compare-rfq?listings=' + listings.map((l) => l.slug).join(',');
    }
    if (rfqCount) rfqCount.textContent = String(count);

    const productHeaderCell = `
        <th class="sticky z-20 text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide bg-gray-50 text-gray-500" style="width:160px;min-width:160px;">
            Product
        </th>`;

    headerRow.innerHTML = productHeaderCell
        + listings.map(renderCompareHeaderCell).join('')
        + (count < limit ? renderAddProductCell() : '');

    const matrix = comparePageState.matrix;
    let bodyHtml = '';

    bodyHtml += `
        <tr>
            <td class="sticky z-10 px-4 py-2 text-[11px] font-bold uppercase tracking-wide bg-gray-50 text-emerald-600 border-t border-gray-200">Reviews</td>
            <td colspan="${listings.length}" class="px-4 py-2 border-t border-l border-gray-200 bg-gray-50"></td>
        </tr>`;
    bodyHtml += renderRatingRow('Product Rating', listings, 'product_rating', 'product_reviews_count');
    bodyHtml += renderRatingRow('Supplier Rating', listings, 'supplier_rating', 'supplier_reviews_count');

    matrix.key_specs.forEach((row) => { bodyHtml += renderSpecRow(row, comparePageState.showDiffsOnly); });

    if (comparePageState.showAdditional) {
        matrix.additional_groups.forEach((group) => {
            bodyHtml += `
                <tr>
                    <td class="sticky z-10 px-4 py-2 text-[11px] font-bold uppercase tracking-wide bg-gray-50 text-emerald-600 border-t border-gray-200">${escapeHtml(group.group_name)}</td>
                    <td colspan="${listings.length}" class="px-4 py-2 border-t border-l border-gray-200 bg-gray-50"></td>
                </tr>`;
            group.rows.forEach((row) => { bodyHtml += renderSpecRow(row, comparePageState.showDiffsOnly); });
        });
    }

    tbody.innerHTML = bodyHtml;

    const toggleBtn = root.querySelector('[data-compare-toggle-additional]');
    if (toggleBtn) {
        toggleBtn.innerHTML = comparePageState.showAdditional
            ? '<i class="fa-solid fa-chevron-up mr-1.5"></i> Show Basic Information'
            : '<i class="fa-solid fa-chevron-down mr-1.5"></i> Show Additional Information';
    }
}

async function refreshComparePage() {
    const root = document.getElementById('v2-compare-page');
    if (!root) return;

    const items = window.EdushopifyCompare.getItems();

    if (items.length === 0) {
        comparePageState.listings = [];
        comparePageState.matrix = { key_specs: [], additional_groups: [] };
        renderComparePage();
        return;
    }

    const loadingEl = root.querySelector('[data-compare-loading]');
    if (loadingEl) loadingEl.classList.remove('hidden');

    try {
        const response = await fetch('/v2/compare/data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            body: JSON.stringify({ items }),
        });

        if (!response.ok) return;

        const data = await response.json();
        comparePageState.listings = data.listings;
        comparePageState.matrix = data.matrix;

        if (data.removed_ids && data.removed_ids.length > 0) {
            data.removed_ids.forEach((id) => window.EdushopifyCompare.removeItem(id));
            window.fnShowToast(
                data.removed_ids.length === 1
                    ? 'One product is no longer available and was removed from comparison.'
                    : `${data.removed_ids.length} products are no longer available and were removed from comparison.`,
                'warning'
            );
        }
    } finally {
        if (loadingEl) loadingEl.classList.add('hidden');
        renderComparePage();
    }
}

function fnCompareRemove(listingId, variantId) {
    window.EdushopifyCompare.removeItem(listingId, variantId);
    window.fnShowToast('Product removed from comparison.', 'info');
    refreshComparePage();
}
window.fnCompareRemove = fnCompareRemove;

function fnCompareChangeVariant(listingId, variantId) {
    setVariantInStorage(listingId, variantId || null);
    refreshComparePage();
}
window.fnCompareChangeVariant = fnCompareChangeVariant;

function fnCompareClearAll() {
    if (!confirm('Clear all products from comparison?')) return;
    window.EdushopifyCompare.clear();
    window.fnShowToast('Comparison cleared.', 'info');
    comparePageState.listings = [];
    comparePageState.matrix = { key_specs: [], additional_groups: [] };
    renderComparePage();
}
window.fnCompareClearAll = fnCompareClearAll;

function fnCompareToggleAdditional() {
    comparePageState.showAdditional = !comparePageState.showAdditional;
    renderComparePage();
}
window.fnCompareToggleAdditional = fnCompareToggleAdditional;

function fnCompareToggleDiffs(checkbox) {
    comparePageState.showDiffsOnly = checkbox.checked;
    renderComparePage();
}
window.fnCompareToggleDiffs = fnCompareToggleDiffs;

let addMoreDebounce = null;
function fnCompareSearch(input) {
    clearTimeout(addMoreDebounce);
    const query = input.value.trim();
    const resultsEl = document.querySelector('[data-compare-add-results]');
    const emptyEl = document.querySelector('[data-compare-add-empty]');
    if (!resultsEl) return;

    if (query.length < 2) {
        resultsEl.innerHTML = '';
        if (emptyEl) emptyEl.classList.add('hidden');
        return;
    }

    addMoreDebounce = setTimeout(async () => {
        try {
            const response = await fetch('/v2/compare/search?q=' + encodeURIComponent(query), { headers: { Accept: 'application/json' } });
            const data = response.ok ? await response.json() : { results: [] };
            const results = data.results || [];

            resultsEl.innerHTML = results.map((r) => `
                <button type="button" onclick="fnCompareAddMore(${r.id})" class="w-full text-left px-2 py-2 rounded-lg hover:bg-gray-50 text-xs flex items-center gap-2">
                    ${r.thumb_url ? `<img src="${escapeHtml(r.thumb_url)}" class="w-8 h-8 rounded object-cover shrink-0" alt="">` : ''}
                    <span class="min-w-0">
                        <span class="block font-medium truncate text-gray-900">${escapeHtml(r.name)}</span>
                        <span class="block text-[10px] text-gray-500">${escapeHtml(r.category)}</span>
                    </span>
                </button>`).join('');

            if (emptyEl) emptyEl.classList.toggle('hidden', results.length > 0);
        } catch (e) {
            resultsEl.innerHTML = '';
        }
    }, 300);
}
window.fnCompareSearch = fnCompareSearch;

function fnCompareAddMore(listingId) {
    const result = window.EdushopifyCompare.addItem(listingId, null);
    const input = document.querySelector('[data-compare-add-input]');
    const resultsEl = document.querySelector('[data-compare-add-results]');
    if (input) input.value = '';
    if (resultsEl) resultsEl.innerHTML = '';
    document.querySelectorAll('[data-compare-add-panel]').forEach((p) => p.classList.add('hidden'));

    if (result === 'added') {
        window.fnShowToast('Product added to comparison.', 'success');
        refreshComparePage();
    } else if (result === 'duplicate') {
        window.fnShowToast('This product is already in comparison.', 'info');
    } else if (result === 'max_reached') {
        window.fnShowToast(`You can compare up to ${maxItems()} products.`, 'warning');
    }
}
window.fnCompareAddMore = fnCompareAddMore;

function fnCompareToggleAddPanel(btn) {
    const panel = btn.parentElement.querySelector('[data-compare-add-panel]');
    panel?.classList.toggle('hidden');
}
window.fnCompareToggleAddPanel = fnCompareToggleAddPanel;

document.addEventListener('click', function (e) {
    if (e.target.closest('[data-compare-add-panel]') || e.target.closest('[onclick="fnCompareToggleAddPanel(this)"]')) return;
    document.querySelectorAll('[data-compare-add-panel]').forEach((p) => p.classList.add('hidden'));
});

/*
 * Always deferred to DOMContentLoaded — never run inline at module-evaluation
 * time. This module is imported near the TOP of frontend_new.js, before that
 * file's own bottom section assigns window.EdushopifyCompare/fnShowToast; by
 * the time DOMContentLoaded fires, every module's top-level code (including
 * that assignment) has already run, so those globals are guaranteed to exist.
 */
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('v2-compare-page')) {
        refreshComparePage();
    }
});

/* Cross-tab / same-page mutation from elsewhere (a card's compare button,
   another tab) — re-render this page's table against the fresh selection. */
window.addEventListener('compare:changed', () => { if (document.getElementById('v2-compare-page')) refreshComparePage(); });
window.addEventListener('storage', function (e) {
    if (e.key === COMPARE_STORAGE_KEY && document.getElementById('v2-compare-page')) refreshComparePage();
});
