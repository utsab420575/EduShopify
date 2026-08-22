@props(['label' => null, 'name', 'required' => false, 'hint' => null, 'options' => [], 'placeholder' => null, 'selected' => null])

@php($hasError = $errors->has($name))
@php($current = old($name, $selected ?? $value ?? ''))

<div>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1.5">
            {{ $label }} @if($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif

    <select
        name="{{ $name }}"
        id="{{ $name }}"
        @if($required) required @endif
        {{ $attributes->merge([
            'class' => 'focus-accent w-full text-sm rounded-lg border px-3 py-2.5 bg-white '
                . ($hasError ? 'border-red-400 bg-red-50' : 'border-gray-300')
        ]) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @if(isset($slot) && trim($slot->toHtml()) !== '')
            {{ $slot }}
        @else
            @foreach($options as $optValue => $optLabel)
                <option value="{{ $optValue }}" @selected((string) $current === (string) $optValue)>{{ $optLabel }}</option>
            @endforeach
        @endif
    </select>

    @if($hasError)
        <p class="mt-1 text-xs text-red-600">{{ $errors->first($name) }}</p>
    @elseif($hint)
        <p class="mt-1.5 text-xs text-gray-400">{{ $hint }}</p>
    @endif
</div>
