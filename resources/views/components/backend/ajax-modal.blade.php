@props(['id', 'width' => 'max-w-3xl'])

<div
    x-data="{
        open: false,
        loading: false,
        html: '',
        rowId: null,
        queueKey: null,
        panelUrl: null,
        showUrl: null,
        async load(url, rowId, queueKey, showUrl) {
            this.panelUrl = url;
            this.rowId = rowId;
            this.queueKey = queueKey;
            this.showUrl = showUrl;
            this.open = true;
            this.loading = true;
            this.html = '';
            try {
                const res = await fetch(url, { headers: { 'Accept': 'text/html' } });
                this.html = res.ok ? await res.text() : '<p class=\'p-6 text-sm text-red-600\'>Failed to load this item. Please close and try again.</p>';
            } catch (e) {
                this.html = '<p class=\'p-6 text-sm text-red-600\'>Network error while loading. Please close and try again.</p>';
            }
            this.loading = false;
        },
        async reload() {
            if (! this.panelUrl) return;
            this.loading = true;
            try {
                const res = await fetch(this.panelUrl, { headers: { 'Accept': 'text/html' } });
                if (res.ok) this.html = await res.text();
            } catch (e) {}
            this.loading = false;
        },
        close() {
            this.open = false;
            this.html = '';
            this.panelUrl = null;
            this.rowId = null;
            this.queueKey = null;
            this.showUrl = null;
        },
        clearFieldErrors(form) {
            form.querySelectorAll('[data-field-error]').forEach(el => el.remove());
            form.querySelectorAll('.field-error-border').forEach(el => el.classList.remove('field-error-border', 'border-red-400'));
        },
        showFieldErrors(form, errors) {
            Object.entries(errors || {}).forEach(([field, messages]) => {
                const input = form.querySelector(`[name='${field}']`);
                if (! input) return;
                input.classList.add('field-error-border', 'border-red-400');
                const p = document.createElement('p');
                p.setAttribute('data-field-error', '');
                p.className = 'text-xs text-red-600 mt-1';
                p.textContent = messages[0];
                input.insertAdjacentElement('afterend', p);
            });
        },
        toast(icon, message) {
            window.dispatchEvent(new CustomEvent('approval-toast', { detail: { icon, message } }));
        },
        async handleSubmit(event) {
            event.preventDefault();
            const form = event.target;
            const btn = form.querySelector('button[type=submit]');
            this.clearFieldErrors(form);
            if (btn) {
                btn.dataset.origHtml = btn.dataset.origHtml || btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class=\'fa-solid fa-spinner fa-spin\'></i>';
            }
            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'Accept': 'application/json' },
                });
                const data = await res.json().catch(() => null);
                if (res.ok && data && data.success) {
                    this.toast('success', data.message || 'Done.');
                    if (data.resolved) {
                        window.dispatchEvent(new CustomEvent('approval-item-resolved', { detail: { rowId: this.rowId, queueKey: this.queueKey } }));
                        this.close();
                        return;
                    }
                    await this.reload();
                } else if (res.status === 422 && data) {
                    this.showFieldErrors(form, data.errors || {});
                    if (data.message) this.toast('error', data.message);
                } else {
                    this.toast('error', (data && data.message) || 'Something went wrong. Please try again.');
                }
            } catch (e) {
                this.toast('error', 'Network error. Please try again.');
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = btn.dataset.origHtml;
                }
            }
        },
    }"
    x-init="
        window.addEventListener('open-ajax-modal-{{ $id }}', (e) => load(e.detail.url, e.detail.rowId, e.detail.queueKey, e.detail.showUrl));
        window.addEventListener('approval-toast', (e) => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: e.detail.icon,
                title: e.detail.message,
                showConfirmButton: false,
                timer: e.detail.icon === 'error' ? 4500 : 3500,
                timerProgressBar: true,
            });
        });
    "
    x-show="open"
    x-cloak
    @keydown.escape.window="open && close()"
    class="fixed inset-0 z-50 flex items-center justify-center px-4"
>
    <div x-show="open" x-transition.opacity @click="close()" class="fixed inset-0 bg-gray-900/40"></div>

    <div x-show="open" x-transition class="relative bg-white rounded-xl border border-gray-200 shadow-lg w-full {{ $width }} max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 z-10 flex items-center justify-end gap-1 px-3 py-2 border-b border-gray-100 bg-white/95 backdrop-blur">
            <a :href="showUrl"
               class="text-xs font-medium text-gray-500 hover:text-indigo-600 hover:bg-gray-50 flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg transition-colors"
               title="Open this review in its own page">
                <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i> Open Full Page
            </a>
            <button type="button" @click="close()" class="text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-full w-7 h-7 flex items-center justify-center transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div x-show="loading" class="p-12 text-center text-gray-400">
            <i class="fa-solid fa-circle-notch fa-spin text-xl"></i>
        </div>

        <div x-show="!loading" class="p-5" @submit="handleSubmit($event)" x-html="html"></div>
    </div>
</div>
