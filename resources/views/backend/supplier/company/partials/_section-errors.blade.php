{{--
    Per-section validation errors. The Business Profile page has 10
    independent accordion sections on one URL, several sharing field names
    (e.g. "title" on both the Video and Service forms) — a single global
    "Please fix the following" box (as used by the shared
    backend.layouts.partials.shared._flash on every other backend page)
    can't tell the visitor which section a given error belongs to. Every
    Company\* Form Request extends ProfileSectionFormRequest, which
    guarantees a validation failure always redirects back with this exact
    section's `?section=` key, so $openSection reliably identifies which
    section's errors these are.
--}}
@if(($openSection ?? null) === $section && $errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
        <p class="font-medium mb-1">Please fix the following:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
