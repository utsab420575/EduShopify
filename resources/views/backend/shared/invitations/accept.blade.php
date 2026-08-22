<x-layouts.auth title="Accept Invitation" subtitle="Join your organization on EduShopify">

    <div class="text-center mb-6">
        <div class="w-14 h-14 rounded-full bg-brand-50 flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-user-plus text-brand-600 text-xl"></i>
        </div>
        <h1 class="text-lg font-bold text-gray-900">You've been invited to join {{ $invitation->account->display_name }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $invitation->invited_email }}</p>
    </div>

    <form method="POST" action="{{ route('invitations.accept.submit', $token) }}" class="space-y-4">
        @csrf
        <input type="hidden" name="requires_registration" value="{{ $userExists ? '0' : '1' }}">

        @if($userExists)
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm text-gray-600">
                An account already exists for this email. Please
                <a href="{{ route('login') }}" class="text-brand-600 font-medium hover:text-brand-700">sign in as {{ $invitation->invited_email }}</a>
                first, then return to this link to accept.
            </div>
        @else
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Your Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required value="{{ old('name') }}" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" required class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm Password <span class="text-red-500">*</span></label>
                <input type="password" name="password_confirmation" required class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
        @endif

        <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 rounded-lg transition">
            Accept Invitation
        </button>
    </form>

</x-layouts.auth>
