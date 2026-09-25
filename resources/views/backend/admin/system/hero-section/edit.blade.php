@extends('backend.layouts.admin')

@section('title', 'Hero Section')
@section('breadcrumb', 'UI Settings / Hero Section')

@section('body')

    <x-backend.page-header title="Hero Section" subtitle="Content shown at the top of the public homepage." />

    <form method="POST" action="{{ route('admin.system.hero-section.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf @method('PUT')

        <x-backend.form-card title="Background Image">
            <div class="flex items-center gap-4">
                <img id="hero-image-preview"
                     src="{{ asset('storage/' . $imagePath) }}"
                     class="w-40 h-24 rounded-lg object-cover border border-gray-200 bg-gray-50" alt="">
                <div class="flex-1">
                    <input type="file" name="image" accept="image/*" onchange="previewHeroImage(this)"
                           class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="text-xs text-gray-400 mt-1">JPG, PNG or WEBP, up to 5MB. Leave empty to keep the current image.</p>
                    @error('image') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </x-backend.form-card>

        <x-backend.form-card title="Text">
            <div class="space-y-4">
                <div>
                    <label for="heading" class="block text-sm font-medium text-gray-700 mb-1.5">Heading <span class="text-red-500">*</span></label>
                    <textarea name="heading" id="heading" rows="2" required
                              class="w-full px-3 py-2.5 border rounded-lg text-sm text-gray-900 transition {{ $errors->has('heading') ? 'border-red-400 bg-red-50' : 'border-gray-300 focus-accent' }}">{{ old('heading', $heading) }}</textarea>
                    <p class="mt-1.5 text-xs text-gray-400">Line breaks are preserved as shown on the homepage.</p>
                    @error('heading') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <x-backend.input name="subheading" label="Subheading" required :value="old('subheading', $subheading)" />
            </div>
        </x-backend.form-card>

        <x-backend.form-card title="Buttons">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-backend.input name="primary_button_text" label="Primary Button Text" required :value="old('primary_button_text', $primaryButtonText)" />
                <x-backend.input name="primary_button_url" label="Primary Button URL" required :value="old('primary_button_url', $primaryButtonUrl)" />
                <x-backend.input name="secondary_button_text" label="Secondary Button Text" required :value="old('secondary_button_text', $secondaryButtonText)" />
                <x-backend.input name="secondary_button_url" label="Secondary Button URL" required :value="old('secondary_button_url', $secondaryButtonUrl)" />
            </div>
        </x-backend.form-card>

        <div class="flex items-center justify-end gap-2 bg-white rounded-xl border border-gray-200 p-4">
            <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg">Save Hero Section</button>
        </div>
    </form>

    <script>
        function previewHeroImage(input) {
            const file = input.files && input.files[0];
            if (!file) return;

            try {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('hero-image-preview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            } catch (e) {}
        }
    </script>

@endsection
