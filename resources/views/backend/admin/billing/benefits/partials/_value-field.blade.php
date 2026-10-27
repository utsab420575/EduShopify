{{--
    Typed value input for a benefit value — one control per value type, only
    the one matching the type is shown and submitted (the rest are disabled).
    Must sit inside an x-data scope.

    $name      — field name (e.g. 'value', 'default_value', 'benefits[5][value]')
    $oldKey    — dot key for old() / errors (defaults to $name)
    $typeExpr  — Alpine expression giving the type: "'integer'" or "type"
    $value     — current value (already typed), or null
    $enabledExpr — optional Alpine expression; false disables the whole field
    $allowEmpty  — boolean: offer an empty choice (e.g. optional default value)
    $unit      — optional unit shown after number inputs
    $useOld    — optional false: ignore old()/errors (another form on the page failed)
--}}
@php
    $oldKey ??= $name;
    $enabledExpr ??= 'true';
    $allowEmpty ??= false;
    $unit ??= null;
    $useOld ??= true;
    $current = $useOld ? old($oldKey, $value ?? null) : ($value ?? null);
    if (! $useOld) {
        $oldKey = '__ignored_'.$oldKey; // no error shown for another form's field
    }
    $boolCurrent = $current === null || $current === '' ? null : filter_var($current, FILTER_VALIDATE_BOOLEAN);
    $scalar = is_bool($current) ? (int) $current : $current;
    $inputClass = 'w-full px-3 py-2 border rounded-lg text-sm text-gray-900 placeholder:text-gray-400 focus-accent disabled:bg-gray-50 disabled:text-gray-400 '
        . ($errors->has($oldKey) ? 'border-red-400 bg-red-50' : 'border-gray-300');
@endphp

<div>
    <select name="{{ $name }}" x-show="{{ $typeExpr }} === 'boolean'" :disabled="!({{ $enabledExpr }}) || {{ $typeExpr }} !== 'boolean'"
            class="{{ $inputClass }} bg-white">
        @if($allowEmpty)
            <option value="" @selected($boolCurrent === null)>— Not set (Disabled) —</option>
        @endif
        <option value="1" @selected($boolCurrent === true)>Enabled</option>
        <option value="0" @selected($boolCurrent === false && ! ($allowEmpty && ($current === null || $current === '')))>Disabled</option>
    </select>

    <div class="flex items-center gap-2" x-show="{{ $typeExpr }} === 'integer' || {{ $typeExpr }} === 'decimal'" x-cloak>
        <input type="number" name="{{ $name }}" step="1" value="{{ is_numeric($scalar) ? (int) $scalar : '' }}"
               x-show="{{ $typeExpr }} === 'integer'" :disabled="!({{ $enabledExpr }}) || {{ $typeExpr }} !== 'integer'"
               placeholder="{{ $allowEmpty ? 'Not set' : '0' }}" class="{{ $inputClass }}">
        <input type="number" name="{{ $name }}" step="any" value="{{ is_numeric($scalar) ? $scalar : '' }}"
               x-show="{{ $typeExpr }} === 'decimal'" :disabled="!({{ $enabledExpr }}) || {{ $typeExpr }} !== 'decimal'"
               placeholder="{{ $allowEmpty ? 'Not set' : '0.00' }}" class="{{ $inputClass }}">
        @if($unit)
            <span class="text-xs text-gray-500 shrink-0">{{ $unit }}</span>
        @endif
    </div>

    <input type="text" name="{{ $name }}" value="{{ is_scalar($scalar) && ! is_bool($current) ? $scalar : '' }}" maxlength="1000"
           x-show="{{ $typeExpr }} === 'text'" x-cloak :disabled="!({{ $enabledExpr }}) || {{ $typeExpr }} !== 'text'"
           placeholder="{{ $allowEmpty ? 'Not set' : 'Value' }}" class="{{ $inputClass }}">

    @error($oldKey)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
