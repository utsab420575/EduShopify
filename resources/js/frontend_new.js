/*
 * Edushopify Frontend V2 — vanilla JS only (per spec: no Alpine, no Livewire).
 * Grows per phase as each static page's own <script> block is ported over.
 * Independent of resources/js/frontend.js (legacy).
 *
 * Functions invoked from inline onclick="" attributes in Blade views are
 * assigned to `window` explicitly. Without that, Vite/Rollup treats them as
 * unused module-scope declarations (it can't see references inside HTML
 * attribute strings) and tree-shakes them out of the production bundle —
 * silently breaking every onclick that calls them.
 */

import './frontend_new/compare.js';

function fnOpenMobileMenu() {
    document.getElementById('fn-mobile-menu')?.classList.remove('translate-x-full');
    document.getElementById('fn-mobile-overlay')?.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function fnCloseMobileMenu() {
    document.getElementById('fn-mobile-menu')?.classList.add('translate-x-full');
    document.getElementById('fn-mobile-overlay')?.classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') fnCloseMobileMenu();
});

/* Product detail — gallery thumbnail switcher */
function switchImg(thumb, src) {
    const mainImg = document.getElementById('main-img');
    if (mainImg) mainImg.src = src;
    document.querySelectorAll('.thumb').forEach(function (t) { t.classList.remove('active'); });
    thumb.classList.add('active');
}

/* Supplier profile — section tab switcher */
function switchTab(btn, name) {
    document.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.remove('active'); });
    document.querySelectorAll('.tab-panel').forEach(function (p) { p.style.display = 'none'; });
    btn.classList.add('active');
    const panel = document.getElementById('tab-' + name);
    if (panel) {
        if (name === 'about' || name === 'certifications' || name === 'contact') {
            panel.style.display = 'flex';
            panel.style.flexDirection = 'column';
            panel.style.gap = '16px';
        } else {
            panel.style.display = 'block';
        }
    }
}

window.fnOpenMobileMenu = fnOpenMobileMenu;
window.fnCloseMobileMenu = fnCloseMobileMenu;
window.switchImg = switchImg;
window.switchTab = switchTab;

/* RFQ list — live client-side search filter over the current page's cards */
const rfqSearchInput = document.getElementById('rfq-search');
if (rfqSearchInput) {
    rfqSearchInput.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        const cards = document.querySelectorAll('#rfq-list .rfq-card');
        let visible = 0;
        cards.forEach(function (card) {
            const match = card.textContent.toLowerCase().includes(q);
            card.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        const empty = document.getElementById('empty-state');
        if (empty) empty.classList.toggle('hidden', visible > 0);
    });
}

/* RFQ detail — deadline countdown */
(function () {
    const el = document.getElementById('deadline-countdown');
    if (!el || !el.dataset.deadline) return;
    const diff = Math.ceil((new Date(el.dataset.deadline) - new Date()) / (1000 * 60 * 60 * 24));
    if (diff > 0) {
        el.textContent = diff + (diff === 1 ? ' day remaining' : ' days remaining');
        el.className = 'text-xs text-red-500 mt-0.5';
    } else if (diff === 0) {
        el.textContent = 'Closes today';
        el.className = 'text-xs text-red-600 font-semibold mt-0.5';
    } else {
        el.textContent = 'Deadline passed';
        el.className = 'text-xs text-gray-400 mt-0.5';
    }
})();

/* ── Robust Clipboard Copy Utility (Supports HTTPS and HTTP fallback) ── */
function fnCopyTextToClipboard(text) {
    return new Promise((resolve) => {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(resolve).catch(() => {
                fallbackCopy(text, resolve);
            });
        } else {
            fallbackCopy(text, resolve);
        }
    });

    function fallbackCopy(str, resolve) {
        try {
            const el = document.createElement('textarea');
            el.value = str;
            el.style.top = '0';
            el.style.left = '0';
            el.style.position = 'fixed';
            el.style.opacity = '0.01';
            el.style.pointerEvents = 'none';
            el.style.zIndex = '-1';
            document.body.appendChild(el);
            el.focus();
            el.select();
            el.setSelectionRange(0, 99999);
            document.execCommand('copy');
            document.body.removeChild(el);
            resolve();
        } catch (err) {
            resolve();
        }
    }
}
window.fnCopyTextToClipboard = fnCopyTextToClipboard;

