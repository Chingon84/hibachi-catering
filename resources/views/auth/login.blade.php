<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login - Hibachi Catering</title>
  <style>
    :root{--bg:#f7f7fb;--text:#111827;--muted:#64748b;--card:#fff;--border:#e5e7eb;--brand:#b21e27;--brand-hover:#9a1a22;--soft:#f8fafc;--ring:rgba(178,30,39,.14)}
    *{box-sizing:border-box}
    html{-webkit-text-size-adjust:100%;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
    body{
      margin:0;
      min-height:100vh;
      background:
        radial-gradient(circle at 16% 12%,rgba(178,30,39,.09),transparent 28%),
        radial-gradient(circle at 86% 84%,rgba(36,59,83,.08),transparent 30%),
        linear-gradient(145deg,#f8fafc 0%,#f7f7fb 48%,#fff7f7 100%);
      color:var(--text);
      font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
    }
    body::before{
      content:"";
      position:fixed;
      inset:0;
      pointer-events:none;
      background-image:
        linear-gradient(rgba(148,163,184,.12) 1px,transparent 1px),
        linear-gradient(90deg,rgba(148,163,184,.12) 1px,transparent 1px);
      background-size:44px 44px;
      mask-image:linear-gradient(180deg,rgba(0,0,0,.42),transparent 72%);
    }
    .wrap{position:relative;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:28px 18px}
    .login-shell{width:100%;max-width:440px}
    .brand-panel{text-align:center;margin:0 auto 16px}
    .brand-mark{
      width:74px;
      height:74px;
      margin:0 auto 14px;
      border-radius:18px;
      border:1px solid rgba(178,30,39,.16);
      background:#fff;
      display:flex;
      align-items:center;
      justify-content:center;
      box-shadow:0 16px 36px rgba(15,23,42,.08);
      overflow:hidden;
    }
    .brand-mark img{width:58px;height:58px;object-fit:contain}
    .brand-fallback{font-weight:900;color:var(--brand);font-size:20px;letter-spacing:.04em}
    .brand-kicker{margin:0 0 5px;font-size:13px;font-weight:900;letter-spacing:.12em;color:#111827;text-transform:uppercase}
    .brand-context{margin:0;color:#475569;font-size:14px;font-weight:700}
    .brand-location{display:inline-flex;align-items:center;gap:6px;margin-top:10px;padding:5px 10px;border:1px solid #e2e8f0;border-radius:999px;background:rgba(255,255,255,.72);color:#64748b;font-size:12px;font-weight:800}
    .brand-location::before{content:"";width:7px;height:7px;border-radius:999px;background:var(--brand);box-shadow:0 0 0 3px rgba(178,30,39,.12)}
    .card{
      width:100%;
      position:relative;
      overflow:hidden;
      background:var(--card);
      border:1px solid var(--border);
      border-radius:16px;
      padding:26px;
      box-shadow:0 22px 55px rgba(15,23,42,.12),0 2px 8px rgba(15,23,42,.04);
    }
    .card::before{content:"";position:absolute;inset:0 0 auto;height:4px;background:linear-gradient(90deg,var(--brand),#ef4444)}
    .card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:20px}
    .title{margin:0 0 6px;font-size:24px;line-height:1.15;letter-spacing:0}
    .sub{margin:0;color:var(--muted);font-size:14px;line-height:1.45}
    .secure-badge{flex:0 0 auto;display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border-radius:999px;background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-size:12px;font-weight:800;white-space:nowrap}
    .secure-badge svg{width:14px;height:14px;color:var(--brand)}
    .field{margin-bottom:15px}
    .field-row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:7px}
    .label{display:block;font-size:13px;font-weight:800;color:#111827}
    .input{
      width:100%;
      min-height:46px;
      padding:11px 13px;
      border:1px solid #dbe2ea;
      border-radius:11px;
      background:#fff;
      color:var(--text);
      font:inherit;
      line-height:1.4;
      transition:border-color .15s ease,box-shadow .15s ease,background-color .15s ease;
    }
    .input::placeholder{color:#94a3b8}
    .input:focus{outline:none;border-color:#cbd5e1;box-shadow:0 0 0 4px rgba(148,163,184,.16)}
    .input[aria-invalid="true"]{border-color:#fca5a5;background:#fffafa}
    .input[aria-invalid="true"]:focus{box-shadow:0 0 0 4px rgba(178,30,39,.12)}
    .password-field{position:relative}
    .password-field .input{padding-right:46px}
    .password-toggle{position:absolute;top:50%;right:10px;transform:translateY(-50%);width:34px;height:34px;background:#fff;border:1px solid transparent;border-radius:9px;padding:0;margin:0;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--muted);transition:background-color .15s ease,border-color .15s ease,color .15s ease}
    .password-toggle:hover{background:#f8fafc;border-color:#e2e8f0;color:#334155}
    .password-toggle:focus-visible{outline:2px solid var(--brand);outline-offset:2px}
    .password-toggle svg{width:20px;height:20px}
    .password-toggle .eye-closed{display:none}
    .password-toggle[data-visible="true"]{color:var(--brand)}
    .password-toggle[data-visible="true"] .eye-open{display:none}
    .password-toggle[data-visible="true"] .eye-closed{display:inline}
    .form-options{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:2px 0 18px}
    .remember{display:inline-flex;align-items:center;gap:9px;color:#334155;font-size:14px;font-weight:700;line-height:1.2;cursor:pointer}
    .remember input{width:16px;height:16px;margin:0;accent-color:var(--brand)}
    .btn{
      width:100%;
      min-height:46px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:8px;
      padding:11px 16px;
      border-radius:11px;
      border:1px solid var(--brand);
      background:var(--brand);
      color:#fff;
      font:inherit;
      font-weight:900;
      text-decoration:none;
      cursor:pointer;
      box-shadow:0 14px 26px rgba(178,30,39,.2);
      transition:background-color .15s ease,border-color .15s ease,box-shadow .15s ease,transform .12s ease,opacity .15s ease;
    }
    .btn:hover{background:var(--brand-hover);border-color:var(--brand-hover);box-shadow:0 16px 30px rgba(178,30,39,.26)}
    .btn:focus-visible{outline:none;box-shadow:0 0 0 4px var(--ring),0 14px 26px rgba(178,30,39,.2)}
    .btn:active{transform:translateY(1px)}
    .btn[disabled]{cursor:not-allowed;opacity:.72;box-shadow:none;transform:none}
    .error-alert{display:flex;gap:10px;align-items:flex-start;margin:0 0 18px;padding:11px 12px;border:1px solid #fecaca;border-radius:11px;background:#fef2f2;color:#991b1b;font-size:14px;font-weight:700}
    .error-alert svg{width:18px;height:18px;flex:0 0 18px;margin-top:1px}
    .status-alert{display:flex;gap:10px;align-items:flex-start;margin:0 0 18px;padding:11px 12px;border:1px solid #bbf7d0;border-radius:11px;background:#f0fdf4;color:#166534;font-size:14px;font-weight:700}
    .status-alert svg{width:18px;height:18px;flex:0 0 18px;margin-top:1px}
    .text-link{color:var(--brand);font-size:13px;font-weight:800;text-decoration:none}
    .text-link:hover{text-decoration:underline;color:var(--brand-hover)}
    .footer-note{text-align:center;margin:16px 0 0;color:#64748b;font-size:13px;line-height:1.5}
    .footer-note strong{display:block;color:#334155;font-size:13px}
    @media (max-width:480px){
      .wrap{align-items:flex-start;padding-top:34px}
      .brand-mark{width:66px;height:66px;border-radius:16px}
      .brand-mark img{width:52px;height:52px}
      .card{padding:22px 18px;border-radius:14px}
      .card-head{display:block}
      .secure-badge{margin-top:12px}
      .title{font-size:22px}
    }
  </style>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  </head>
<body>
  <div class="wrap">
    <main class="login-shell" aria-labelledby="login-title">
      <section class="brand-panel" aria-label="Hibachi Admin">
        <div class="brand-mark" aria-hidden="true">
          <img src="/assets/brand/logo.png" alt="" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
          <span class="brand-fallback" hidden>HA</span>
        </div>
        <p class="brand-kicker">HIBACHI ADMIN</p>
        <p class="brand-context">Hibachi Catering Operations Panel</p>
        <span class="brand-location">Corona HQ</span>
      </section>

      <section class="card" aria-label="Admin login form">
        <div class="card-head">
          <div>
            <h1 class="title" id="login-title">Admin Login</h1>
            <p class="sub">Secure access for authorized staff.</p>
          </div>
          <span class="secure-badge">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2 5 5v6c0 4.6 2.9 8.8 7 10 4.1-1.2 7-5.4 7-10V5l-7-3zm0 2.2 5 2.15V11c0 3.3-1.95 6.5-5 7.8-3.05-1.3-5-4.5-5-7.8V6.35l5-2.15z"/><path d="M11 13.2 8.8 11l-1.4 1.4L11 16l6-6-1.4-1.4z"/></svg>
            Secure admin access
          </span>
        </div>

        @if ($errors->any())
          <div class="error-alert" id="form-error" role="alert">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11 7h2v7h-2V7zm0 9h2v2h-2v-2z"/><path d="m12 2 11 20H1L12 2zm0 4.15L4.38 20h15.24L12 6.15z"/></svg>
            <span>{{ $errors->first() }}</span>
          </div>
        @endif

        @if (session('status'))
          <div class="status-alert" role="status">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm-1.2 14.6-4-4 1.4-1.4 2.6 2.58 5.98-5.98 1.42 1.42-7.4 7.38z"/></svg>
            <span>{{ session('status') }}</span>
          </div>
        @endif

        <form method="post" action="{{ route('login.submit') }}" data-login-form>
          @csrf
          <div class="field">
            <div class="field-row">
              <label class="label" for="login">Email or Username</label>
            </div>
            <input
              class="input"
              id="login"
              name="login"
              value="{{ old('login') }}"
              placeholder="Enter your email or username"
              autocomplete="username"
              autofocus
              required
              aria-invalid="{{ $errors->has('login') ? 'true' : 'false' }}"
              @if($errors->has('login')) aria-describedby="form-error" @endif
            >
          </div>

          <div class="field">
            <div class="field-row">
              <label class="label" for="password">Password</label>
              <a class="text-link" href="{{ route('password.request') }}">Forgot your password?</a>
            </div>
            <div class="password-field">
              <input
                class="input"
                id="password"
                type="password"
                name="password"
                autocomplete="current-password"
                required
                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                @if($errors->has('password')) aria-describedby="form-error" @endif
              >
              <button class="password-toggle" type="button" aria-label="Show password" data-target="password" data-visible="false">
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

          <div class="form-options">
            <label class="remember" for="remember">
              <input type="checkbox" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
              <span>Remember me</span>
            </label>
          </div>

          <button class="btn" type="submit" data-login-submit data-default-text="Login" data-loading-text="Signing in...">Login</button>
        </form>
      </section>

      <p class="footer-note">
        <strong>Authorized personnel only.</strong>
        Need access? Contact the administrator.
      </p>
    </main>
  </div>
  <script>
    document.querySelectorAll('.password-toggle').forEach(function(btn){
      var targetId = btn.getAttribute('data-target');
      var input = document.getElementById(targetId);
      if (!input) return;

      var toggleState = function(makeVisible){
        input.type = makeVisible ? 'text' : 'password';
        btn.dataset.visible = makeVisible ? 'true' : 'false';
        btn.setAttribute('aria-label', makeVisible ? 'Hide password' : 'Show password');
      };

      btn.addEventListener('click', function(){
        var makeVisible = input.type === 'password';
        toggleState(makeVisible);
      });
    });

    document.querySelectorAll('[data-login-form]').forEach(function(form){
      form.addEventListener('submit', function(){
        var button = form.querySelector('[data-login-submit]');
        if (!button || button.disabled) return;

        button.disabled = true;
        button.textContent = button.getAttribute('data-loading-text') || 'Signing in...';
      });
    });
  </script>
</body>
</html>
