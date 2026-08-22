@extends('backend.layouts.supplier')

@section('title', 'Close Account')
@section('breadcrumb', 'Settings / Close Account')

@section('body')

    <x-backend.page-header title="Close Account" subtitle="Request closure and deletion of this supplier business account." />

    @if($blockers->isNotEmpty())
        <div class="bg-white rounded-xl border border-amber-200 p-5 max-w-xl">
            <p class="text-sm font-semibold text-gray-900">You have outstanding items that must be resolved first:</p>
            <ul class="list-disc list-inside text-sm text-gray-600 mt-2 space-y-1">
                @foreach($blockers as $blocker)
                    <li>{{ ucfirst($blocker) }}</li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="max-w-xl">
            <x-backend.form-card title="Account Closure Request">
                <p class="text-xs text-red-600 mb-4 leading-relaxed">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    <strong>Warning:</strong> Closing your supplier account will delist all catalog products, cancel pending quotations, and revoke active team member access.
                </p>

                <form method="POST" action="{{ route('supplier.settings.close-account.request') }}" class="space-y-4">
                    @csrf
                    <x-backend.textarea name="reason" label="Reason for Closing Account" required placeholder="Help us understand why you are closing your account..." />

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Type <strong>DELETE</strong> to confirm <span class="text-red-500">*</span></label>
                        <input type="text" name="confirmation" required placeholder="DELETE" class="w-full text-sm rounded-lg border border-red-300 px-3 py-2 bg-white">
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-lg bg-red-600 text-white text-xs font-bold hover:bg-red-700 shadow-sm">
                        Submit Closure Request
                    </button>
                </form>
            </x-backend.form-card>
        </div>
    @endif

@endsection