/* Share helper (Web Share API with clipboard fallback & toast) */
function fnShare(data) {
    const shareData = Object.assign({
        title: document.title,
        url: window.location.href
    }, data || {});

    if (navigator.share) {
        navigator.share(shareData).catch(() => {});
    } else {
        fnCopyTextToClipboard(shareData.url).then(() => {
            if (window.fnShowToast) {
                window.fnShowToast('Link copied to clipboard!', 'success');
            }
        });
    }
}
window.fnShare = fnShare;

/* ── Product Share Modal Handlers ── */
function fnOpenProductShareModal() {
    const modal = document.getElementById('fn-share-modal');
    if (!modal) return;
    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        modal.classList.remove('opacity-0');
        const card = document.getElementById('fn-share-modal-card');
        if (card) {
            card.classList.remove('scale-95');
            card.classList.add('scale-100');
        }
    });
    document.body.style.overflow = 'hidden';
}
window.fnOpenProductShareModal = fnOpenProductShareModal;

function fnCloseProductShareModal() {
    const modal = document.getElementById('fn-share-modal');
    if (!modal) return;
    modal.classList.add('opacity-0');
    const card = document.getElementById('fn-share-modal-card');
    if (card) {
        card.classList.remove('scale-100');
        card.classList.add('scale-95');
    }
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 200);
}
window.fnCloseProductShareModal = fnCloseProductShareModal;

function fnCopyProductShareLink(btn) {
    const input = document.getElementById('fn-share-url-input');
    const url = input ? input.value : window.location.href;

    if (input) {
        try {
            input.focus();
            input.select();
            input.setSelectionRange(0, 99999);
            document.execCommand('copy');
        } catch (e) {}
    }

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).catch(() => {});
    }

    if (btn) {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = `<svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg><span>Copied!</span>`;
        btn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
        btn.classList.add('bg-emerald-700');
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.remove('bg-emerald-700');
            btn.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
        }, 2000);
    }
    if (window.fnShowToast) {
        window.fnShowToast('Product link copied to clipboard!', 'success');
    }
}
window.fnCopyProductShareLink = fnCopyProductShareLink;

// Close share modal on Escape key press
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        fnCloseProductShareModal();
    }
});

/*
 * Homepage — per-grid skeleton reveal. The hero animates independently via
 * pure CSS (.hero-fade-up) and has no skeleton at all. Every other
 * section's heading/tabs/links render immediately for real; only its card
 * grid has a placeholder. Each skeleton element carries
 * data-skeleton-target pointing at the real grid it precedes, so this
 * works for any number of sections without per-section JS. No-ops on any
 * page without one, so it's safe to run everywhere frontend_new.js loads.
 */
(function () {
    const skeletons = document.querySelectorAll('[data-skeleton-target]');
    if (!skeletons.length) return;

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const MIN_SKELETON_MS = reducedMotion ? 0 : 500;
    const started = Date.now();

    function reveal() {
        const elapsed = Date.now() - started;
        const wait = Math.max(0, MIN_SKELETON_MS - elapsed);

        setTimeout(function () {
            skeletons.forEach(function (skeleton) {
                const real = document.querySelector(skeleton.dataset.skeletonTarget);
                skeleton.style.display = 'none';
                if (real) real.classList.remove('cards-hidden');
            });
        }, wait);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', reveal);
    } else {
        reveal();
    }
})();

/*
 * Homepage — All Suppliers category tabs (spec §23.4 pattern).
 * Guarded on .supplier-item so this doesn't also attach to the supplier
 * profile page's differently-purposed .tab-btn section tabs (same class
 * name, different widget, and this script loads on every page).
 */
if (document.querySelector('.supplier-item')) {
    document.querySelectorAll('.tab-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.tab-btn').forEach(function (b) {
                b.classList.remove('tag-active');
                b.classList.add('tag-inactive');
            });
            this.classList.remove('tag-inactive');
            this.classList.add('tag-active');

            const cat = this.dataset.cat;
            document.querySelectorAll('.supplier-item').forEach(function (item) {
                item.style.display = (cat === 'all' || item.dataset.cat === cat) ? '' : 'none';
            });
        });
    });
}

/* ══════════════════════════════════════════════════════════════
 * Product Comparison & Toast System (Frontend V2)
 * Pure vanilla JS, shares localStorage with /compare seamlessly.
 * ══════════════════════════════════════════════════════════════ */

const COMPARE_STORAGE_KEY = 'edushopify_compare';
const COMPARE_STORAGE_VERSION = 1;

