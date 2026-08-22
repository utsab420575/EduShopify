@extends('backend.layouts.buyer')

@section('title', 'Personal & Security')
@section('breadcrumb', 'Settings / Personal & Security')

@section('body')

    <x-backend.page-header title="Personal & Security" subtitle="Manage your personal login details." />

    <form method="POST" action="{{ route('buyer.settings.security.update') }}" class="space-y-6 max-w-2xl">
        @csrf @method('PUT')

        <x-backend.form-card title="Personal Information">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-backend.input name="name" label="Full Name" required :value="old('name', $user->name)" />
                <x-backend.input type="email" name="email" label="Email" required :value="old('email', $user->email)" />
                <x-backend.input name="phone" label="Phone" :value="old('phone', $user->phone)" />
                <x-backend.select name="currency_code" label="Preferred Currency" placeholder="Default">
                    @foreach($currencies as $currency)
                        <option value="{{ $currency->code }}" @selected(old('currency_code', $user->currency_code) === $currency->code)>{{ $currency->code }} — {{ $currency->name }}</option>
                    @endforeach
                </x-backend.select>
            </div>
        </x-backend.form-card>

        <x-backend.form-card title="Change Password" description="Leave blank to keep your current password.">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-backend.input type="password" name="current_password" label="Current Password" />
                <x-backend.input type="password" name="password" label="New Password" />
                <x-backend.input type="password" name="password_confirmation" label="Confirm New Password" />
            </div>
        </x-backend.form-card>

        <div class="flex justify-end">
            <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg">Save Changes</button>
        </div>
    </form>

@endsection
