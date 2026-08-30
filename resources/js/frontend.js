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

registerComparisonAlpine(Alpine);

window.Alpine = Alpine;
Alpine.start();
