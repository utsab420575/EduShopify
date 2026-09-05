@props(['supplier', 'isSaved' => false])

@php
    $location = collect([$supplier->city?->name, $supplier->country?->name])->filter()->implode(', ');
    $primaryType = $supplier->account?->supplierTypes?->first();
@endphp

<div class="fe-card fe-card-hover rounded-2xl p-5 flex flex-col h-full">
    <div class="flex items-start gap-3 mb-3">
        <span class="w-14 h-14 rounded-xl flex items-center justify-center shrink-0 text-lg font-bold" style="background:var(--fe-primary-soft);color:var(--fe-primary);font-family:var(--font-display);">
            {{ strtoupper(substr($supplier->display_name, 0, 1)) }}
        </span>
        <div class="min-w-0">
            <div class="flex items-center gap-1.5">
                <a href="{{ route('frontend.suppliers.show', $supplier->slug) }}" class="fe-focus-ring text-sm font-semibold fe-line-clamp-2" style="color:var(--fe-text);">{{ $supplier->display_name }}</a>
            </div>
            <x-frontend::common.badge variant="verified" class="mt-1"><i class="fa-solid fa-circle-check text-[10px]"></i> Verified Supplier</x-frontend::common.badge>
        </div>
    </div>

    @if($location)
        <p class="text-xs mb-1 flex items-center gap-1.5" style="color:var(--fe-text-muted);">
            <i class="fa-solid fa-location-dot text-[10px]"></i> {{ $location }}
        </p>
    @endif

    @if($primaryType)
        <p class="text-xs mb-3" style="color:var(--fe-text-muted);">{{ $primaryType->name }}</p>
    @endif

    @if($supplier->description)
        <p class="text-sm fe-line-clamp-2 mb-3" style="color:var(--fe-text-muted);">{{ $supplier->description }}</p>
    @endif

    <div class="mt-auto pt-3 border-t flex items-center justify-between gap-2" style="border-color:var(--fe-border);"
         x-data="{
            isSaved: {{ $isSaved ? 'true' : 'false' }},
            loading: false,
            async saveSupplier() {
                @guest
                    window.location.href = '{{ route('frontend.handoff.save-supplier', $supplier->slug) }}';
                    return;
                @endguest

                if (this.loading) return;
                this.loading = true;

                try {
                    const res = await fetch('{{ route('buyer.saved-items.toggle') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content
                        },
                        body: JSON.stringify({
                            type: 'supplier',
                            id: {{ $supplier->account_id }},
                            action: 'save'
                        })
                    });

                    const data = await res.json();
                    if (res.ok) {
                        this.isSaved = true;
                        const toastDetail = {
                            message: data.message || 'Supplier is saved',
                            type: 'success',
                            actionUrl: '{{ route('buyer.saved-items.index', ['type' => 'supplier']) }}',
                            actionLabel: 'saved suppliers'
                        };
                        window.dispatchEvent(new CustomEvent('toast', { detail: toastDetail }));
                    } else {
                        const errDetail = {
                            message: data.message || 'Could not save supplier.',
                            type: 'danger'
                        };
                        window.dispatchEvent(new CustomEvent('toast', { detail: errDetail }));
                    }
                } catch (e) {
                    console.error(e);
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            message: 'An error occurred while saving.',
                            type: 'danger'
                        }
                    }));
                } finally {
                    this.loading = false;
                }
            }
         }">
        <x-frontend::marketplace.rating-summary :rating="$supplier->rating" :count="$supplier->reviews_count ?? 0" />
        <div class="flex items-center gap-1.5 shrink-0">
            <button type="button"
                    @click="saveSupplier()"
                    :disabled="loading"
                    class="group fe-focus-ring text-xs font-semibold px-2.5 py-1.5 rounded-lg border flex items-center gap-1.5 transition-all duration-200 cursor-pointer shadow-xs focus:outline-none focus:ring-2 active:scale-[0.98]"
                    :class="isSaved
                        ? 'text-emerald-700 bg-emerald-50 border-emerald-300 hover:bg-emerald-100/70 hover:border-emerald-400 focus:ring-emerald-500/40'
                        : 'bg-white text-slate-700 border-slate-300 hover:bg-emerald-50/70 hover:text-emerald-700 hover:border-emerald-300 focus:ring-emerald-500/40'"
                    title="Save supplier to dashboard">
                <i :class="isSaved ? 'fa-solid fa-bookmark text-emerald-600' : 'fa-regular fa-bookmark text-slate-400 group-hover:text-emerald-600'"
                   class="text-xs transition-transform duration-200 group-hover:scale-110"
                   :class="loading ? 'animate-pulse' : ''"></i>
                <span x-text="isSaved ? 'Saved' : 'Save Supplier'"></span>
            </button>
            <a href="{{ route('frontend.suppliers.show', $supplier->slug) }}"
               class="fe-focus-ring shrink-0 text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-700 transition-all duration-200 hover:bg-slate-50 hover:border-slate-400 hover:text-slate-900 cursor-pointer focus:outline-none focus:ring-2 focus:ring-slate-400/30">
                View Supplier
            </a>
        </div>
    </div>
</div>