function fnGetMaxCompareItems() {
    const meta = document.querySelector('meta[name="compare-max-items"]');
    return Number(meta?.content) || 5;
}

function fnCompareEmptyState() {
    return { version: COMPARE_STORAGE_VERSION, items: [] };
}

function fnReadCompareStorage() {
    let raw;
    try {
        raw = localStorage.getItem(COMPARE_STORAGE_KEY);
    } catch (e) {
        return fnCompareEmptyState();
    }
    if (!raw) return fnCompareEmptyState();

    let parsed;
    try {
        parsed = JSON.parse(raw);
    } catch (e) {
        return fnCompareEmptyState();
    }

    if (!parsed || typeof parsed !== 'object' || !Array.isArray(parsed.items)) {
        return fnCompareEmptyState();
    }

    const maxItems = fnGetMaxCompareItems();
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
        if (items.length >= maxItems) break;
    }

    return { version: COMPARE_STORAGE_VERSION, items };
}

function fnWriteCompareStorage(state) {
    try {
        localStorage.setItem(COMPARE_STORAGE_KEY, JSON.stringify(state));
    } catch (e) {}
    window.dispatchEvent(new CustomEvent('compare:changed', { detail: { count: state.items.length } }));
}

const EdushopifyCompare = {
    get maxItems() {
        return fnGetMaxCompareItems();
    },
    getItems() {
        return fnReadCompareStorage().items;
    },
    count() {
        return fnReadCompareStorage().items.length;
    },
    contains(listingId, variantId = null) {
        const lId = Number(listingId);
        const vId = variantId ? Number(variantId) : null;
        return fnReadCompareStorage().items.some(i => i.listing_id === lId && i.variant_id === vId);
    },
    addItem(listingId, variantId = null) {
        const state = fnReadCompareStorage();
        const lId = Number(listingId);
        const vId = variantId ? Number(variantId) : null;
        if (state.items.some(i => i.listing_id === lId && i.variant_id === vId)) {
            return 'duplicate';
        }
        if (state.items.length >= this.maxItems) {
            return 'max_reached';
        }
        state.items.push({ listing_id: lId, variant_id: vId });
        fnWriteCompareStorage(state);
        return 'added';
    },
    removeItem(listingId, variantId = null) {
        const state = fnReadCompareStorage();
        const lId = Number(listingId);
        const vId = variantId ? Number(variantId) : null;
        state.items = state.items.filter(i => !(i.listing_id === lId && i.variant_id === vId));
        fnWriteCompareStorage(state);
    },
    clear() {
        fnWriteCompareStorage(fnCompareEmptyState());
    }
};

