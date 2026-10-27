{{--
    Public badge settings (display_badge, badge_color) with a live preview of
    the badge exactly as supplier cards / profile heroes render it. Expects
    the enclosing <form> to carry the achievementBadgeForm() x-data, which
    holds name / type / label / color ('' = automatic by type).
    $idPrefix — unique per modal, keeps input ids distinct.
--}}
<div class="rounded-lg border border-gray-200 bg-gray-50/60 p-4 space-y-4">
    <div class="flex items-center justify-between gap-3">
        <p class="text-sm font-semibold text-gray-900">Public Badge</p>
        <div class="flex items-center gap-2">
            <span class="text-[11px] text-gray-400">Preview</span>
            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full whitespace-nowrap" :style="previewStyle()" x-text="previewLabel()"></span>
        </div>
    </div>

    <x-backend.input name="display_badge" label="Display Badge" maxlength="60" x-model="label"
                     placeholder="e.g. Founding" hint="Short text shown on the public badge. Leave empty to use the achievement name." />

    <div>
        <label for="{{ $idPrefix }}_badge_hex" class="block text-sm font-medium text-gray-700 mb-1.5">Badge Color</label>
        <input type="hidden" name="badge_color" :value="color">

        <div class="flex flex-wrap items-center gap-2">
            <input type="color" :value="activeColor()" @input="color = $event.target.value" title="Pick any colour"
                   class="w-10 h-10 p-0.5 rounded-lg border border-gray-300 bg-white cursor-pointer shrink-0">
            <input type="text" id="{{ $idPrefix }}_badge_hex" :value="color" @change="setHex($event.target)" maxlength="7"
                   :placeholder="activeColor() + ' (auto)'"
                   class="w-36 px-3 py-2.5 border border-gray-300 rounded-lg text-sm font-mono text-gray-900 placeholder:text-gray-400 focus-accent">
            <button type="button" @click="color = ''"
                    class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-2.5 rounded-lg border transition"
                    :class="color === '' ? 'border-indigo-500 text-indigo-700 bg-indigo-50' : 'border-dashed border-gray-300 text-gray-600 bg-white hover:bg-gray-50'">
                <i class="fa-solid fa-wand-magic-sparkles text-[10px]"></i> Auto (by type)
            </button>
        </div>

        <div class="flex flex-wrap gap-1.5 mt-3">
            @foreach(\App\Support\BadgeColor::TAILWIND as $label => $hex)
                <button type="button" @click="color = '{{ $hex }}'" title="{{ $label }} ({{ $hex }})"
                        class="w-6 h-6 rounded-full border-2 border-white shadow-xs transition hover:scale-110"
                        style="background:{{ $hex }}"
                        :class="color === '{{ $hex }}' && 'ring-2 ring-offset-1 ring-gray-900'"></button>
            @endforeach
        </div>

        @error('badge_color')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @else
            <p class="mt-1.5 text-xs text-gray-400">Pick any colour, or a Tailwind shade above. Background, border and a readable text shade are derived from it automatically.</p>
        @enderror
    </div>
</div>

@once
@push('scripts')
<script>
    function achievementBadgeForm(initial) {
        const defaults = @json(\App\Models\Achievement::DEFAULT_BADGE_COLORS);
        const HEX = /^#[0-9a-f]{6}$/;

        // JS mirror of App\Support\BadgeColor::palette() — keep in sync.
        function palette(hex) {
            const rgb = [1, 3, 5].map(i => parseInt(hex.substr(i, 2), 16));
            const mix = (white) => rgb.map(c => c * (1 - white) + 255 * white);
            const lum = (c3) => {
                const [r, g, b] = c3.map(c => { c /= 255; return c <= 0.03928 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4; });
                return 0.2126 * r + 0.7152 * g + 0.0722 * b;
            };
            const contrast = (a, b) => { const la = lum(a), lb = lum(b); return (Math.max(la, lb) + 0.05) / (Math.min(la, lb) + 0.05); };
            const toHex = (c3) => '#' + c3.map(c => Math.round(c).toString(16).padStart(2, '0')).join('');

            const bg = mix(0.9);
            let text = rgb;
            for (let i = 0; i < 20 && contrast(text, bg) < 4.5; i++) text = text.map(c => c * 0.88);

            return { bg: toHex(bg), border: toHex(mix(0.65)), text: toHex(text) };
        }

        return Object.assign({ name: '', type: '', label: '', color: '' }, initial, {
            activeColor() {
                return HEX.test(this.color) ? this.color : (defaults[this.type.trim()] || defaults.default);
            },
            setHex(input) {
                let v = input.value.trim().toLowerCase();
                if (v !== '' && v[0] !== '#') v = '#' + v;
                if (v === '' || HEX.test(v)) this.color = v;
                input.value = this.color;
            },
            previewLabel() {
                return this.label.trim() || this.name.trim() || 'Badge';
            },
            previewStyle() {
                const p = palette(this.activeColor());
                return `background:${p.bg};color:${p.text};border:1px solid ${p.border};`;
            },
        });
    }
</script>
@endpush
@endonce
