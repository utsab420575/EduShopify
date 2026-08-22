@extends('backend.layouts.supplier')

@section('title', 'Personal & Security Settings')
@section('breadcrumb', 'Settings / Personal & Security')

@section('body')

    <x-backend.page-header title="Personal &amp; Security" subtitle="Update your account credentials and personal preferences." />

    <div class="max-w-2xl space-y-6">

        <x-backend.form-card title="Personal Profile">
            <form method="POST" action="{{ route('supplier.settings.security.update') }}" class="space-y-4">
                @csrf @method('PUT')
                <x-backend.input name="name" label="Full Name" required :value="old('name', $user->name)" />
                <x-backend.input type="email" name="email" label="Email Address" required :value="old('email', $user->email)" />
                <x-backend.input name="phone" label="Phone Number" :value="old('phone', $user->phone)" />

                <div class="pt-4 border-t border-gray-100">
                    <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wide mb-3">Change Password (leave blank to keep current)</h4>
                    <div class="space-y-3">
                        <x-backend.input type="password" name="password" label="New Password" />
                        <x-backend.input type="password" name="password_confirmation" label="Confirm New Password" />
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="btn-primary text-xs font-bold px-5 py-2.5 rounded-lg flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-check"></i> Save Changes
                    </button>
                </div>
            </form>
        </x-backend.form-card>

    </div>

@endsection
