@php
  $fieldId = $fieldId ?? 'team-members';
  $fieldName = $fieldName ?? 'team_members';
  $fieldLabel = $fieldLabel ?? 'Team Members';
  $placeholder = $placeholder ?? 'Search active team members...';
  $max = $max ?? 7;
  $options = collect($options ?? [])->map(function ($memberOption) {
      if (is_array($memberOption)) {
          return [
              'value' => (string) ($memberOption['value'] ?? $memberOption['name'] ?? $memberOption['label'] ?? ''),
              'label' => (string) ($memberOption['label'] ?? $memberOption['name'] ?? $memberOption['value'] ?? ''),
          ];
      }

      return ['value' => (string) $memberOption, 'label' => (string) $memberOption];
  })->filter(fn ($memberOption) => $memberOption['value'] !== '')->values();
  $selected = collect($selected ?? [])->map(function ($member) {
      if (is_array($member)) {
          return (string) ($member['value'] ?? $member['name'] ?? $member['label'] ?? '');
      }

      return (string) $member;
  })->filter()->values();
@endphp

<div class="team-members-multiselect relative" data-team-members-field data-max="{{ $max }}" data-selected='@json($selected->all())'>
  <label class="field-label" for="{{ $fieldId }}_search">{{ $fieldLabel }}</label>
  <div class="team-members-control rounded-xl border border-slate-200 bg-white px-3 py-2" data-team-members-trigger tabindex="0" role="button" aria-expanded="false">
    <div class="flex flex-wrap items-center gap-1.5" data-selected-pills></div>
    <div class="mt-1 flex items-center gap-2">
      <input id="{{ $fieldId }}_search" class="team-members-search min-w-0 flex-1 border-0 bg-transparent px-0 py-0 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-0" type="text" placeholder="{{ $placeholder }}">
      <button class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-[11px] font-medium text-slate-500 hover:bg-slate-100" type="button" data-team-members-toggle>Browse</button>
    </div>
  </div>
  <div class="absolute left-0 right-0 top-[calc(100%+8px)] z-20 hidden rounded-xl border border-slate-200 bg-white p-2 shadow-lg" data-team-members-panel>
    <div class="max-h-56 overflow-y-auto pr-1">
      @foreach($options as $memberOption)
        <label class="flex items-center gap-3 rounded-md px-3 py-2 hover:bg-slate-50 cursor-pointer" data-team-member-option data-member-name="{{ $memberOption['label'] }}">
          <input
            type="checkbox"
            class="h-4 w-4 shrink-0 rounded border border-slate-300 align-middle"
            name="{{ $fieldName }}[]"
            value="{{ $memberOption['value'] }}"
            data-team-member-checkbox
          >
          <span class="text-sm font-medium text-slate-900 leading-none">{{ $memberOption['label'] }}</span>
        </label>
      @endforeach
    </div>
  </div>
  <div class="mt-1.5 text-[11px] text-slate-500">Maximum {{ $max }} team members per complaint.</div>
  <div class="mt-1 text-xs text-rose-600" data-team-members-message></div>
</div>
