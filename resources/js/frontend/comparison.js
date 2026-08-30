/**
 * Product Comparison — selection state lives ENTIRELY in localStorage.
 * The backend (App\Http\Controllers\Frontend\ProductComparisonController /
 * App\Services\Catalog\ProductComparisonService) is the only authoritative
 * source for price/specs/supplier/availability; this module only ever
 * stores/reads {listing_id, variant_id} pairs.
 *
 * Imported (not built as a separate Vite entry) by resources/js/frontend.js
 * so there is exactly one Alpine instance for the whole public frontend —
 * two independent `Alpine.start()` calls from separate entry bundles would
 * silently break component registration.
 */

const STORAGE_KEY = 'edushopify_compare';
const STORAGE_VERSION = 1;
const MAX_ITEMS = Number(document.querySelector('meta[name="compare-max-items"]')?.content) || 5;

function emptyState() {
    return { version: STORAGE_VERSION, items: [] };
}

/**
 * Reads and validates the stored payload, resetting to an empty, valid
 * shape on ANY corruption — invalid JSON, wrong version, wrong structure,
 * non-array items, duplicate pairs, more than the configured maximum.
 * Never throws; never leaves the page in a broken state (spec §52).
 */
function readRaw() {
    let raw;
    try {
        raw = localStorage.getItem(STORAGE_KEY);
    } catch (e) {
        // localStorage unavailable (private mode, storage disabled, etc.)
        return emptyState();
    }

    if (!raw) {
        return emptyState();
    }

    let parsed;
    try {
        parsed = JSON.parse(raw);
    } catch (e) {
        return emptyState();
    }

    if (!parsed || typeof parsed !== 'object' || !Array.isArray(parsed.items)) {
        return emptyState();
    }

    const seen = new Set();
    const items = [];

    for (const item of parsed.items) {
        if (!item || typeof item !== 'object') continue;
        const listingId = Number(item.listing_id);
        if (!Number.isInteger(listingId) || listingId <= 0) continue;

        const variantId = item.variant_id === null || item.variant_id === undefined || item.variant_id === ''
            ? null
            : Number(item.variant_id);
        const normalizedVariantId = Number.isInteger(variantId) && variantId > 0 ? variantId : null;

        const key = listingId + ':' + (normalizedVariantId ?? '0');
        if (seen.has(key)) continue;
        seen.add(key);

        items.push({ listing_id: listingId, variant_id: normalizedVariantId });
        if (items.length >= MAX_ITEMS) break;
    }

    return { version: STORAGE_VERSION, items };
}

function writeRaw(state) {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
    } catch (e) {
        // Storage full/unavailable — nothing more we can safely do; the
        // in-memory Alpine state for this page load still reflects reality.
    }
    window.dispatchEvent(new CustomEvent('compare:changed', { detail: { count: state.items.length } }));
}

export function toast(message, type = 'success') {
    window.dispatchEvent(new CustomEvent('compare:toast', { detail: { message, type } }));
}

export const EdushopifyCompare = {
    maxItems: MAX_ITEMS,

    getItems() {
        return readRaw().items;
    },

    count() {
        return readRaw().items.length;
    },

    contains(listingId, variantId = null) {
        const vId = variantId ? Number(variantId) : null;
        return readRaw().items.some((i) => i.listing_id === Number(listingId) && i.variant_id === vId);
    },

    /**
     * Returns 'added' | 'duplicate' | 'max_reached'.
     */
    addItem(listingId, variantId = null) {
        const state = readRaw();
        const lId = Number(listingId);
        const vId = variantId ? Number(variantId) : null;

        if (state.items.some((i) => i.listing_id === lId && i.variant_id === vId)) {
            return 'duplicate';
        }

        if (state.items.length >= MAX_ITEMS) {
            return 'max_reached';
        }

        state.items.push({ listing_id: lId, variant_id: vId });
        writeRaw(state);
        return 'added';
    },

    removeItem(listingId, variantId = null) {
        const state = readRaw();
        const lId = Number(listingId);
        const vId = variantId ? Number(variantId) : null;
        state.items = state.items.filter((i) => !(i.listing_id === lId && i.variant_id === vId));
        writeRaw(state);
    },

    /**
     * Swaps the variant selected for a listing already in the comparison
     * (used by the compare page's per-column variant dropdown) while
     * preserving its position.
     */
    setVariant(listingId, newVariantId) {
        const state = readRaw();
        const lId = Number(listingId);
        const vId = newVariantId ? Number(newVariantId) : null;
        const idx = state.items.findIndex((i) => i.listing_id === lId);
        if (idx !== -1) {
            state.items[idx] = { listing_id: lId, variant_id: vId };
            writeRaw(state);
        }
    },

    clear() {
        writeRaw(emptyState());
    },
};

