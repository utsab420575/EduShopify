<script>
(function () {
    // RFQ-scoped localStorage wrapper. Stores ONLY rfq_id + quotation_ids —
    // never price/supplier/attribute data. Any corruption/wrong-shape/
    // over-max content is silently normalized back to an empty selection,
    // never thrown, never breaks page render.
    window.QuotationCompareStore = {
        maxItems: 5,

        key(rfqId) {
            return 'edushopify_quotation_compare_' + rfqId;
        },

        get(rfqId) {
            const empty = { version: 1, rfq_id: rfqId, quotation_ids: [] };
            try {
                const raw = window.localStorage.getItem(this.key(rfqId));
                if (!raw) return empty;
                const parsed = JSON.parse(raw);
                if (!parsed || typeof parsed !== 'object' || !Array.isArray(parsed.quotation_ids)) return empty;
                const ids = [...new Set(parsed.quotation_ids.map((v) => parseInt(v, 10)).filter((v) => Number.isInteger(v) && v > 0))].slice(0, this.maxItems);
                return { version: 1, rfq_id: rfqId, quotation_ids: ids };
            } catch (e) {
                return empty;
            }
        },

        save(rfqId, ids) {
            try {
                window.localStorage.setItem(this.key(rfqId), JSON.stringify({ version: 1, rfq_id: rfqId, quotation_ids: ids }));
            } catch (e) { /* storage unavailable — selection just won't persist */ }
            window.dispatchEvent(new CustomEvent('quotation-compare:changed', { detail: { rfqId: rfqId, count: ids.length } }));
        },

        count(rfqId) {
            return this.get(rfqId).quotation_ids.length;
        },

        contains(rfqId, quotationId) {
            return this.get(rfqId).quotation_ids.includes(quotationId);
        },

        add(rfqId, quotationId) {
            const state = this.get(rfqId);
            if (state.quotation_ids.includes(quotationId)) return 'duplicate';
            if (state.quotation_ids.length >= this.maxItems) return 'max_reached';
            state.quotation_ids.push(quotationId);
            this.save(rfqId, state.quotation_ids);
            return 'added';
        },

        remove(rfqId, quotationId) {
            const state = this.get(rfqId);
            this.save(rfqId, state.quotation_ids.filter((id) => id !== quotationId));
        },

        clear(rfqId) {
            try { window.localStorage.removeItem(this.key(rfqId)); } catch (e) { /* noop */ }
            window.dispatchEvent(new CustomEvent('quotation-compare:changed', { detail: { rfqId: rfqId, count: 0 } }));
        },
    };

    function toast(icon, title) {
        if (typeof Swal === 'undefined') return;
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon,
            title: title,
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
        });
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('compareCheckbox', (rfqId, quotationId) => ({
            checked: false,
            init() {
                QuotationCompareStore.maxItems = this.$root.dataset.maxItems ? parseInt(this.$root.dataset.maxItems, 10) : QuotationCompareStore.maxItems;
                this.checked = QuotationCompareStore.contains(rfqId, quotationId);
            },
            toggle() {
                if (this.checked) {
                    QuotationCompareStore.remove(rfqId, quotationId);
                    this.checked = false;
                    return;
                }
                const result = QuotationCompareStore.add(rfqId, quotationId);
                if (result === 'added') {
                    this.checked = true;
                    toast('success', 'Quotation added to comparison.');
                } else if (result === 'duplicate') {
                    this.checked = true;
                    toast('info', 'This quotation is already selected.');
                } else if (result === 'max_reached') {
                    toast('warning', 'You can compare up to ' + QuotationCompareStore.maxItems + ' quotations at a time.');
                }
            },
        }));

        Alpine.data('compareTray', (rfqId, maxItems) => ({
            count: 0,
            maxItems: maxItems,
            init() {
                QuotationCompareStore.maxItems = maxItems;
                this.count = QuotationCompareStore.count(rfqId);
                window.addEventListener('quotation-compare:changed', (e) => {
                    if (e.detail.rfqId === rfqId) this.count = e.detail.count;
                });
            },
        }));

        Alpine.data('quotationComparePage', (rfqId, maxItems, dataUrl) => ({
            loading: true,
            count: 0,
            maxItems: maxItems,
            dataUrl: dataUrl,
            data: null,
            highlightDiffs: true,
            showDiffsOnly: false,
            showAdditional: false,

            init() {
                QuotationCompareStore.maxItems = maxItems;
                this.refresh();
                window.addEventListener('quotation-compare:changed', (e) => {
                    if (e.detail.rfqId === rfqId) this.refresh();
                });
            },

            refresh() {
                const state = QuotationCompareStore.get(rfqId);
                this.count = state.quotation_ids.length;

                if (state.quotation_ids.length === 0) {
                    this.data = null;
                    this.loading = false;
                    return;
                }

                this.loading = true;
                fetch(this.dataUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({ quotation_ids: state.quotation_ids }),
                })
                    .then((r) => r.json())
                    .then((json) => {
                        this.data = json;
                        this.loading = false;

                        if (json.removed_ids && json.removed_ids.length > 0) {
                            const kept = state.quotation_ids.filter((id) => !json.removed_ids.includes(id));
                            QuotationCompareStore.save(rfqId, kept);
                            this.count = kept.length;
                            toast('warning', 'One or more selected quotations are no longer available and were removed from comparison.');
                        }
                    })
                    .catch(() => {
                        this.loading = false;
                        toast('error', 'Could not load comparison data.');
                    });
            },

            remove(quotationId) {
                QuotationCompareStore.remove(rfqId, quotationId);
            },

            clearAll() {
                QuotationCompareStore.clear(rfqId);
            },

            rowDiffers(values) {
                const present = values.filter((v) => v !== null && v !== undefined && v !== '');
                if (present.length <= 1) return present.length !== values.length;
                return new Set(present.map((v) => String(v).toLowerCase().trim())).size > 1 || present.length !== values.length;
            },

            // ── Lookups keyed by quotation_id, used from the template ──
            commercialFor(qid) {
                return (this.data?.commercial?.rows || []).find((r) => r.quotation_id === qid) || {};
            },
            partialFor(qid) {
                return (this.data?.partial || {})[qid] || { quoted_count: 0, total_count: 0, is_full: true };
            },
            addonsFor(qid) {
                return (this.data?.addons || {})[qid] || { items: [], addon_line_total: 0 };
            },
            offersFor(item, qid) {
                return (item.offers || {})[qid] || [];
            },
            offerTypeLabel(type) {
                return { existing_product: 'Existing Supplier Product', custom: 'Custom RFQ Offer', alternative: 'Alternative Offer' }[type] || type;
            },
            attrStatusClass(status) {
                return {
                    match: 'text-emerald-700 bg-emerald-50 border-emerald-200',
                    different: 'text-amber-800 bg-amber-50 border-amber-200',
                    missing: 'text-gray-500 bg-gray-100 border-gray-200',
                    alternative: 'text-blue-700 bg-blue-50 border-blue-200',
                }[status] || 'text-gray-500 bg-gray-100 border-gray-200';
            },
            money(v, currency) {
                if (v === null || v === undefined) return '—';
                return Number(v).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + (currency ? ' ' + currency : '');
            },
            buyerAttrStatuses(item, attributeId) {
                const statuses = [];
                (this.data?.summary || []).forEach((q) => {
                    this.offersFor(item, q.quotation_id).forEach((offer) => {
                        const a = (offer.attributes || []).find((x) => x.attribute_id === attributeId);
                        if (a) statuses.push(a.status);
                    });
                });
                return statuses;
            },
            buyerAttrRowVisible(item, attributeId) {
                if (!this.showDiffsOnly) return true;
                const statuses = this.buyerAttrStatuses(item, attributeId);
                return statuses.length === 0 || statuses.some((s) => s !== 'match');
            },
        }));
    });
})();
</script>
