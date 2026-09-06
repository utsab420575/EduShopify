@php
  $isSaved = $isSaved ?? ($supplier->is_saved ?? false);
  $savesCount = $savesCount ?? ($supplier->saves_count ?? 0);
  $btnClass = $class ?? '';
  $idSuffix = $supplier->id ?? ($supplier->slug ?? 'item');
@endphp

<button
  type="button"
  id="fn-supplier-save-{{ $idSuffix }}"
  class="fn-supplier-save-btn group/fav flex items-center gap-1.5 px-2.5 py-1 rounded-full transition-all duration-200 text-xs font-semibold backdrop-blur-sm select-none border cursor-pointer {{ $isSaved ? 'is-saved bg-rose-50 hover:bg-rose-100 text-rose-600 border-rose-200 shadow-xs' : ($savesCount > 0 ? 'bg-white/90 hover:bg-white text-gray-700 hover:text-rose-600 border-gray-200 shadow-xs' : 'bg-white/90 hover:bg-white text-gray-400 hover:text-rose-600 border-gray-200 shadow-xs') }} {{ $btnClass }}"
  data-supplier-slug="{{ $supplier->slug }}"
  data-supplier-id="{{ $supplier->id }}"
  data-supplier-account-id="{{ $supplier->account_id }}"
  data-saved="{{ $isSaved ? '1' : '0' }}"
  data-saves-count="{{ $savesCount }}"
  data-save-url="{{ route('v2.suppliers.save', $supplier->slug) }}"
  aria-label="{{ $isSaved ? 'Remove ' . $supplier->display_name . ' from favorites' : 'Save ' . $supplier->display_name . ' to favorites' }}"
  title="{{ $isSaved ? 'Saved to your favorites (' . $savesCount . ')' : 'Save supplier (' . $savesCount . ')' }}"
  onclick="event.preventDefault(); event.stopPropagation(); window.fnToggleSupplierSave && window.fnToggleSupplierSave(this);"
>
  <i class="fn-fav-icon fa-{{ $isSaved ? 'solid text-rose-500' : 'regular text-gray-400 group-hover/fav:text-rose-500' }} fa-heart text-xs transition-transform duration-200"></i>
  <span id="fav-count-supplier-{{ $idSuffix }}" class="fn-fav-count text-xs font-semibold leading-none {{ $isSaved ? 'text-rose-600' : 'text-gray-700 group-hover/fav:text-rose-600' }} {{ ($savesCount > 0 || $isSaved) ? '' : 'hidden' }}">{{ $savesCount }}</span>
</button>
