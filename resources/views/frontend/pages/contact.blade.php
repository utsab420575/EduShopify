@extends('frontend.layouts.master')

@section('title', 'Contact EduShopify')
@section('meta_description', 'Get in touch with the EduShopify team.')

@section('content')
    <div class="fe-container py-10 sm:py-14">
        <x-frontend::navigation.breadcrumbs :items="['Contact' => null]" />

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 max-w-5xl mx-auto">
            <div class="lg:col-span-4">
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight mb-3" style="font-family:var(--font-display);color:var(--fe-text);">Get in touch</h1>
                <p class="text-sm mb-6" style="color:var(--fe-text-muted);">Have a question about procurement, suppliers or your account? Send us a message and our team will respond.</p>

                <div class="space-y-4 text-sm" style="color:var(--fe-text-muted);">
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-circle-question mt-0.5" style="color:var(--fe-primary);"></i>
                        <span>Check our <a href="{{ route('frontend.pages.faqs') }}" class="font-semibold" style="color:var(--fe-primary);">FAQs</a> for quick answers.</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-clock mt-0.5" style="color:var(--fe-primary);"></i>
                        <span>We typically respond within 1–2 business days.</span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-8">
                <div class="fe-card rounded-2xl p-6 sm:p-8">
                    @if(session('success'))
                        <div class="rounded-xl border px-4 py-3 text-sm mb-5 flex items-center gap-2" style="background:var(--fe-success-soft);border-color:var(--fe-success);color:#166534;">
                            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('frontend.pages.contact.submit') }}" class="space-y-4">
                        @csrf
                        <div class="hidden" aria-hidden="true">
                            <label for="fe-contact-website">Leave blank</label>
                            <input type="text" id="fe-contact-website" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="fe-contact-name" class="block text-sm font-medium mb-1.5" style="color:var(--fe-text);">Name <span class="text-red-500">*</span></label>
                                <input type="text" id="fe-contact-name" name="name" value="{{ old('name') }}" required class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm @error('name') border-red-400 @enderror" style="border-color:var(--fe-border);">
                                @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="fe-contact-email" class="block text-sm font-medium mb-1.5" style="color:var(--fe-text);">Email <span class="text-red-500">*</span></label>
                                <input type="email" id="fe-contact-email" name="email" value="{{ old('email') }}" required class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm @error('email') border-red-400 @enderror" style="border-color:var(--fe-border);">
                                @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="fe-contact-phone" class="block text-sm font-medium mb-1.5" style="color:var(--fe-text);">Phone</label>
                                <input type="text" id="fe-contact-phone" name="phone" value="{{ old('phone') }}" class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm" style="border-color:var(--fe-border);">
                            </div>
                            <div>
                                <label for="fe-contact-organization" class="block text-sm font-medium mb-1.5" style="color:var(--fe-text);">Organization</label>
                                <input type="text" id="fe-contact-organization" name="organization" value="{{ old('organization') }}" class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm" style="border-color:var(--fe-border);">
                            </div>
                        </div>

                        <div>
                            <label for="fe-contact-subject" class="block text-sm font-medium mb-1.5" style="color:var(--fe-text);">Subject <span class="text-red-500">*</span></label>
                            <input type="text" id="fe-contact-subject" name="subject" value="{{ old('subject') }}" required class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm @error('subject') border-red-400 @enderror" style="border-color:var(--fe-border);">
                            @error('subject')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="fe-contact-message" class="block text-sm font-medium mb-1.5" style="color:var(--fe-text);">Message <span class="text-red-500">*</span></label>
                            <textarea id="fe-contact-message" name="message" required rows="5" class="fe-focus-ring w-full px-3 py-2.5 rounded-xl border text-sm @error('message') border-red-400 @enderror" style="border-color:var(--fe-border);min-height:130px;">{{ old('message') }}</textarea>
                            @error('message')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit" class="fe-btn-primary fe-focus-ring px-6 py-2.5 rounded-lg text-sm font-semibold">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
