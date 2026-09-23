{{--
    Renders one action from the $actions array built in index.blade.php's
    row loop, in either the desktop "pill" style or the mobile dropdown
    "menu" style — both variants read the exact same $action data so the
    two layouts can never drift out of sync. Mirrors
    supplier/procurement/quotations/partials/_action-button.blade.php and
    buyer/procurement/rfqs/partials/_action-button.blade.php.

    Expected $action keys: label, icon, href, method ('GET'|'POST'|'DELETE'),
    stats (bool — opens the shared Statistics modal instead of navigating),
    danger (bool — red styling), confirm (['title','text','icon','confirmText']).
--}}
@php
    $isDanger = ! empty($action['danger']);
    $method = strtoupper($action['method'] ?? 'GET');

    $pillClass = 'text-[11px] font-semibold px-2.5 py-1.5 rounded-lg border flex items-center gap-1.5 transition-colors whitespace-nowrap shrink-0 '
        . ($isDanger ? 'border-red-200 text-red-600 hover:bg-red-50' : 'border-gray-300 text-gray-600 hover:bg-gray-50');
    $menuClass = 'w-full text-left text-xs px-3 py-2 flex items-center gap-2.5 transition-colors '
        . ($isDanger ? 'text-red-600 hover:bg-red-50' : 'text-gray-700 hover:bg-gray-50');
    $class = $variant === 'pill' ? $pillClass : $menuClass;
@endphp

@if(! empty($action['stats']))
    <button type="button" @click="openStatistics({{ $quotation->id }})" class="{{ $class }}">
        <i class="{{ $action['icon'] }} {{ $variant === 'pill' ? '' : 'w-3.5 text-center' }}"></i> {{ $action['label'] }}
    </button>
@elseif($method === 'GET')
    <a href="{{ $action['href'] }}" class="{{ $class }}">
        <i class="{{ $action['icon'] }} {{ $variant === 'pill' ? '' : 'w-3.5 text-center' }}"></i> {{ $action['label'] }}
    </a>
@else
    <form method="POST" action="{{ $action['href'] }}" class="{{ $variant === 'menu' ? 'w-full' : '' }}"
          @if(! empty($action['confirm']))
              onsubmit="return confirmSwal(this, {{ Js::from($action['confirm']['title']) }}, {{ Js::from($action['confirm']['text']) }}, {{ Js::from($action['confirm']['icon']) }}, {{ Js::from($action['confirm']['confirmText']) }})"
          @endif>
        @csrf
        @if($method === 'DELETE') @method('DELETE') @endif
        <button type="submit" class="{{ $class }} {{ $variant === 'menu' ? 'w-full' : '' }}">
            <i class="{{ $action['icon'] }} {{ $variant === 'pill' ? '' : 'w-3.5 text-center' }}"></i> {{ $action['label'] }}
        </button>
    </form>
@endif
