{{-- ── 7. Exhibitions ── --}}
<div id="sp-section-exhibitions" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ open: {{ $spAccOpen('exhibitions', false) }} }" x-init="$watch('open', v => localStorage.setItem('sp-acc-exhibitions', v ? '1' : '0'))">
    <button @click="open = !open" type="button"
            class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                <i class="fa-solid fa-calendar-star text-sm"></i>
            </div>
            <div class="text-left">
                <p class="text-sm font-semibold text-gray-900">Exhibitions</p>
                <p class="text-xs text-gray-400">{{ $participating->count() > 0 ? 'Participating in '.$participating->count().' exhibition'.($participating->count() > 1 ? 's' : '') : 'Trade shows and educational events' }}</p>
            </div>
        </div>
        <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
    </button>

    <div x-show="open" x-cloak x-transition class="border-t border-gray-100 px-6 py-6">

        @include('backend.supplier.company.partials._section-errors', ['section' => 'exhibitions'])

        @if($participating->isNotEmpty())
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Your Current Exhibitions</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                @foreach($participating as $exhibition)
                    @php($pivot = $exhibition->supplierAccounts->first()?->pivot)
                    <div class="bg-gray-50 rounded-xl border border-indigo-200 p-4 space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <h4 class="text-sm font-bold text-gray-900">{{ $exhibition->getTranslation('name', app()->getLocale()) }}</h4>
                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-full px-2 py-0.5 shrink-0">
                                <i class="fa-solid fa-circle-check"></i> Joined
                            </span>
                        </div>
                        @if($pivot?->booth_number)
                            <p class="text-xs text-gray-600">Booth: {{ $pivot->booth_number }}</p>
                        @endif
                        @if($pivot?->participation_year)
                            <p class="text-xs text-gray-600">Year: {{ $pivot->participation_year }}</p>
                        @endif
                        <form method="POST" action="{{ route('supplier.company.profile.exhibitions.leave', $exhibition) }}" onsubmit="return confirmSwal(this, 'Withdraw from this exhibition?', '', 'warning', 'Yes, withdraw')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-700 cursor-pointer">
                                <i class="fa-solid fa-right-from-bracket mr-1"></i> Withdraw
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif

        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Available Exhibitions</p>
        @if($available->isEmpty())
            <p class="text-sm text-gray-400 text-center py-4">No new exhibitions available right now.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($available as $exhibition)
                    <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-2" x-data="{ joining: false }">
                        <h4 class="text-sm font-bold text-gray-900">{{ $exhibition->getTranslation('name', app()->getLocale()) }}</h4>
                        @if($exhibition->description)
                            <p class="text-xs text-gray-600 line-clamp-2">{{ $exhibition->description }}</p>
                        @endif
                        <button type="button" @click="joining = !joining" class="text-xs font-semibold text-purple-700 bg-purple-50 border border-purple-200 rounded-lg px-3 py-1.5 hover:bg-purple-100 transition cursor-pointer">
                            <i class="fa-solid fa-plus mr-1"></i> Join Exhibition
                        </button>

                        <div x-show="joining" x-transition x-cloak class="pt-2 border-t border-gray-100 space-y-2">
                            <form method="POST" action="{{ route('supplier.company.profile.exhibitions.join', $exhibition) }}" class="space-y-2">
                                @csrf
                                <input name="booth_number" type="text" placeholder="Booth number (optional)"
                                       class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-purple-300 outline-none">
                                <input name="participation_year" type="number" placeholder="Participation year" value="{{ now()->year }}"
                                       class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-purple-300 outline-none">
                                <button type="submit" class="w-full text-xs font-bold px-3 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                                    Confirm Join
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