/* ── Toast Notifications ── */
function fnShowToast(message, type = 'success', actionUrl = null, actionLabel = null) {
    let container = document.getElementById('fn-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'fn-toast-container';
        container.className = 'fixed z-[200] top-5 right-5 left-5 sm:left-auto flex flex-col gap-2.5 items-end pointer-events-none';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'fn-toast-item pointer-events-auto w-auto min-w-[260px] max-w-sm rounded-2xl bg-white border border-slate-200/90 shadow-xl px-4 py-3 flex items-center justify-between gap-4 transition-all duration-200';

    let iconHtml = '';
    let iconClass = 'border-emerald-200 bg-emerald-50 text-emerald-600';

    if (type === 'warning') {
        iconClass = 'border-amber-200 bg-amber-50 text-amber-600';
        iconHtml = '<i class="fa-solid fa-triangle-exclamation text-xs"></i>';
    } else if (type === 'danger' || type === 'error') {
        iconClass = 'border-rose-200 bg-rose-50 text-rose-600';
        iconHtml = '<i class="fa-solid fa-xmark text-sm"></i>';
    } else {
        iconHtml = '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
    }

    let linkHtml = '';
    if (actionUrl) {
        linkHtml = `
            <div class="mt-0.5">
                <a href="${actionUrl}" class="font-bold underline text-slate-900 hover:text-emerald-600 inline-flex items-center gap-1 transition-colors">
                    <span>${actionLabel || 'compare'}</span>
                    <i class="fa-solid fa-arrow-right-arrow-left text-[10px]"></i>
                </a>
            </div>
        `;
    }

    toast.innerHTML = `
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 border-2 ${iconClass}">
                ${iconHtml}
            </div>
            <div class="text-xs sm:text-sm text-slate-700 font-medium leading-snug">
                <div>${message}</div>
                ${linkHtml}
            </div>
        </div>
        <button type="button" class="text-slate-300 hover:text-slate-500 transition-colors p-1 -mr-1 text-sm shrink-0 cursor-pointer" aria-label="Close notification">
            <i class="fa-solid fa-xmark"></i>
        </button>
    `;

    const closeBtn = toast.querySelector('button');
    let removeTimer = null;

    function dismiss() {
        if (removeTimer) clearTimeout(removeTimer);
        toast.classList.add('fn-toast-hiding');
        setTimeout(() => toast.remove(), 200);
    }

    closeBtn?.addEventListener('click', dismiss);
    removeTimer = setTimeout(dismiss, 2500);

    container.appendChild(toast);
}

/* ── Toggle Compare ── */
function fnToggleCompare(listingId, variantId = null) {
    const lId = Number(listingId);
    if (!lId) return;

    const isActive = EdushopifyCompare.contains(lId, variantId);
    if (isActive) {
        EdushopifyCompare.removeItem(lId, variantId);
        fnShowToast('Product removed from', 'info', '/compare', 'compare');
    } else {
        const result = EdushopifyCompare.addItem(lId, variantId);
        if (result === 'added') {
            fnShowToast('Product added to', 'success', '/compare', 'compare');
        } else if (result === 'duplicate') {
            fnShowToast('This product is already in', 'info', '/compare', 'compare');
        } else if (result === 'max_reached') {
            fnShowToast(`You can compare up to ${EdushopifyCompare.maxItems} products in`, 'warning', '/compare', 'compare');
        }
    }

    fnSyncAllCompareButtons();
}

/* ── Synchronize All Compare Buttons on the Page ── */
function fnSyncAllCompareButtons() {
    const buttons = document.querySelectorAll('[data-compare-id]');
    buttons.forEach(btn => {
        const id = Number(btn.dataset.compareId);
        const active = EdushopifyCompare.contains(id);
        const style = btn.dataset.style || 'icon';

        if (style === 'icon') {
            if (active) {
                btn.classList.add('active', 'compare-active');
                btn.setAttribute('title', 'Remove from comparison');
                btn.setAttribute('aria-label', 'Remove from comparison');
            } else {
                btn.classList.remove('active', 'compare-active');
                btn.setAttribute('title', 'Add to compare');
                btn.setAttribute('aria-label', 'Add to compare');
            }
        } else {
            // style === 'text'
            const labelEl = btn.querySelector('.fn-compare-label');
            const iconEl = btn.querySelector('i');
            if (active) {
                btn.classList.add('active', 'compare-active');
                btn.setAttribute('title', 'Remove from comparison');
                if (labelEl) labelEl.textContent = 'Remove from Compare';
                if (iconEl) iconEl.className = 'fa-solid fa-arrow-right-arrow-left text-xs transition-transform duration-200';
            } else {
                btn.classList.remove('active', 'compare-active');
                btn.setAttribute('title', 'Add to compare');
                if (labelEl) labelEl.textContent = 'Add to Compare';
                if (iconEl) iconEl.className = 'fa-solid fa-arrow-right-arrow-left text-xs transition-transform duration-200';
            }
        }
    });

    // Update all compare badges across the DOM
    const count = EdushopifyCompare.count();
    document.querySelectorAll('[data-compare-badge], #fn-nav-compare-badge, #fn-mobile-compare-badge').forEach(badge => {
        badge.textContent = count;
        if (count > 0) {
            badge.classList.remove('hidden');
            badge.classList.add('inline-flex');
        } else {
            badge.classList.add('hidden');
            badge.classList.remove('inline-flex');
        }
    });
}

window.addEventListener('compare:changed', fnSyncAllCompareButtons);
window.addEventListener('storage', function(e) {
    if (e.key === COMPARE_STORAGE_KEY) fnSyncAllCompareButtons();
});
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', fnSyncAllCompareButtons);
} else {
    fnSyncAllCompareButtons();
}

window.EdushopifyCompare = EdushopifyCompare;
window.fnToggleCompare = fnToggleCompare;
window.fnSyncAllCompareButtons = fnSyncAllCompareButtons;
window.fnShowToast = fnShowToast;

/* ═══════════════════════════════════════════════════════════════════════════
   Supplier Save / Favorite System (Vanilla JS, live count, toast feedback)
   ═══════════════════════════════════════════════════════════════════════════ */

