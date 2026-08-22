@extends('backend.layouts.supplier')

@section('title', 'Dashboard Mode Preference')
@section('breadcrumb', 'Settings / Dashboard Mode')

@section('body')

    <x-backend.page-header title="Default Dashboard Mode" subtitle="Choose your default landing dashboard if your account holds both Buyer and Supplier capabilities." />

    <div class="max-w-xl">
        <x-backend.form-card title="Landing Portal Preference">
            <form method="POST" action="{{ route('supplier.settings.dashboard-mode.update') }}" class="space-y-4">
                @csrf @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Default Dashboard on Login</label>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 p-3.5 border rounded-xl cursor-pointer hover:bg-gray-50 {{ $current === 'supplier' ? 'border-indigo-500 bg-indigo-50/30' : 'border-gray-200' }}">
                            <input type="radio" name="default_mode" value="supplier" @checked($current === 'supplier') style="accent-color:var(--theme-primary)">
                            <div>
                                <p class="text-sm font-bold text-gray-900">Supplier Dashboard (Sell &amp; Quote)</p>
                                <p class="text-xs text-gray-400">View RFQ opportunities, quotations, awards, and listings.</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 p-3.5 border rounded-xl cursor-pointer hover:bg-gray-50 {{ $current === 'buyer' ? 'border-indigo-500 bg-indigo-50/30' : 'border-gray-200' }}">
                            <input type="radio" name="default_mode" value="buyer" @checked($current === 'buyer') style="accent-color:var(--theme-primary)">
                            <div>
                                <p class="text-sm font-bold text-gray-900">Buyer Dashboard (Procure &amp; Order)</p>
                                <p class="text-xs text-gray-400">Create RFQs, compare quotations, and issue purchase orders.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="btn-primary text-xs font-bold px-5 py-2.5 rounded-lg flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-check"></i> Save Preference
                    </button>
                </div>
            </form>
        </x-backend.form-card>
    </div>

@endsection
