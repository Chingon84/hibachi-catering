@props([
    'label' => null,
    'for' => null,
    'hint' => null,
])
<div {{ $attributes->merge(['class' => 'field']) }}>
    @if($label)<label class="field-label" @if($for)for="{{ $for }}"@endif>{{ $label }}</label>@endif
    {{ $slot }}
    @if($hint)<div class="muted" style="margin-top:5px;font-size:12px">{{ $hint }}</div>@endif
</div>
