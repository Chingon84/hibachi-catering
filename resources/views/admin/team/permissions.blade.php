<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Permissions Matrix</title>
  <style>
    :root{--bg:#f7f7fb;--text:#111827;--muted:#6b7280;--card:#fff;--border:#e5e7eb;--brand:#b21e27;--brand-hover:#9a1a22}
    body{margin:0;background:#fff;color:var(--text);font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}
    .wrap{padding:16px}
    .head{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
    .title{margin:0;font-size:20px}
    .btn{display:inline-flex;align-items:center;gap:6px;padding:8px 12px;border-radius:10px;border:1px solid var(--brand);background:var(--brand);color:#fff;text-decoration:none}
    .btn.secondary{background:#fff;color:#111;border-color:var(--border)}
    table{width:100%;border-collapse:collapse;border:1px solid var(--border);border-radius:12px;overflow:hidden}
    th,td{padding:10px;border-bottom:1px solid var(--border);text-align:left}
    th{background:#f9fafb;font-weight:700}
    .muted{color:var(--muted);font-size:12px}
    .center{text-align:center}
    input[type=checkbox]{width:18px;height:18px}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="head">
      <h1 class="title">Permissions Matrix</h1>
      <div style="display:flex;gap:8px">
        <a class="btn secondary" href="{{ route('admin.team.index') }}">Back to Team</a>
      </div>
    </div>
    @if (session('ok'))
      <div style="margin:8px 0;color:#065f46">{{ session('ok') }}</div>
    @endif

    <form method="post" action="{{ route('admin.team.permissions.update') }}">
      @csrf
      <table>
        <thead>
          <tr>
            <th>Description</th>
            @foreach ($roles as $role)
              <th class="center">{{ ucfirst($role) }}</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach ($permissions as $perm)
            <tr>
              <td>
                <div>{{ $perm }}</div>
                <div class="muted">Key: {{ $perm }}</div>
              </td>
              @foreach ($roles as $role)
                <td class="center">
                  @if ($role === 'owner')
                    <input type="checkbox" checked disabled title="Owner has all permissions">
                  @else
                    @php $checked = in_array($perm, $assigned[$role] ?? []); @endphp
                    <input type="checkbox" name="matrix[{{ $role }}][]" value="{{ $perm }}" {{ $checked ? 'checked' : '' }}>
                  @endif
                </td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>
      <div style="margin-top:12px;display:flex;gap:8px">
        <button class="btn" type="submit">Save Matrix</button>
        <a class="btn secondary" href="{{ route('admin.team.index') }}">Cancel</a>
      </div>
    </form>
  </div>
</body>
</html>

