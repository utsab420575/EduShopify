@extends('backend.layouts.admin')

@section('title', 'Currencies')
@section('breadcrumb', 'Catalog & Taxonomy / Currencies')

@section('body')

<div x-data="{
    modalOpen: false,
    isEdit: false,
    formAction: '',
    formMethod: 'POST',
    form: {
        id: null,
        code: '',
        name: '',
        symbol: '',
        exchange_rate: '1.00000000',
        decimal_places: 2,
        is_active: true
    },
    openCreate() {
        this.isEdit = false;
        this.formAction = '{{ route('admin.catalog.currencies.store') }}';
        this.formMethod = 'POST';
        this.form = {
            id: null,
            code: '',
            name: '',
            symbol: '',
            exchange_rate: '1.00000000',
            decimal_places: 2,
            is_active: true
        };
        this.modalOpen = true;
    },
    openEdit(item) {
        this.isEdit = true;
        this.formAction = '{{ url('admin/catalog/currencies') }}/' + item.id;
        this.formMethod = 'PUT';
        this.form = {
            id: item.id,
            code: item.code,
            name: item.name,
            symbol: item.symbol,
            exchange_rate: item.exchange_rate,
            decimal_places: item.decimal_places,
            is_active: Boolean(item.is_active)
        };
        this.modalOpen = true;
    }
}">

    <x-backend.page-header title="Currencies" subtitle="Manage standard platform currencies, symbols, exchange rates, and base currency defaults.">
        <x-slot:actions>
            <button type="button" @click="openCreate()" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5 shadow-xs">
                <i class="fa-solid fa-plus"></i>
                <span>New Currency</span>
            </button>
        </x-slot:actions>
    </x-backend.page-header>

    <div class="mb-4">
        <form method="GET" action="{{ route('admin.catalog.currencies.index') }}" class="flex items-center gap-3">
            <div class="relative w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search code, name, symbol..."
                       class="w-full text-xs rounded-xl border border-gray-300 pl-8 pr-3 py-2 bg-white focus-accent transition">
            </div>
            @if(request('search'))
                <a href="{{ route('admin.catalog.currencies.index') }}" class="text-xs text-gray-500 hover:text-gray-800">Clear</a>
            @endif
        </form>
    </div>

    <x-backend.table>
        @if($currencies->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-coins" title="No currencies found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Currency Code &amp; Name</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Symbol</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Exchange Rate</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Default</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($currencies as $currency)
                <tr class="hover:bg-gray-50/80 transition-colors">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center font-mono font-bold text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-800">{{ $currency->code }}</span>
                            <span class="font-medium text-gray-900">{{ $currency->name }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-sm font-bold text-gray-900">{{ $currency->symbol }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600 font-mono">{{ $currency->exchange_rate }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        @if($currency->is_default)
                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i class="fa-solid fa-check text-[10px]"></i> Base Default
                            </span>
                        @else
                            <form method="POST" action="{{ route('admin.catalog.currencies.default', $currency) }}"
                                  onsubmit="return confirmSwal(this, 'Set {{ $currency->code }} as Base Currency?', 'This will make {{ addslashes($currency->name) }} the primary platform currency.', 'question', 'Yes, Make Default')">
                                @csrf
                                <button type="submit" class="text-xs font-semibold hover:underline text-indigo-600">Make default</button>
                            </form>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        <x-backend.status-badge :status="$currency->is_active ? 'active' : 'inactive'" />
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            {{-- Edit via Modal --}}
                            <button type="button"
                                    @click="openEdit({{ json_encode([
                                        'id' => $currency->id,
                                        'code' => $currency->code,
                                        'name' => $currency->name,
                                        'symbol' => $currency->symbol,
                                        'exchange_rate' => $currency->exchange_rate,
                                        'decimal_places' => $currency->decimal_places,
                                        'is_active' => (bool)$currency->is_active,
                                    ]) }})"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:bg-gray-100 transition"
                                    title="Edit Currency">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>

                            @if(!$currency->is_default)
                                {{-- Delete with SweetAlert --}}
                                <form method="POST" action="{{ route('admin.catalog.currencies.destroy', $currency) }}"
                                      onsubmit="return confirmSwal(this, 'Delete Currency {{ $currency->code }}?', 'Are you sure you want to delete {{ addslashes($currency->name) }} ({{ $currency->code }})? This action cannot be undone.', 'warning', 'Yes, Delete')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50 transition" title="Delete Currency">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif
    </x-backend.table>

    {{-- Unified Modal for Add & Edit Currency --}}
    <div x-show="modalOpen"
         x-cloak
         @keydown.escape.window="modalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">

        {{-- Backdrop --}}
        <div x-show="modalOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity"
             @click="modalOpen = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="modalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">

                <form :action="formAction" method="POST">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    {{-- Modal Header --}}
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                        <div>
                            <h3 class="text-base font-bold text-gray-900" x-text="isEdit ? ('Edit Currency (' + form.code + ')') : 'Add New Currency'"></h3>
                            <p class="text-xs text-gray-500 mt-0.5" x-text="isEdit ? 'Update currency symbol, exchange rate or active status' : 'Define an ISO 3-letter currency code and exchange rate'"></p>
                        </div>
                        <button type="button" @click="modalOpen = false" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Currency Code (ISO) <span class="text-red-500">*</span></label>
                                <input type="text" name="code" x-model="form.code" required maxlength="3" placeholder="e.g. USD, BDT"
                                       :readonly="isEdit"
                                       :class="isEdit ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 'bg-white text-gray-900'"
                                       class="w-full text-sm font-mono uppercase rounded-lg border border-gray-300 px-3 py-2 focus-accent transition">
                                <p class="text-[11px] text-gray-400 mt-1" x-show="!isEdit">3-letter standard ISO code.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Currency Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="form.name" required placeholder="e.g. US Dollar, Euro"
                                       class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white text-gray-900 focus-accent transition">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Symbol <span class="text-red-500">*</span></label>
                                <input type="text" name="symbol" x-model="form.symbol" required placeholder="e.g. $, ৳, €"
                                       class="w-full text-sm font-semibold rounded-lg border border-gray-300 px-3 py-2 bg-white text-gray-900 focus-accent transition">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Exchange Rate <span class="text-red-500">*</span></label>
                                <input type="number" step="0.00000001" min="0" name="exchange_rate" x-model="form.exchange_rate" required placeholder="1.00000000"
                                       class="w-full text-sm font-mono rounded-lg border border-gray-300 px-3 py-2 bg-white text-gray-900 focus-accent transition">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Decimals <span class="text-red-500">*</span></label>
                                <input type="number" min="0" max="4" name="decimal_places" x-model="form.decimal_places" required
                                       class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white text-gray-900 focus-accent transition">
                            </div>
                        </div>

                        <div class="pt-2 border-t border-gray-100 flex items-center justify-between">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="rounded text-indigo-600 focus:ring-indigo-500" style="accent-color:var(--theme-primary)">
                                <span class="text-xs font-semibold text-gray-800">Active across platform and catalog</span>
                            </label>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2.5 rounded-b-2xl">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100 rounded-lg transition">Cancel</button>
                        <button type="submit" class="btn-primary text-xs font-semibold px-5 py-2 rounded-lg flex items-center gap-1.5 shadow-xs">
                            <i class="fa-solid fa-check"></i>
                            <span x-text="isEdit ? 'Save Changes' : 'Create Currency'"></span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>

@endsection
