@extends('backend.layouts.buyer')

@section('title', 'Close Account')
@section('breadcrumb', 'Settings / Close Account')

@section('body')

    <x-backend.page-header title="Close Account" subtitle="Permanently close your EduShopify account." />

    @if($blockers->isNotEmpty())
        <div class="bg-white rounded-xl border border-amber-200 p-5 max-w-2xl">
            <p class="text-sm font-semibold text-gray-900">You have outstanding items that must be resolved first:</p>
            <ul class="list-disc list-inside text-sm text-gray-600 mt-2 space-y-1">
                @foreach($blockers as $blocker)
                    <li>{{ ucfirst($blocker) }}</li>
                @endforeach
            </ul>
        </div>
    @else
        <form method="POST" action="{{ route('buyer.settings.close-account.request') }}" class="max-w-2xl" onsubmit="return confirm('Are you sure you want to close your account?');">
            @csrf
            <x-backend.form-card title="Close Your Account">
                <p class="text-sm text-gray-600 mb-4">This will submit a closure request. Your transactional history (RFQs, quotations, awards, purchase orders, reviews) will be preserved.</p>
                <x-backend.textarea name="reason" label="Reason for closing your account" required />
                <div class="flex justify-end mt-4">
                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Request Account Closure</button>
                </div>
            </x-backend.form-card>
        </form>
    @endif

@endsection
