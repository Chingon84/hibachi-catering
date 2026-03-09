<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - New Feedback</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    :root{
      --panel-border:#e7ebf3;
      --panel-shadow:0 18px 40px rgba(15,23,42,.06);
      --text-strong:#0f172a;
    }
    *{box-sizing:border-box}
    .container{width:calc(100vw - 24px);max-width:none;margin:20px 12px;padding:0 12px}
    .page-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:16px}
    .title{margin:0 0 6px;font-size:28px;line-height:1.08;letter-spacing:-.03em;color:var(--text-strong)}
    .subtitle{margin:0;color:#64748b;font-size:14px;max-width:620px}
    .eyebrow{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;border:1px solid #e6e9f2;background:#fff;color:#475569;font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;margin-bottom:12px}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:42px;padding:0 16px;border-radius:12px;text-decoration:none;box-shadow:0 10px 24px rgba(178,30,39,.18)}
    .btn.secondary{box-shadow:none}
    .surface-card{background:linear-gradient(180deg,#fff 0%,#fcfdff 100%);border:1px solid var(--panel-border);border-radius:18px;box-shadow:var(--panel-shadow)}
    .surface-body{padding:18px}
    .type-strip{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px}
    .type-chip{display:inline-flex;align-items:center;gap:8px;padding:9px 12px;border-radius:999px;border:1px solid #d8deea;background:#fff;color:#334155;font-size:12px;font-weight:800;text-decoration:none}
    .type-chip.active{background:#0f172a;border-color:#0f172a;color:#fff;box-shadow:0 10px 20px rgba(15,23,42,.14)}
    .form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
    .field-full{grid-column:1 / -1}
    .field-label{display:block;margin:0 0 6px;font-size:11px;font-weight:800;color:#64748b;letter-spacing:.07em;text-transform:uppercase}
    .input,.select,.textarea{width:100%;min-height:42px;border:1px solid #d8deea;border-radius:12px;background:#fff;padding:10px 12px}
    .textarea{min-height:120px;resize:vertical}
    .input:focus,.select:focus,.textarea:focus{outline:none;border-color:#c6d1e3;box-shadow:0 0 0 4px rgba(148,163,184,.14)}
    .callout{margin-top:16px;padding:14px 16px;border-radius:16px;background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-size:13px;line-height:1.6}
    .actions{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:18px;padding-top:16px;border-top:1px solid #edf2f7}
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
              @php $isFull = in_array($field['type'], ['textarea'], true); @endphp
              <div class="{{ $isFull ? 'field-full' : '' }}">
                <label class="field-label" for="field_{{ $field['name'] }}">{{ $field['label'] }}</label>
                @if($field['type'] === 'select')
                  <select class="select" id="field_{{ $field['name'] }}" name="{{ $field['name'] }}" {{ !empty($field['required']) ? 'required' : '' }}>
                    <option value="">Select {{ strtolower($field['label']) }}</option>
                    @foreach($field['options'] as $option)
                      <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                  </select>
                @elseif($field['type'] === 'textarea')
                  <textarea class="textarea" id="field_{{ $field['name'] }}" name="{{ $field['name'] }}" {{ !empty($field['required']) ? 'required' : '' }}></textarea>
                @else
                  <input class="input" id="field_{{ $field['name'] }}" name="{{ $field['name'] }}" type="{{ $field['type'] }}" {{ !empty($field['required']) ? 'required' : '' }}>
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
</body>
</html>