function fnSyncSupplierSaveButtons(identifier, isSaved, count) {
    if (!identifier) return;
    const selector = `[data-supplier-account-id="${identifier}"], [data-supplier-slug="${identifier}"]`;
    document.querySelectorAll(selector).forEach(b => {
        b.dataset.saved = isSaved ? '1' : '0';
        b.dataset.savesCount = count;

        const icon = b.querySelector('.fn-fav-icon');
        const countSpan = b.querySelector('.fn-fav-count');

        if (isSaved) {
            b.classList.add('is-saved');
            b.classList.remove('text-gray-400', 'text-gray-700');

            if (icon) {
                icon.className = 'fn-fav-icon fa-solid fa-heart text-xs text-rose-500 transition-transform duration-200 animate-fav-pop';
            }
            if (countSpan) {
                countSpan.textContent = count;
                countSpan.classList.remove('hidden', 'text-gray-700');
                countSpan.classList.add('text-rose-600');
            }
            b.setAttribute('title', `Saved to your favorites (${count})`);
            b.setAttribute('aria-label', `Remove from favorites (${count})`);
        } else {
            b.classList.remove('is-saved');

            if (icon) {
                icon.className = 'fn-fav-icon fa-regular fa-heart text-xs text-gray-400 group-hover/fav:text-rose-500 transition-transform duration-200';
            }
            if (countSpan) {
                countSpan.textContent = count;
                countSpan.classList.remove('text-rose-600');
                countSpan.classList.add('text-gray-700');
                if (count > 0) {
                    countSpan.classList.remove('hidden');
                } else {
                    countSpan.classList.add('hidden');
                }
            }
            b.setAttribute('title', `Save supplier (${count})`);
            b.setAttribute('aria-label', `Save supplier to favorites (${count})`);
        }
    });
}

async function fnToggleSupplierSave(btn) {
    if (!btn) return;

    // Check authentication from body data attribute
    const isAuthed = document.body.dataset.authed === '1';
    const saveUrl = btn.dataset.saveUrl;
    const supplierSlug = btn.dataset.supplierSlug;
    const supplierAccountId = btn.dataset.supplierAccountId;
    const identifier = supplierAccountId || supplierSlug;

    if (!isAuthed) {
        const returnUrl = window.location.href;
        const loginUrl = '/login?redirect=' + encodeURIComponent(returnUrl);
        if (window.fnShowToast) {
            window.fnShowToast('Please sign in to save suppliers to your favorites', 'info', loginUrl, 'Sign In');
        }
        setTimeout(() => {
            window.location.href = loginUrl;
        }, 900);
        return;
    }

    if (btn.dataset.loading === '1') return;
    btn.dataset.loading = '1';

    const wasSaved = btn.dataset.saved === '1';
    const currentCount = parseInt(btn.dataset.savesCount, 10) || 0;
    const newSaved = !wasSaved;
    const newCount = Math.max(0, currentCount + (newSaved ? 1 : -1));

    // Optimistic UI update across all matching buttons on the page
    fnSyncSupplierSaveButtons(identifier, newSaved, newCount);

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const res = await fetch(saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ slug: supplierSlug })
        });

        const data = await res.json();

        if (res.status === 401 || (!data.success && data.redirect)) {
            window.location.href = data.redirect || '/login';
            return;
        }

        if (data.success) {
            fnSyncSupplierSaveButtons(identifier, data.saved, data.saves_count);
            if (window.fnShowToast) {
                window.fnShowToast(data.message, data.saved ? 'success' : 'info');
            }
        } else {
            // Rollback optimistic change on error
            fnSyncSupplierSaveButtons(identifier, wasSaved, currentCount);
            if (window.fnShowToast) {
                window.fnShowToast(data.message || 'Could not update saved supplier', 'error');
            }
        }
    } catch (err) {
        console.error('Error toggling supplier save:', err);
        fnSyncSupplierSaveButtons(identifier, wasSaved, currentCount);
        if (window.fnShowToast) {
            window.fnShowToast('Network error, please try again', 'error');
        }
    } finally {
        btn.dataset.loading = '0';
    }
}

window.fnToggleSupplierSave = fnToggleSupplierSave;
window.fnSyncSupplierSaveButtons = fnSyncSupplierSaveButtons;

/* ═══════════════════════════════════════════════════════════════════════════
   Product / Listing Save / Favorite System (Vanilla JS, live count, toast)
   ═══════════════════════════════════════════════════════════════════════════ */

