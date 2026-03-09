<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $expense->exists ? 'Edit Expense' : 'Add Expense' }}</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    .wrap{max-width:980px;margin:24px auto;padding:0 12px 24px}
    .panel{padding:22px}
    .panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px}
    .title{margin:0;font-size:28px;line-height:1.08}
    .subtitle{margin:8px 0 0;color:var(--muted);font-size:14px}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
    .field{display:flex;flex-direction:column;gap:6px}
    .field.span-2{grid-column:1 / -1}
    .error{margin-bottom:12px;color:#b91c1c;font-weight:700}
    .row{display:flex;gap:10px;margin-top:20px}
    @media (max-width: 760px){.grid{grid-template-columns:1fr}.panel-head{flex-direction:column}}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card panel">
      <div class="panel-head">
        <div>
          <h1 class="title">{{ $expense->exists ? 'Edit Expense' : 'Add Expense' }}</h1>
          <p class="subtitle">Manage manual expense entries used by the Financial Overview profit and loss dashboard.</p>
        </div>
        <a class="btn secondary" href="{{ $backUrl }}">Back to Financial Overview</a>
      </div>

      @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
      @endif

      <form method="post" action="{{ $expense->exists ? route('admin.expenses.update', $expense->id) : route('admin.expenses.store') }}">
        @csrf
        <input type="hidden" name="back" value="{{ $backUrl }}">
        <div class="grid">
          <div class="field">
            <label class="label" for="expense_date">Expense Date</label>
            <input class="input" type="date" id="expense_date" name="expense_date" value="{{ old('expense_date', $expense->expense_date?->toDateString()) }}" required>
          </div>
          <div class="field">
            <label class="label" for="category">Category</label>
            <select class="select" id="category" name="category" required>
              <option value="">Select category</option>
              @foreach ($categories as $category)
                <option value="{{ $category }}" {{ old('category', $expense->category) === $category ? 'selected' : '' }}>{{ $category }}</option>
              @endforeach
            </select>
          </div>
          <div class="field">
            <label class="label" for="description">Description</label>
            <input class="input" id="description" name="description" value="{{ old('description', $expense->description) }}" placeholder="Optional short description">
          </div>
          <div class="field">
            <label class="label" for="amount">Amount</label>
            <input class="input" type="number" step="0.01" min="0.01" id="amount" name="amount" value="{{ old('amount', $expense->amount) }}" required>
          </div>
          <div class="field span-2">
            <label class="label" for="notes">Notes</label>
            <textarea class="input" id="notes" name="notes" rows="5" style="min-height:140px">{{ old('notes', $expense->notes) }}</textarea>
          </div>
        </div>
        <div class="row">
          <a class="btn secondary" href="{{ $backUrl }}">Cancel</a>
          <button class="btn" type="submit">{{ $expense->exists ? 'Save Changes' : 'Save Expense' }}</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
