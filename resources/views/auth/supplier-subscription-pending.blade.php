<x-layouts.auth title="Subscription Pending">

    <div class="text-center">
        <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-5">
            <svg class="w-8 h-8 text-indigo-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <h1 class="text-xl font-bold text-gray-900 mb-2">Confirming Your Subscription</h1>
        <p class="text-gray-500 text-sm leading-relaxed mb-6">
            We're waiting for your payment to be confirmed. This usually only takes a moment —
            you'll be moved to your dashboard automatically once it's done.
        </p>

        <div class="p-4 bg-indigo-50 border border-indigo-100 rounded-xl text-left mb-6">
            <p class="text-xs text-indigo-800">
                If you completed checkout and this message doesn't clear within a few minutes,
                please refresh this page or contact
                <a href="mailto:support@edushopify.test" class="font-semibold hover:underline">support@edushopify.test</a>.
            </p>
        </div>

        <a href="{{ route('supplier.pricing') }}"
            class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl transition text-sm mb-3">
            Back to Plans
        </a>

        <div class="pt-3 border-t border-gray-100">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-400 hover:text-gray-600 transition">Sign out</button>
            </form>
        </div>
    </div>

</x-layouts.auth>
