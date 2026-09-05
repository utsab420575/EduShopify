{{-- ── 6. Business Hours ── --}}
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ open: {{ $spAccOpen('hours', false) }} }" x-init="$watch('open', v => localStorage.setItem('sp-acc-hours', v ? '1' : '0'))">
    <button @click="open = !open" type="button"
            class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                <i class="fa-solid fa-clock text-sm"></i>
            </div>
            <div class="text-left">
                <p class="text-sm font-semibold text-gray-900">Business Hours</p>
                <p class="text-xs text-gray-400">Weekly operating schedule</p>
            </div>
        </div>
        <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
    </button>

    <div x-show="open" x-cloak x-transition class="border-t border-gray-100 px-6 py-6"
         x-data="{
             defaultOpen: '09:00', defaultClose: '17:00',
             applyDefaults() {
                 this.$refs.hoursForm.querySelectorAll('[data-is-open]').forEach(cb => {
                     if (cb.checked) {
                         const row = cb.closest('[data-day-row]');
                         row.querySelector('[data-open-time]').value = this.defaultOpen;
                         row.querySelector('[data-close-time]').value = this.defaultClose;
                     }
                 });
             }
         }">

        <div class="flex flex-wrap items-end gap-3 mb-4 p-3 bg-gray-50 rounded-xl border border-gray-100">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Default Open</label>
                <input type="time" x-model="defaultOpen" class="text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 bg-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Default Close</label>
                <input type="time" x-model="defaultClose" class="text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 bg-white">
            </div>
            <button type="button" @click="applyDefaults()" class="text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 hover:bg-amber-100 transition cursor-pointer">
                Apply to all open days
            </button>
        </div>

        <form method="POST" action="{{ route('supplier.company.profile.business-hours.update') }}" x-ref="hoursForm">
            @csrf
            @method('PUT')

            <div class="divide-y divide-gray-100 border border-gray-100 rounded-xl overflow-hidden">
                @foreach($businessHours as $i => $bh)
                    <div class="px-4 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-gray-50"
                         x-data="{ isOpen: {{ $bh['is_open'] ? 'true' : 'false' }} }" data-day-row>
                        <div class="w-32 flex items-center gap-2.5">
                            <input type="hidden" name="days[{{ $i }}][is_open]" value="0">
                            <input type="checkbox" name="days[{{ $i }}][is_open]" value="1" x-model="isOpen" data-is-open id="day_{{ $i }}" style="accent-color:var(--theme-primary)">
                            <label for="day_{{ $i }}" class="text-sm font-semibold text-gray-800 cursor-pointer">{{ $bh['day_name'] }}</label>
                        </div>

                        <div class="flex items-center gap-3" x-show="isOpen">
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs text-gray-400">Open:</span>
                                <input name="days[{{ $i }}][open_time]" value="{{ $bh['open_time'] }}" type="time" data-open-time class="text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 bg-white">
                            </div>
                            <span class="text-gray-300">&ndash;</span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs text-gray-400">Close:</span>
                                <input name="days[{{ $i }}][close_time]" value="{{ $bh['close_time'] }}" type="time" data-close-time class="text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 bg-white">
                            </div>
                        </div>
                        <div class="text-xs text-gray-400 italic" x-show="!isOpen">Closed</div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end mt-5 pt-4 border-t border-gray-100">
                <button type="submit" class="inline-flex items-center gap-2 text-sm font-medium px-5 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                    <i class="fa-solid fa-floppy-disk"></i> Save Business Hours
                </button>
            </div>
        </form>
    </div>
</div>
