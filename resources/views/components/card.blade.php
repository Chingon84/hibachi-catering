@props([
    'flush' => false,
    'title' => null,
])
<div {{ $attributes->merge(['class' => 'card']) }}>
    @if($title || isset($actions))
    <div class="card-head">
        @if($title)<h2 class="card-title">{{ $title }}</h2>@endif
        @isset($actions)<div class="row" style="margin-left:auto">{{ $actions }}</div>@endisset
    </div>
    @endif
    @if($flush)
        {{ $slot }}
    @else
        <div class="card-body">{{ $slot }}</div>
    @endif
</div>
