import Alpine from 'alpinejs';
import { registerComparisonAlpine } from './frontend/comparison.js';

Alpine.data('mobileMenu', () => ({
    open: false,
    toggle() {
        this.open = !this.open;
    },
    close() {
        this.open = false;
    },
}));

Alpine.data('categoryMenu', () => ({
    open: false,
    toggle() {
        this.open = !this.open;
    },
    close() {
        this.open = false;
    },
}));

Alpine.data('globalSearch', () => ({
    query: '',
    open: false,
    loading: false,
    results: null,
    activeIndex: -1,
    debounceTimer: null,

    init() {
        this.$watch('query', (value) => {
            clearTimeout(this.debounceTimer);

            if (value.trim().length < 2) {
                this.results = null;
                this.open = false;
                return;
            }

            this.debounceTimer = setTimeout(() => this.fetchResults(value), 300);
        });
    },

    async fetchResults(value) {
        this.loading = true;
        this.open = true;

        try {
            const response = await fetch(`/search/suggestions?q=${encodeURIComponent(value)}`, {
                headers: { Accept: 'application/json' },
            });

            this.results = response.ok ? await response.json() : { groups: [] };
        } catch (e) {
            this.results = { groups: [] };
        } finally {
            this.loading = false;
            this.activeIndex = -1;
        }
    },

    closeOnEscape() {
        this.open = false;
        this.activeIndex = -1;
    },
}));

Alpine.data('variantSelector', (initialVariantId) => ({
    selected: initialVariantId,
    select(id) {
        this.selected = id;
    },
    isSelected(id) {
        return this.selected === id;
    },
}));

Alpine.data('quantityEstimator', (tiers, basePrice) => ({
    quantity: 1,
    tiers: tiers || [],
    basePrice: basePrice || 0,

    get unitPrice() {
        if (!this.tiers.length) {
            return this.basePrice;
        }

        const match = this.tiers.find((tier) => {
            const max = tier.max_quantity === null ? Infinity : tier.max_quantity;
            return this.quantity >= tier.min_quantity && this.quantity <= max;
        });

        return match ? match.unit_price : this.basePrice;
    },

    get subtotal() {
        return this.unitPrice * this.quantity;
    },

    increment() {
        this.quantity = Number(this.quantity) + 1;
    },

    decrement() {
        this.quantity = Math.max(1, Number(this.quantity) - 1);
    },
}));

Alpine.data('marketplaceToast', () => ({
    toasts: [],
    init() {
        const handleToast = (detail) => {
            if (!detail || !detail.message) return;
            this.add(detail.message, detail.type, detail.actionUrl, detail.actionLabel);
        };
        window.addEventListener('toast', (e) => handleToast(e.detail));
        window.addEventListener('compare:toast', (e) => handleToast(e.detail));
        window.marketplaceToast = (msg, type = 'success', url = null, label = null) => {
            this.add(msg, type, url, label);
        };
    },
    add(message, type = 'success', actionUrl = null, actionLabel = null) {
        if (!message) return;
        const now = Date.now();
        // Prevent duplicate toast with identical message within 600ms
        if (this.toasts.some((t) => t.message === message && (now - t.createdAt) < 600)) {
            return;
        }
        const id = now + Math.random();
        this.toasts.push({
            id,
            createdAt: now,
            message,
            type: type || 'success',
            actionUrl: actionUrl || null,
            actionLabel: actionLabel || null,
        });
        setTimeout(() => this.remove(id), 2000);
    },
    push(message, type = 'success', actionUrl = null, actionLabel = null) {
        this.add(message, type, actionUrl, actionLabel);
    },
    remove(id) {
        this.toasts = this.toasts.filter((t) => t.id !== id);
    },
}));

registerComparisonAlpine(Alpine);

window.Alpine = Alpine;
Alpine.start();
