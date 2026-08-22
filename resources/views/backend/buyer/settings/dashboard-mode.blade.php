@extends('backend.layouts.buyer')

@section('title', 'Dashboard Mode')
@section('breadcrumb', 'Settings / Dashboard Mode')

@section('body')

    <x-backend.page-header title="Dashboard Mode" subtitle="Choose which dashboard opens by default when you sign in." />

    <form method="POST" action="{{ route('buyer.settings.dashboard-mode.update') }}" class="max-w-xl">
        @csrf @method('PUT')
        <x-backend.form-card>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex flex-col items-center text-center gap-2 rounded-lg border-2 px-3 py-4 cursor-pointer transition"
                       style="{{ $current === 'buyer' ? 'border-color:var(--theme-primary); background:var(--theme-primary-soft)' : 'border-color:#e5e7eb' }}">
                    <i class="fa-solid fa-cart-shopping text-lg" style="color:var(--theme-primary)"></i>
                    <span class="text-xs font-semibold" style="color:var(--theme-primary)">Buyer</span>
                    <input type="radio" name="default_mode" value="buyer" class="hidden" @checked($current === 'buyer')>
                </label>
                <label class="flex flex-col items-center text-center gap-2 rounded-lg border-2 border-gray-200 bg-white px-3 py-4 cursor-pointer hover:border-gray-300 transition">
                    <i class="fa-solid fa-store text-lg text-gray-400"></i>
                    <span class="text-xs font-medium text-gray-600">Supplier</span>
                    <input type="radio" name="default_mode" value="supplier" class="hidden" @checked($current === 'supplier')>
                </label>
            </div>
            <p class="text-xs text-gray-400 mt-4">Changing your default dashboard does not change what you're authorized to do — that's always based on your active capabilities and permissions.</p>
            <div class="flex justify-end mt-4">
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Save</button>
            </div>
        </x-backend.form-card>
    </form>

@endsection
