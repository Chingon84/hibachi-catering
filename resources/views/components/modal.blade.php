@props([
    'id' => null,
    'title' => null,
])
<div class="modal-backdrop" @if($id)id="{{ $id }}"@endif data-modal hidden>
    <div class="modal" role="dialog" aria-modal="true">
        @if($title)
        <div class="modal-head">
            <h3 class="modal-title">{{ $title }}</h3>
            <button type="button" class="icon-btn" data-modal-close aria-label="Close">&times;</button>
        </div>
        @endif
        <div class="modal-body">{{ $slot }}</div>
        @isset($footer)<div class="modal-foot">{{ $footer }}</div>@endisset
    </div>
</div>