export function registerComparisonAlpine(Alpine) {
    Alpine.data('compareBadge', () => ({
        count: EdushopifyCompare.count(),
        init() {
            window.addEventListener('compare:changed', (e) => {
                this.count = e.detail.count;
            });
        },
    }));

    Alpine.data('compareButton', (listingId, variantId = null) => ({
        listingId,
        variantId,
        active: EdushopifyCompare.contains(listingId, variantId),

        init() {
            window.addEventListener('compare:changed', () => {
                this.active = EdushopifyCompare.contains(this.listingId, this.variantId);
            });
        },

        toggle() {
            if (this.active) {
                EdushopifyCompare.removeItem(this.listingId, this.variantId);
                toast('Product removed from comparison.', 'success');
                return;
            }

            const result = EdushopifyCompare.addItem(this.listingId, this.variantId);
            if (result === 'added') {
                toast('Product added to comparison.', 'success');
            } else if (result === 'duplicate') {
                toast('This product is already in your comparison.', 'info');
            } else if (result === 'max_reached') {
                toast(`You can compare up to ${MAX_ITEMS} products at a time.`, 'warning');
            }
        },
    }));

    Alpine.data('comparePage', () => ({
        loading: true,
        items: [],
        listings: [],
        matrix: { key_specs: [], additional_groups: [] },
        maxItems: MAX_ITEMS,
        showAdditional: false,
        highlightDiffs: true,
        showDiffsOnly: false,
        addMoreQuery: '',
        addMoreResults: [],

        async init() {
            await this.refresh();
        },

        get count() {
            return this.items.length;
        },

        get canAddMore() {
            return this.count < this.maxItems;
        },

        async refresh() {
            this.items = EdushopifyCompare.getItems();

            if (this.items.length === 0) {
                this.listings = [];
                this.matrix = { key_specs: [], additional_groups: [] };
                this.loading = false;
                return;
            }

            this.loading = true;

            try {
                const response = await fetch('/compare/data', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({ items: this.items }),
                });

                if (!response.ok) {
                    this.loading = false;
                    return;
                }

                const data = await response.json();
                this.listings = data.listings;
                this.matrix = data.matrix;

                if (data.removed_ids && data.removed_ids.length > 0) {
                    data.removed_ids.forEach((id) => EdushopifyCompare.removeItem(id));
                    this.items = EdushopifyCompare.getItems();
                    toast(
                        data.removed_ids.length === 1
                            ? 'One product is no longer available and was removed from comparison.'
                            : `${data.removed_ids.length} products are no longer available and were removed from comparison.`,
                        'warning'
                    );
                }
            } finally {
                this.loading = false;
            }
        },

        remove(listingId, variantId) {
            EdushopifyCompare.removeItem(listingId, variantId);
            toast('Product removed from comparison.', 'success');
            this.refresh();
        },

        async changeVariant(listingId, variantId) {
            EdushopifyCompare.setVariant(listingId, variantId || null);
            await this.refresh();
        },

        clearAll() {
            EdushopifyCompare.clear();
            this.items = [];
            this.listings = [];
            this.matrix = { key_specs: [], additional_groups: [] };
            toast('Comparison cleared.', 'success');
        },

        async searchAddMore() {
            if (this.addMoreQuery.trim().length < 2) {
                this.addMoreResults = [];
                return;
            }

            try {
                const response = await fetch('/compare/search?q=' + encodeURIComponent(this.addMoreQuery), {
                    headers: { Accept: 'application/json' },
                });
                const data = response.ok ? await response.json() : { results: [] };
                this.addMoreResults = data.results || [];
            } catch (e) {
                this.addMoreResults = [];
            }
        },

        async addMore(listingId) {
            const result = EdushopifyCompare.addItem(listingId, null);
            this.addMoreQuery = '';
            this.addMoreResults = [];

            if (result === 'added') {
                toast('Product added to comparison.', 'success');
                await this.refresh();
            } else if (result === 'duplicate') {
                toast('This product is already in your comparison.', 'info');
            } else if (result === 'max_reached') {
                toast(`You can compare up to ${MAX_ITEMS} products at a time.`, 'warning');
            }
        },

        rowVisible(values) {
            if (!this.showDiffsOnly) return true;
            const nonNull = values.filter((v) => v !== null && v !== undefined && v !== '');
            const unique = new Set(nonNull.map((v) => JSON.stringify(v)));
            // A row counts as "different" if values disagree, OR if some
            // columns have a value and others are missing it entirely.
            return unique.size > 1 || nonNull.length !== values.length;
        },

        rowDiffers(values) {
            const nonNull = values.filter((v) => v !== null && v !== undefined && v !== '');
            const unique = new Set(nonNull.map((v) => JSON.stringify(v)));
            return unique.size > 1 || nonNull.length !== values.length;
        },
    }));
}
