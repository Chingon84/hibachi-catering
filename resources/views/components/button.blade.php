@props([
    'variant' => 'primary',
    'size' => null,
    'href' => null,
    'type' => 'button',
])
@php
    $classes = 'btn';
    if ($variant && $variant !== 'primary') { $classes .= ' '.$variant; }
    if ($size) { $classes .= ' '.$size; }
@endphp
@if($href)
<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
