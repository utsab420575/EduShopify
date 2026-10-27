@extends('frontend_new.layouts.app')

@section('title', 'Contact Us | Edushopify')

@section('content')

  @include('frontend_new.pages.partials._hero', [
    'label' => 'Contact',
    'title' => 'Get in Touch',
    'subtitle' => "Questions about buying, selling or your account? Send us a message and our team will get back to you.",
  ])

  <section class="py-16 px-4">
    <div class="max-w-6xl mx-auto grid gap-10 lg:grid-cols-[1fr_1.6fr]">

      {{-- Info --}}
      <div class="space-y-6">
        @if($contactEmail)
          <div class="flex gap-4">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0"><i class="fa-regular fa-envelope"></i></div>
            <div>
              <p class="font-semibold text-gray-900">Email</p>
              <a href="mailto:{{ $contactEmail }}" class="text-sm text-emerald-600 hover:underline break-all">{{ $contactEmail }}</a>
            </div>
          </div>
        @endif
        <div class="flex gap-4">
          <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0"><i class="fa-regular fa-life-ring"></i></div>
          <div>
            <p class="font-semibold text-gray-900">Help Center</p>
            <p class="text-sm text-gray-500">Many questions are answered in our <a href="{{ route('v2.pages.help') }}" class="text-emerald-600 hover:underline">Help Center</a>.</p>
          </div>
        </div>
        <div class="flex gap-4">
          <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0"><i class="fa-regular fa-clock"></i></div>
          <div>
            <p class="font-semibold text-gray-900">Response time</p>
            <p class="text-sm text-gray-500">We reply to most messages within 1–2 business days.</p>
          </div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 text-sm text-gray-600">
          Looking for a supplier quote? Contact suppliers directly from their profile, or <a href="{{ route('v2.handoff.post-rfq') }}" class="font-semibold text-emerald-600 hover:underline">post an RFQ</a>.
        </div>
      </div>

      {{-- Form --}}
      <div class="bg-white border border-gray-200 rounded-2xl p-6 sm:p-8">
        @if(session('success'))
          <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <i class="fa-solid fa-circle-check mr-1.5"></i>{{ session('success') }}
          </div>
        @endif

        <form method="POST" action="{{ route('v2.pages.contact.submit') }}" class="grid gap-5 sm:grid-cols-2">
          @csrf
          {{-- Honeypot: StoreContactRequest rejects any value here --}}
          <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

          @foreach([
            ['name' => 'name', 'label' => 'Full Name', 'type' => 'text', 'required' => true, 'max' => 150],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'max' => 190],
            ['name' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'required' => false, 'max' => 30],
            ['name' => 'organization', 'label' => 'Organization', 'type' => 'text', 'required' => false, 'max' => 150],
          ] as $field)
            <div>
              <label for="contact_{{ $field['name'] }}" class="block text-sm font-medium text-gray-700 mb-1.5">{{ $field['label'] }}@if($field['required'])<span class="text-red-500"> *</span>@endif</label>
              <input id="contact_{{ $field['name'] }}" type="{{ $field['type'] }}" name="{{ $field['name'] }}" value="{{ old($field['name']) }}" maxlength="{{ $field['max'] }}" @required($field['required'])
                     class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 {{ $errors->has($field['name']) ? 'border-red-400' : 'border-gray-300' }}">
              @error($field['name'])<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
          @endforeach

          <div class="sm:col-span-2">
            <label for="contact_subject" class="block text-sm font-medium text-gray-700 mb-1.5">Subject<span class="text-red-500"> *</span></label>
            <input id="contact_subject" type="text" name="subject" value="{{ old('subject') }}" maxlength="190" required
                   class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 {{ $errors->has('subject') ? 'border-red-400' : 'border-gray-300' }}">
            @error('subject')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
          </div>

          <div class="sm:col-span-2">
            <label for="contact_message" class="block text-sm font-medium text-gray-700 mb-1.5">Message<span class="text-red-500"> *</span></label>
            <textarea id="contact_message" name="message" rows="6" maxlength="3000" required
                      class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 {{ $errors->has('message') ? 'border-red-400' : 'border-gray-300' }}">{{ old('message') }}</textarea>
            @error('message')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            @error('website')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
          </div>

          <div class="sm:col-span-2">
            <button type="submit" class="w-full sm:w-auto bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold px-6 py-3 rounded-lg transition">Send Message</button>
          </div>
        </form>
      </div>
    </div>
  </section>

@endsection
