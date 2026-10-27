{{-- Shared page header: @include('frontend_new.pages.partials._hero', ['label' => ..., 'title' => ..., 'subtitle' => ...]) --}}
<section class="bg-gray-50 border-b border-gray-200 py-16 px-4 text-center">
  <div class="max-w-3xl mx-auto">
    <p class="text-xs font-semibold tracking-widest uppercase text-emerald-600 mb-3">{{ $label }}</p>
    <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">{{ $title }}</h1>
    @isset($subtitle)
      <p class="mt-4 text-base text-gray-500 leading-relaxed">{{ $subtitle }}</p>
    @endisset
  </div>
</section>
