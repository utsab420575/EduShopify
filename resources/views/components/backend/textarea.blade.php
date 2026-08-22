@props(['label' => null, 'name', 'required' => false, 'hint' => null, 'rows' => 4])

@php($hasError = $errors->has($name))

<div>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1.5">
            {{ $label }} @if($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        @if($required) required @endif
        {{ $attributes->merge([
            'class' => 'w-full px-3 py-2.5 border rounded-lg text-sm text-gray-900 placeholder:text-gray-400 transition '
                . ($hasError ? 'border-red-400 bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-400' : 'border-gray-300 focus-accent')
        ]) }}
    >{{ old($name, $value ?? '') }}</textarea>

    @if($hasError)
        <p class="mt-1 text-xs text-red-600">{{ $errors->first($name) }}</p>
    @elseif($hint)
        <p class="mt-1.5 text-xs text-gray-400">{{ $hint }}</p>
    @endif
</div>
