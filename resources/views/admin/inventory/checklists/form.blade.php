@extends('layouts.admin')

@section('title', $record->exists ? 'Edit Checklist' : 'New Checklist')

@push('styles')
<style>
    .page{display:grid;gap:12px}
    .hero{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap}
    .hero-copy{display:grid;gap:4px;max-width:760px}
    .hero-kicker{font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#64748b}
    .hero-title{margin:0;font-size:26px;line-height:1.08;font-weight:800;color:#0f172a}
    .hero-note{font-size:13px;line-height:1.45;color:#475569}
    .hero-actions{display:flex;gap:10px;flex-wrap:wrap}
    .summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
    .summary-card{border:1px solid #e2e8f0;border-radius:14px;background:#fff;padding:10px 12px;box-shadow:0 8px 18px rgba(15,23,42,.04)}
    .summary-label{font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#64748b}
    .summary-value{margin-top:4px;font-size:14px;font-weight:750;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .form-shell{display:grid;gap:12px}
    .section-card{border:1px solid #e2e8f0;border-radius:16px;background:#fff;box-shadow:0 10px 24px rgba(15,23,42,.045)}
    .section-head{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;padding:14px 16px 0}
    .section-title{margin:0;font-size:15px;font-weight:800;color:#0f172a}
    .section-copy{margin-top:2px;font-size:12px;line-height:1.42;color:#64748b}
    .section-tag{display:inline-flex;align-items:center;gap:6px;padding:4px 8px;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;white-space:nowrap}
    .section-body{padding:14px 16px 16px}
    .grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:11px}
    .field{display:flex;flex-direction:column;gap:5px}
    .field.col-3{grid-column:span 3}
    .field.col-4{grid-column:span 4}
    .field.col-6{grid-column:span 6}
    .field.col-12{grid-column:1 / -1}
    .label{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#64748b}
    .hint{font-size:11px;line-height:1.35;color:#94a3b8}
    .input,.select,.textarea{width:100%;min-height:38px;border:1px solid #dbe2ea;border-radius:10px;background:#fff;padding:7px 10px;font-size:13px;line-height:1.35;color:#0f172a;box-shadow:inset 0 1px 2px rgba(15,23,42,.03)}
    .textarea{min-height:78px;resize:vertical;padding-top:9px;padding-bottom:9px}
    .input:focus,.select:focus,.textarea:focus{outline:none;border-color:#93c5fd;box-shadow:0 0 0 4px rgba(37,99,235,.12)}
    .upload-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
    .upload-tile{display:grid;gap:9px;padding:12px;border:1px dashed #cbd5e1;border-radius:14px;background:#f8fafc}
    .upload-preview{display:flex;gap:8px;flex-wrap:wrap}
    .upload-preview img{width:76px;height:76px;object-fit:cover;border-radius:12px;border:1px solid #dbe2ea;background:#fff}
    .inline-check{display:flex;align-items:center;gap:8px;font-size:12px;color:#64748b}
    .action-card{position:sticky;bottom:0;z-index:5;box-shadow:0 -10px 24px rgba(15,23,42,.06)}
    .footbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .footnote{font-size:11px;line-height:1.45;color:#64748b;max-width:640px}
    .form-actions{display:flex;gap:10px;flex-wrap:wrap}
    .btn.soft{background:#fff;border:1px solid #dbe2ea;color:#334155}
    .btn.save{background:#16a34a}
    .btn.save:hover{background:#15803d}
    @media (max-width: 1080px){
      .summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
      .field.col-3,.field.col-4,.field.col-6{grid-column:span 6}
      .upload-grid{grid-template-columns:1fr}
    }
    @media (max-width: 760px){
      .summary-grid{grid-template-columns:1fr}
      .field.col-3,.field.col-4,.field.col-6,.field.col-12{grid-column:1 / -1}
      .section-head,.section-body{padding-left:14px;padding-right:14px}
      .hero-title{font-size:24px}
      .action-card{position:static}
    }
  </style>
@endpush

@section('content')
  <div class="container">
    <div class="page">
      <div class="hero">
        <div class="hero-copy">
          <div class="hero-kicker">Inventory / Van Operations</div>
          <h1 class="hero-title">{{ $record->exists ? 'Edit Van Checklist' : 'New Van Checklist' }}</h1>
          <div class="hero-note">Capture dispatch, return, and maintenance checklists in one structured operational form. Equipment counts, cleanliness, damage notes, and photo evidence stay aligned in the same workflow.</div>
        </div>
        <div class="hero-actions">
          <a class="btn soft" href="{{ route('admin.inventory.checklists.index') }}">Back to Records</a>
        </div>
      </div>

      @if ($errors->any())
        <div class="card"><div class="card-body" style="color:#b91c1c;font-weight:700">
          Please review the checklist form and correct the highlighted fields.
        </div></div>
      @endif

      @include('admin.inventory._subnav')

      <div class="summary-grid">
        <div class="summary-card">
          <div class="summary-label">Checklist</div>
          <div class="summary-value">{{ old('checklist_type', $record->checklist_type ?: 'Dispatch') }}</div>
        </div>
        <div class="summary-card">
          <div class="summary-label">Trip Status</div>
          <div class="summary-value">{{ old('trip_status', $record->trip_status ?: 'Complete') }}</div>
        </div>
        <div class="summary-card">
          <div class="summary-label">Van</div>
          <div class="summary-value">{{ old('van_number', $record->van_number ?: 'Select van') }}</div>
        </div>
        <div class="summary-card">
          <div class="summary-label">Logged By</div>
          <div class="summary-value">{{ old('user', $record->user ?: (auth()->user()->name ?? '')) }}</div>
        </div>
      </div>

      <form
        method="post"
        enctype="multipart/form-data"
        action="{{ $record->exists ? route('admin.inventory.checklists.update', $record->id) : route('admin.inventory.checklists.store') }}"
        class="form-shell"
      >
        @csrf

        <section class="section-card">
          <div class="section-head">
            <div>
              <h2 class="section-title">Trip Info</h2>
              <div class="section-copy">Define the operational context first so every record is easy to audit later.</div>
            </div>
            <div class="section-tag">Operational Header</div>
          </div>
          <div class="section-body">
            <div class="grid">
              <div class="field col-3">
                <label class="label" for="date_time">Date &amp; Time</label>
                <input class="input" id="date_time" type="datetime-local" name="date_time" value="{{ old('date_time', optional($record->date_time)->format('Y-m-d\\TH:i') ?? now()->format('Y-m-d\\TH:i')) }}" required>
              </div>
              <div class="field col-3">
                <label class="label" for="user">User</label>
                <input class="input" id="user" type="text" name="user" value="{{ old('user', $record->user) }}" required>
              </div>
              <div class="field col-3">
                <label class="label" for="checklist_type">Checklist Type</label>
                <select class="select" id="checklist_type" name="checklist_type" required>
                  @foreach ($checklistTypeOptions as $option)
                    <option value="{{ $option }}" {{ old('checklist_type', $record->checklist_type) === $option ? 'selected' : '' }}>{{ $option }}</option>
                  @endforeach
                </select>
              </div>
              <div class="field col-3">
                <label class="label" for="trip_status">Trip Status</label>
                <select class="select" id="trip_status" name="trip_status" required>
                  @foreach ($tripStatusOptions as $option)
                    <option value="{{ $option }}" {{ old('trip_status', $record->trip_status) === $option ? 'selected' : '' }}>{{ $option }}</option>
                  @endforeach
                </select>
              </div>

              <div class="field col-3">
                <label class="label" for="van_number">Van Selection</label>
                <select class="select" id="van_number" name="van_number" required>
                  <option value="">Select van</option>
                  @foreach ($vanOptions as $van)
                    <option value="{{ $van['value'] }}" {{ old('van_number', $record->van_number) === $van['value'] ? 'selected' : '' }}>{{ $van['label'] }}</option>
                  @endforeach
                </select>
                <div class="hint">Uses the current van registry.</div>
              </div>
              <div class="field col-3">
                <label class="label" for="gas_level">Gas Level</label>
                <select class="select" id="gas_level" name="gas_level" required>
                  @foreach ($gasLevelOptions as $option)
                    <option value="{{ $option }}" {{ old('gas_level', $record->gas_level) === $option ? 'selected' : '' }}>{{ $option }}</option>
                  @endforeach
                </select>
              </div>
              <div class="field col-3">
                <label class="label" for="clean">Clean Status</label>
                <select class="select" id="clean" name="clean">
                  <option value="">Select status</option>
                  @foreach ($cleanOptions as $option)
                    <option value="{{ $option }}" {{ old('clean', $record->clean) === $option ? 'selected' : '' }}>{{ $option }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
        </section>

        <section class="section-card">
          <div class="section-head">
            <div>
              <h2 class="section-title">Equipment Count</h2>
              <div class="section-copy">Track inventory quantities in one compact sweep before the van leaves or returns.</div>
            </div>
            <div class="section-tag" style="background:#fef3c7;color:#92400e">Loadout</div>
          </div>
          <div class="section-body">
            <div class="grid">
              <div class="field col-3">
                <label class="label" for="grills">Grills</label>
                <input class="input" id="grills" type="number" min="0" name="grills" value="{{ old('grills', $record->grills ?? 0) }}" required>
              </div>
              <div class="field col-3">
                <label class="label" for="grills_numbers">Grill Numbers</label>
                <input class="input" id="grills_numbers" type="text" name="grills_numbers" value="{{ old('grills_numbers', $record->grills_numbers) }}" placeholder="Serials or tag IDs">
              </div>
              <div class="field col-3">
                <label class="label" for="propane">Propane</label>
                <input class="input" id="propane" type="number" min="0" name="propane" value="{{ old('propane', $record->propane ?? 0) }}" required>
              </div>
              <div class="field col-3">
                <label class="label" for="tables">Tables</label>
                <input class="input" id="tables" type="number" min="0" name="tables" value="{{ old('tables', $record->tables ?? 0) }}" required>
              </div>

              <div class="field col-3">
                <label class="label" for="chairs">Chairs</label>
                <input class="input" id="chairs" type="number" min="0" name="chairs" value="{{ old('chairs', $record->chairs ?? 0) }}" required>
              </div>
              <div class="field col-3">
                <label class="label" for="chairs_covers">Chair Covers</label>
                <input class="input" id="chairs_covers" type="number" min="0" name="chairs_covers" value="{{ old('chairs_covers', $record->chairs_covers ?? 0) }}" required>
              </div>
              <div class="field col-3">
                <label class="label" for="dolly">Dolly</label>
                <input class="input" id="dolly" type="number" min="0" name="dolly" value="{{ old('dolly', $record->dolly ?? 0) }}" required>
              </div>

              <div class="field col-3">
                <label class="label" for="ramps">Ramps</label>
                <input class="input" id="ramps" type="number" min="0" name="ramps" value="{{ old('ramps', $record->ramps ?? 0) }}" required>
              </div>
              <div class="field col-3">
                <label class="label" for="mats">Mats</label>
                <input class="input" id="mats" type="number" min="0" name="mats" value="{{ old('mats', $record->mats ?? 0) }}" required>
              </div>
            </div>
          </div>
        </section>

        <section class="section-card">
          <div class="section-head">
            <div>
              <h2 class="section-title">Condition</h2>
              <div class="section-copy">Capture cleanliness and operational notes in one review section.</div>
            </div>
            <div class="section-tag" style="background:#fee2e2;color:#b91c1c">Review</div>
          </div>
          <div class="section-body">
            <div class="grid">
              <div class="field col-12">
                <label class="label" for="notes">Notes</label>
                <textarea class="textarea" id="notes" name="notes" placeholder="Operational notes, follow-up items, or handoff instructions.">{{ old('notes', $record->notes) }}</textarea>
              </div>
            </div>
          </div>
        </section>

        <section class="section-card">
          <div class="section-head">
            <div>
              <h2 class="section-title">Evidence</h2>
              <div class="section-copy">Attach visual proof for dispatch condition, return damage, or maintenance review.</div>
            </div>
            <div class="section-tag" style="background:#ede9fe;color:#6d28d9">Media</div>
          </div>
          <div class="section-body">
            <div class="upload-grid">
              <div class="upload-tile">
                <div>
                  <label class="label" for="picture1">Picture 1</label>
                  <div class="hint">Front, loadout, or damage overview.</div>
                </div>
                @if($record->picture1)
                  <div class="upload-preview">
                    <a href="{{ \App\Support\UploadedFiles::url($record->picture1) }}" target="_blank" rel="noopener"><img src="{{ \App\Support\UploadedFiles::url($record->picture1) }}" alt="Picture 1"></a>
                  </div>
                @endif
                <input class="input" id="picture1" type="file" name="picture1" accept="image/*">
                @if($record->picture1)
                  <label class="inline-check"><input type="checkbox" name="remove_picture1" value="1"> Remove current image</label>
                @endif
              </div>

              <div class="upload-tile">
                <div>
                  <label class="label" for="picture2">Picture 2</label>
                  <div class="hint">Interior, equipment placement, or additional evidence.</div>
                </div>
                @if($record->picture2)
                  <div class="upload-preview">
                    <a href="{{ \App\Support\UploadedFiles::url($record->picture2) }}" target="_blank" rel="noopener"><img src="{{ \App\Support\UploadedFiles::url($record->picture2) }}" alt="Picture 2"></a>
                  </div>
                @endif
                <input class="input" id="picture2" type="file" name="picture2" accept="image/*">
                @if($record->picture2)
                  <label class="inline-check"><input type="checkbox" name="remove_picture2" value="1"> Remove current image</label>
                @endif
              </div>
            </div>
          </div>
        </section>

        <section class="section-card action-card">
          <div class="section-body">
            <div class="footbar">
              <div class="footnote">This checklist feeds the operational records log. Keep counts accurate, use trip status intentionally, and add photos whenever the van condition needs evidence.</div>
              <div class="form-actions">
                <a class="btn soft" href="{{ route('admin.inventory.checklists.index') }}">Cancel</a>
                <button class="btn save" type="submit">Save Checklist</button>
              </div>
            </div>
          </div>
        </section>
      </form>
    </div>
  </div>
@endsection
