@php
  $isSaved = $isSaved ?? ($listing->is_saved ?? false);
  $savesCount = $savesCount ?? ($listing->saves_count ?? 0);
  $btnClass = $class ?? '';
  $idSuffix = $listing->id ?? ($listing->slug ?? 'item');
@endphp

<button
  type="button"
  id="fn-listing-save-{{ $idSuffix }}"
  class="fn-listing-save-btn group/fav flex items-center gap-1.5 px-2.5 py-1 rounded-full transition-all duration-200 text-xs font-semibold backdrop-blur-sm select-none border cursor-pointer {{ $isSaved ? 'is-saved bg-rose-50 hover:bg-rose-100 text-rose-600 border-rose-200 shadow-xs' : ($savesCount > 0 ? 'bg-white/90 hover:bg-white text-gray-700 hover:text-rose-600 border-gray-200 shadow-xs' : 'bg-white/90 hover:bg-white text-gray-400 hover:text-rose-600 border-gray-200 shadow-xs') }} {{ $btnClass }}"
  data-listing-slug="{{ $listing->slug }}"
  data-listing-id="{{ $listing->id }}"
  data-saved="{{ $isSaved ? '1' : '0' }}"
  data-saves-count="{{ $savesCount }}"
  data-save-url="{{ route('v2.products.save', $listing->slug) }}"
  aria-label="{{ $isSaved ? 'Remove ' . $listing->name . ' from favorites' : 'Save ' . $listing->name . ' to favorites' }}"
  title="{{ $isSaved ? 'Saved to your favorites (' . $savesCount . ')' : 'Save product (' . $savesCount . ')' }}"
  onclick="event.preventDefault(); event.stopPropagation(); window.fnToggleListingSave && window.fnToggleListingSave(this);"
>
  <i class="fn-fav-icon fa-{{ $isSaved ? 'solid text-rose-500' : 'regular text-gray-400 group-hover/fav:text-rose-500' }} fa-heart text-xs transition-transform duration-200"></i>
  <span id="fav-count-listing-{{ $idSuffix }}" class="fn-fav-count text-xs font-semibold leading-none {{ $isSaved ? 'text-rose-600' : 'text-gray-700 group-hover/fav:text-rose-600' }} {{ ($savesCount > 0 || $isSaved) ? '' : 'hidden' }}">{{ $savesCount }}</span>
</button>
