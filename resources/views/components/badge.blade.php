@props(['variant' => 'neutral'])
<span {{ $attributes->merge(['class' => 'badge '.$variant]) }}>{{ $slot }}</span>
