<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <style>
    :root{--bg:#f7f7fb;--text:#111827;--muted:#6b7280;--card:#fff;--border:#e5e7eb;--brand:#b21e27;--brand-hover:#9a1a22}
    *{box-sizing:border-box}
    body{margin:0;background:var(--bg);color:var(--text);font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}
    .wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
    .card{width:100%;max-width:420px;background:#fff;border:1px solid var(--border);border-radius:14px;padding:24px;box-shadow:0 8px 24px rgba(0,0,0,.05)}
    .title{margin:0 0 8px;font-size:22px}
    .sub{margin:0 0 16px;color:var(--muted)}
    .field{margin-bottom:14px}
    .label{display:block;margin-bottom:6px;font-weight:600}
    .input{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:10px}
l      .password-field{position:relative}
    .password-toggle{position:absolute;top:50%;right:12px;transform:translateY(-50%);background:none;border:none;padding:0;margin:0;display:flex;align-items:center;cursor:pointer;color:var(--muted)}
    .password-toggle:focus-visible{outline:2px solid var(--brand);outline-offset:2px}
    .password-toggle svg{width:20px;height:20px}
    .password-toggle .eye-closed{display:none}
    .password-toggle[data-visible="true"]{color:var(--brand)}
    .password-toggle[data-visible="true"] .eye-open{display:none}
    .password-toggle[data-visible="true"] .eye-closed{display:inline}
    .btn{display:inline-flex;align-items:center;justify-content:center;padding:10px 14px;border-radius:12px;border:1px solid var(--brand);background:var(--brand);color:#fff;text-decoration:none;cursor:pointer}
    .btn:hover{background:var(--brand-hover);border-color:var(--brand-hover)}
    .error{color:#b21e27;font-size:14px;margin:6px 0 0}
  </style>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script>
    // Ensure fetch/axios have token if needed
  </script>
  </head>
<body>
  <div class="wrap">
    <div class="card">
      <h1 class="title">Admin Login</h1>
      <p class="sub">Use email or username and password.</p>
      @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
      @endif
      <form method="post" action="{{ route('login.submit') }}">
        @csrf
        <div class="field">
          <label class="label" for="login">Email or Username</label>
          <input class="input" id="login" name="login" value="{{ old('login') }}" required>
        </div>
        <div class="field">
          <label class="label" for="password">Password</label>
          <div class="password-field">
            <input class="input" id="password" type="password" name="password" autocomplete="current-password" required>
            <button class="password-toggle" type="button" aria-label="Mostrar contraseña" data-target="password" data-visible="false">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <g class="eye-open">
                  <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z" />
                  <circle cx="12" cy="12" r="3" />
                </g>
                <g class="eye-closed">
                  <path d="M17.94 17.94A10.9 10.9 0 0 1 12 19c-7 0-11-7-11-7a19.4 19.4 0 0 1 4.86-5.55" />
                  <path d="M9.88 9.88A3 3 0 0 0 9 12a3 3 0 0 0 5.19 2.12" />
                  <line x1="1" y1="1" x2="23" y2="23" />
                </g>
              </svg>
            </button>
          </div>
        </div>
        <div class="field" style="display:flex;align-items:center;gap:8px">
          <input type="checkbox" id="remember" name="remember" value="1">
          <label for="remember">Remember me</label>
        </div>
        <button class="btn" type="submit">Login</button>
      </form>
    </div>
  </div>
  <script>
    document.querySelectorAll('.password-toggle').forEach(function(btn){
      var targetId = btn.getAttribute('data-target');
      var input = document.getElementById(targetId);
      if (!input) return;

      var toggleState = function(makeVisible){
        input.type = makeVisible ? 'text' : 'password';
        btn.dataset.visible = makeVisible ? 'true' : 'false';
        btn.setAttribute('aria-label', makeVisible ? 'Ocultar contraseña' : 'Mostrar contraseña');
      };

      btn.addEventListener('click', function(){
        var makeVisible = input.type === 'password';
        toggleState(makeVisible);
      });
    });
  </script>
</body>
</html>
