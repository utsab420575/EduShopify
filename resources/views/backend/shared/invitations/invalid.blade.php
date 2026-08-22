<x-layouts.auth title="Invitation">
    <div class="text-center">
        <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-circle-exclamation text-red-500 text-xl"></i>
        </div>
        <h1 class="text-lg font-bold text-gray-900">This invitation link is no longer valid</h1>
        <p class="text-sm text-gray-500 mt-2">It may have expired, already been used, or been cancelled by the organization. Please ask the organization owner to send a new invitation.</p>
        <a href="{{ url('/') }}" class="inline-block mt-6 text-sm font-medium text-brand-600 hover:text-brand-700">Return home</a>
    </div>
</x-layouts.auth>
