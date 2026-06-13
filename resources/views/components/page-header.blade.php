@props([
    'title' => null,
    'subtitle' => null,
])
<div {{ $attributes->merge(['class' => 'page-header']) }}>
    <div class="ph-titles">
        @if($title)<h1>{{ $title }}</h1>@endif
        @if($subtitle)<div class="ph-sub">{{ $subtitle }}</div>@endif
        {{ $slot }}
    </div>
    @isset($actions)<div class="ph-actions">{{ $actions }}</div>@endisset
</div>
