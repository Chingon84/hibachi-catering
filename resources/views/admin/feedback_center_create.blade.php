<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - New Feedback</title>
  <link rel="stylesheet" href="/assets/admin.css">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    :root{
      --panel-border:#e7ebf3;
      --panel-shadow:0 18px 40px rgba(15,23,42,.06);
      --text-strong:#0f172a;
    }
    html{-webkit-text-size-adjust:100%;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
    *,*::before,*::after{box-sizing:border-box}
    .container{width:calc(100vw - 24px);max-width:none;margin:18px 12px;padding:0 12px}
    .form-shell{max-width:980px;margin:0 auto}
    .page-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:12px}
    .title{margin:0 0 6px;font-size:28px;line-height:1.08;letter-spacing:-.03em;color:var(--text-strong)}
    .subtitle{margin:0;color:#64748b;font-size:14px;max-width:620px}
    .eyebrow{display:inline-flex;align-items:center;gap:8px;padding:5px 9px;border-radius:999px;border:1px solid #e6e9f2;background:#fff;color:#475569;font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;margin-bottom:10px}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:40px;padding:0 15px;border-radius:12px;text-decoration:none;box-shadow:0 10px 24px rgba(178,30,39,.18);font-family:inherit;font-size:14px;line-height:1.2;-webkit-appearance:none;appearance:none}
    .btn.secondary{min-height:36px;padding:0 13px;border:1px solid #e2e8f0;background:#fff;color:#64748b;box-shadow:none;font-size:13px}
    .surface-card{background:linear-gradient(180deg,#fff 0%,#fcfdff 100%);border:1px solid var(--panel-border);border-radius:18px;box-shadow:var(--panel-shadow)}
    .surface-body{padding:15px}
    .type-strip{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px}
    .type-chip{display:inline-flex;align-items:center;gap:8px;padding:7px 11px;border-radius:999px;border:1px solid #d8deea;background:#fff;color:#334155;font-size:11px;font-weight:800;text-decoration:none}
    .type-chip.active{background:#0f172a;border-color:#0f172a;color:#fff;box-shadow:0 10px 20px rgba(15,23,42,.14)}
    .form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px 12px}
    .field-full{grid-column:1 / -1}
    .field-label{display:block;margin:0 0 5px;font-size:11px;font-weight:800;color:#64748b;letter-spacing:.07em;text-transform:uppercase}
    .input,.select,.textarea{width:100%;min-height:36px;border:1px solid #d8deea;border-radius:12px;background:#fff;padding:7px 11px;font-family:inherit;font-size:14px;line-height:1.4;-webkit-appearance:none;appearance:none}
    .textarea{min-height:96px;resize:vertical}
    .input:focus,.select:focus,.textarea:focus{outline:none;border-color:#c6d1e3;box-shadow:0 0 0 4px rgba(148,163,184,.14)}
    .select{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='none' stroke='%2394a3b8' stroke-width='1.7'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='m6 8 4 4 4-4'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;background-size:14px 14px;padding-right:38px}
    .team-members-multiselect [data-team-member-option]{display:flex !important;align-items:center !important;gap:12px}
    .team-members-multiselect [data-team-member-checkbox]{width:16px !important;height:16px !important;min-width:16px;flex:0 0 16px;display:inline-block !important;margin:0 !important;padding:0 !important;border-radius:4px;background:#fff;vertical-align:middle;align-self:center;border:1px solid #cbd5e1;box-shadow:none}
    .callout{margin-top:12px;padding:10px 12px;border-radius:14px;background:#fafcff;border:1px solid #e8edf5;color:#64748b;font-size:12px;line-height:1.55}
    .actions{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:12px;padding-top:12px;border-top:1px solid #edf2f7}
    .actions-left,.actions-right{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
    .helper{font-size:12px;color:#94a3b8}
    @media (max-width: 760px){
      .page-head{flex-direction:column;align-items:stretch}
      .form-grid{grid-template-columns:1fr}
      .container{padding:0 10px}
      .actions{flex-direction:column;align-items:stretch}
      .actions-left,.actions-right{width:100%;justify-content:flex-start}
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="form-shell">
      @if (session('ok'))
        <div class="surface-card" style="margin-bottom:12px">
          <div class="surface-body" style="padding:14px 18px;color:#166534;background:#ecfdf5;border-radius:18px">
            {{ session('ok') }}
          </div>
        </div>
      @endif
      @if ($errors->any())
        <div class="surface-card" style="margin-bottom:12px">
          <div class="surface-body" style="padding:14px 18px;color:#991b1b;background:#fef2f2;border-radius:18px">
            {{ $errors->first() }}
          </div>
        </div>
      @endif

      <div class="page-head">
        <div>
          <div class="eyebrow">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M4 4h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H8l-4 3v-3H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2zm3 5h10v2H7V9zm0 4h7v2H7v-2z"/></svg>
            Workflow launcher
          </div>
          <h1 class="title">{{ $typeMap[$selectedType]['title'] }}</h1>
          <p class="subtitle">{{ $typeMap[$selectedType]['subtitle'] }}</p>
        </div>
        <div class="actions-right">
          <a class="btn secondary" href="{{ $backUrl }}">Back to Feedback Center</a>
        </div>
      </div>

      <div class="surface-card">
        <div class="surface-body">
          <div class="type-strip">
            @foreach($typeMap as $typeKey => $config)
              <a class="type-chip {{ $selectedType === $typeKey ? 'active' : '' }}" href="{{ route('admin.feedback.create', ['type' => $typeKey, 'back' => $backUrl]) }}">{{ $config['title'] }}</a>
            @endforeach
          </div>

          <form method="post" action="{{ route('admin.feedback.create.submit', ['type' => $selectedType, 'back' => $backUrl]) }}" novalidate>
            @csrf
            <input type="hidden" name="type" value="{{ $selectedType }}">
            <div class="form-grid">
              @foreach($typeMap[$selectedType]['fields'] as $field)
                @if($field['type'] === 'team-members')
                  <div class="field-full">
                    @include('admin.partials.team-members-multiselect', [
                      'fieldId' => 'create-complaint-team-members',
                      'fieldName' => 'team_members',
                      'fieldLabel' => $field['label'],
                      'options' => $teamMemberOptions ?? [],
                      'selected' => old('team_members', []),
                      'max' => 7,
                      'placeholder' => 'Search active team members...',
                    ])
                  </div>
                  @continue
                @endif
                @php $isFull = in_array($field['type'], ['textarea'], true); @endphp
                <div class="{{ $isFull ? 'field-full' : '' }}">
                  <label class="field-label" for="field_{{ $field['name'] }}">{{ $field['label'] }}</label>
                  @if($field['type'] === 'select')
                    <select class="select" id="field_{{ $field['name'] }}" name="{{ $field['name'] }}" {{ !empty($field['required']) ? 'required' : '' }}>
                      <option value="">Select {{ strtolower($field['label']) }}</option>
                      @foreach($field['options'] as $option)
                        <option value="{{ $option }}" {{ old($field['name'], $field['name'] === 'status' && $selectedType === 'days-off' ? 'Pending' : null) == $option ? 'selected' : '' }}>{{ $option }}</option>
                      @endforeach
                    </select>
                  @elseif($field['type'] === 'textarea')
                    <textarea class="textarea" id="field_{{ $field['name'] }}" name="{{ $field['name'] }}" {{ !empty($field['required']) ? 'required' : '' }}>{{ old($field['name']) }}</textarea>
                  @else
                    <input class="input" id="field_{{ $field['name'] }}" name="{{ $field['name'] }}" type="{{ $field['type'] }}" value="{{ old($field['name'], $field['name'] === 'unauthorized_days' && $selectedType === 'days-off' ? 0 : null) }}" {{ !empty($field['required']) ? 'required' : '' }}>
                  @endif
                </div>
              @endforeach
            </div>

            <div class="callout">
              This creation form is now type-aware and workflow-ready. The next backend step is wiring the submit action to store records in the appropriate feedback tables or unified intake model.
            </div>

            <div class="actions">
              <div class="actions-left">
                <span class="helper">Selected workflow: <strong>{{ $typeMap[$selectedType]['title'] }}</strong></span>
              </div>
              <div class="actions-right">
                <a class="btn secondary" href="{{ $backUrl }}">Cancel</a>
                <button class="btn" type="submit">Create {{ $typeMap[$selectedType]['title'] }}</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('[data-team-members-field]').forEach((field) => {
        const searchInput = field.querySelector('.team-members-search');
        const toggleButton = field.querySelector('[data-team-members-toggle]');
        const trigger = field.querySelector('[data-team-members-trigger]');
        const panel = field.querySelector('[data-team-members-panel]');
        const pills = field.querySelector('[data-selected-pills]');
        const message = field.querySelector('[data-team-members-message]');
        const checkboxes = Array.from(field.querySelectorAll('[data-team-member-checkbox]'));
        const options = Array.from(field.querySelectorAll('[data-team-member-option]'));
        const max = Number(field.dataset.max || 7);
        let selected = [];

        try {
          selected = JSON.parse(field.dataset.selected || '[]');
        } catch (error) {
          selected = [];
        }

        selected = selected.filter(Boolean).slice(0, max);

        const setOpen = (isOpen) => {
          if (!panel || !trigger) return;
          panel.classList.toggle('hidden', !isOpen);
          trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
          if (isOpen) {
            searchInput?.focus();
          }
        };

        const render = () => {
          pills.innerHTML = '';
          message.textContent = selected.length >= max ? `Maximum ${max} team members per complaint.` : '';

          if (selected.length === 0) {
            const empty = document.createElement('span');
            empty.className = 'text-sm text-slate-400';
            empty.textContent = 'No team members selected yet.';
            pills.appendChild(empty);
          }

          selected.forEach((name) => {
            const pill = document.createElement('span');
            pill.className = 'inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700';
            pill.textContent = name;

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'text-slate-400 hover:text-slate-700';
            remove.textContent = '×';
            remove.addEventListener('click', () => {
              selected = selected.filter((member) => member !== name);
              syncFromState();
            });
            pill.appendChild(remove);
            pills.appendChild(pill);
          });
        };

        const syncFromState = () => {
          checkboxes.forEach((checkbox) => {
            checkbox.checked = selected.includes(checkbox.value);
            checkbox.disabled = !checkbox.checked && selected.length >= max;
          });
          render();
        };

        toggleButton?.addEventListener('click', (event) => {
          event.preventDefault();
          event.stopPropagation();
          setOpen(panel?.classList.contains('hidden'));
        });

        trigger?.addEventListener('click', (event) => {
          if (event.target === toggleButton || event.target === searchInput) {
            return;
          }
          setOpen(panel?.classList.contains('hidden'));
        });

        trigger?.addEventListener('keydown', (event) => {
          if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            setOpen(panel?.classList.contains('hidden'));
          }
        });

        checkboxes.forEach((checkbox) => {
          checkbox.addEventListener('change', () => {
            if (checkbox.checked) {
              if (selected.length >= max) {
                checkbox.checked = false;
                message.textContent = `Maximum ${max} team members per complaint.`;
                return;
              }
              selected.push(checkbox.value);
            } else {
              selected = selected.filter((member) => member !== checkbox.value);
            }
            selected = [...new Set(selected)];
            syncFromState();
          });
        });

        searchInput?.addEventListener('input', () => {
          const query = searchInput.value.trim().toLowerCase();
          options.forEach((option) => {
            const name = option.dataset.memberName?.toLowerCase() || '';
            option.classList.toggle('hidden', query !== '' && !name.includes(query));
          });
          if (panel?.classList.contains('hidden')) {
            setOpen(true);
          }
        });

        document.addEventListener('click', (event) => {
          if (!field.contains(event.target)) {
            setOpen(false);
          }
        });

        syncFromState();
      });
    });
  </script>
</body>
</html>