function fnSyncListingSaveButtons(identifier, isSaved, count) {
    if (!identifier) return;
    const selector = `[data-listing-id="${identifier}"], [data-listing-slug="${identifier}"]`;
    document.querySelectorAll(selector).forEach(b => {
        b.dataset.saved = isSaved ? '1' : '0';
        b.dataset.savesCount = count;

        const icon = b.querySelector('.fn-fav-icon');
        const countSpan = b.querySelector('.fn-fav-count');
        const labelSpan = b.querySelector('.fn-save-label');

        if (isSaved) {
            b.classList.add('is-saved');
            b.classList.remove('text-gray-400', 'text-gray-600', 'text-gray-700', 'hover:text-gray-900', 'hover:border-gray-300');

            if (icon) {
                icon.className = 'fn-fav-icon fa-solid fa-heart text-rose-500 transition-transform duration-200 animate-fav-pop ' + (labelSpan ? 'text-sm' : 'text-xs');
            }
            if (labelSpan) {
                labelSpan.textContent = 'Saved';
            }
            if (countSpan) {
                countSpan.textContent = labelSpan ? `(${count})` : count;
                countSpan.classList.remove('hidden', 'text-gray-700', 'text-gray-600');
                countSpan.classList.add('text-rose-600');
            }
            b.setAttribute('title', `Saved to your favorites (${count})`);
            b.setAttribute('aria-label', `Remove from favorites (${count})`);
        } else {
            b.classList.remove('is-saved');

            if (icon) {
                icon.className = 'fn-fav-icon fa-regular fa-heart text-gray-400 group-hover/fav:text-rose-500 transition-transform duration-200 ' + (labelSpan ? 'text-sm' : 'text-xs');
            }
            if (labelSpan) {
                labelSpan.textContent = 'Save';
            }
            if (countSpan) {
                countSpan.textContent = labelSpan ? `(${count})` : count;
                countSpan.classList.remove('text-rose-600');
                countSpan.classList.add('text-gray-700');
                if (count > 0) {
                    countSpan.classList.remove('hidden');
                } else {
                    countSpan.classList.add('hidden');
                }
            }
            b.setAttribute('title', `Save product (${count})`);
            b.setAttribute('aria-label', `Save product to favorites (${count})`);
        }
    });
}

async function fnToggleListingSave(btn) {
    if (!btn) return;

    const isAuthed = document.body.dataset.authed === '1';
    const saveUrl = btn.dataset.saveUrl;
    const listingSlug = btn.dataset.listingSlug;
    const listingId = btn.dataset.listingId;
    const identifier = listingId || listingSlug;

    if (!isAuthed) {
        const returnUrl = window.location.href;
        const loginUrl = '/login?redirect=' + encodeURIComponent(returnUrl);
        if (window.fnShowToast) {
            window.fnShowToast('Please sign in to save products to your favorites', 'info', loginUrl, 'Sign In');
        }
        setTimeout(() => {
            window.location.href = loginUrl;
        }, 900);
        return;
    }

    if (btn.dataset.loading === '1') return;
    btn.dataset.loading = '1';

    const wasSaved = btn.dataset.saved === '1';
    const currentCount = parseInt(btn.dataset.savesCount, 10) || 0;
    const newSaved = !wasSaved;
    const newCount = Math.max(0, currentCount + (newSaved ? 1 : -1));

    // Optimistic UI update across all matching buttons on the page
    fnSyncListingSaveButtons(identifier, newSaved, newCount);

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const res = await fetch(saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ slug: listingSlug })
        });

        const data = await res.json();

        if (res.status === 401 || (!data.success && data.redirect)) {
            window.location.href = data.redirect || '/login';
            return;
        }

        if (data.success) {
            fnSyncListingSaveButtons(identifier, data.saved, data.saves_count);
            if (window.fnShowToast) {
                window.fnShowToast(data.message, data.saved ? 'success' : 'info');
            }
        } else {
            fnSyncListingSaveButtons(identifier, wasSaved, currentCount);
            if (window.fnShowToast) {
                window.fnShowToast(data.message || 'Could not update saved product', 'error');
            }
        }
    } catch (err) {
        console.error('Error toggling product save:', err);
        fnSyncListingSaveButtons(identifier, wasSaved, currentCount);
        if (window.fnShowToast) {
            window.fnShowToast('Network error, please try again', 'error');
        }
    } finally {
        btn.dataset.loading = '0';
    }
}

window.fnToggleListingSave = fnToggleListingSave;
window.fnSyncListingSaveButtons = fnSyncListingSaveButtons;



