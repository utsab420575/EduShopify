<x-layouts.app>
    <div class="min-h-[70vh] flex items-center justify-center py-20 px-4">
        <div class="max-w-lg w-full text-center space-y-6">

            {{-- Icon --}}
            <div class="w-20 h-20 mx-auto bg-slate-100 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </div>

            <div class="space-y-2">
                <h1 class="text-3xl font-extrabold text-slate-900 font-display">Payment Cancelled</h1>
                <p class="text-slate-500 text-base">No charges were made. You can try again whenever you're ready.</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-center pt-2">
                <a href="{{ route('supplier.pricing') }}"
                   class="inline-flex items-center justify-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-emerald-500/20 transition-all duration-200 hover:-translate-y-0.5">
                    View Plans
                </a>
                <a href="{{ route('supplier.dashboard') }}"
                   class="inline-flex items-center justify-center px-6 py-3 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all duration-200">
                    Back to Dashboard
                </a>
            </div>

        </div>
    </div>
</x-layouts.app>
