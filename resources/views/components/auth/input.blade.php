@props([
    'autocomplete' => null,
    'label',
    'name',
    'placeholder' => null,
    'type' => 'text',
    'value' => null,
])

<div>
    <label for="{{ $name }}" class="text-sm font-bold text-slate-700">
        {{ $label }}
    </label>
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        autocomplete="{{ $autocomplete }}"
        {{ $attributes->merge(['class' => 'form-input-shell']) }}
    >
    @error($name)
        <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
    @enderror
</div>
