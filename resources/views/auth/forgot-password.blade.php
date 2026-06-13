<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Reset Password - Hibachi Admin</title>
  <style>
    :root{--bg:#f7f7fb;--text:#111827;--muted:#64748b;--card:#fff;--border:#e5e7eb;--brand:#b21e27;--brand-hover:#9a1a22;--ring:rgba(178,30,39,.14)}
    *{box-sizing:border-box}
    html{-webkit-text-size-adjust:100%;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
    body{margin:0;min-height:100vh;background:radial-gradient(circle at 16% 12%,rgba(178,30,39,.09),transparent 28%),radial-gradient(circle at 86% 84%,rgba(36,59,83,.08),transparent 30%),linear-gradient(145deg,#f8fafc 0%,#f7f7fb 48%,#fff7f7 100%);color:var(--text);font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;-webkit-font-smoothing:antialiased}
    body::before{content:"";position:fixed;inset:0;pointer-events:none;background-image:linear-gradient(rgba(148,163,184,.12) 1px,transparent 1px),linear-gradient(90deg,rgba(148,163,184,.12) 1px,transparent 1px);background-size:44px 44px;mask-image:linear-gradient(180deg,rgba(0,0,0,.42),transparent 72%)}
    .wrap{position:relative;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:28px 18px}
    .shell{width:100%;max-width:440px}
    .brand-panel{text-align:center;margin:0 auto 16px}
    .brand-mark{width:74px;height:74px;margin:0 auto 14px;border-radius:18px;border:1px solid rgba(178,30,39,.16);background:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 16px 36px rgba(15,23,42,.08);overflow:hidden}
    .brand-mark img{width:58px;height:58px;object-fit:contain}
    .brand-fallback{font-weight:900;color:var(--brand);font-size:20px;letter-spacing:.04em}
    .brand-kicker{margin:0 0 5px;font-size:13px;font-weight:900;letter-spacing:.12em;color:#111827;text-transform:uppercase}
    .brand-context{margin:0;color:#475569;font-size:14px;font-weight:700}
    .card{position:relative;overflow:hidden;background:var(--card);border:1px solid var(--border);border-radius:16px;padding:26px;box-shadow:0 22px 55px rgba(15,23,42,.12),0 2px 8px rgba(15,23,42,.04)}
    .card::before{content:"";position:absolute;inset:0 0 auto;height:4px;background:linear-gradient(90deg,var(--brand),#ef4444)}
    .title{margin:0 0 6px;font-size:24px;line-height:1.15;letter-spacing:0}
    .sub{margin:0 0 20px;color:var(--muted);font-size:14px;line-height:1.45}
    .field{margin-bottom:16px}
    .label{display:block;font-size:13px;font-weight:800;color:#111827;margin-bottom:7px}
    .input{width:100%;min-height:46px;padding:11px 13px;border:1px solid #dbe2ea;border-radius:11px;background:#fff;color:var(--text);font:inherit;line-height:1.4;transition:border-color .15s ease,box-shadow .15s ease}
    .input::placeholder{color:#94a3b8}
    .input:focus{outline:none;border-color:#cbd5e1;box-shadow:0 0 0 4px rgba(148,163,184,.16)}
    .btn{width:100%;min-height:46px;display:inline-flex;align-items:center;justify-content:center;padding:11px 16px;border-radius:11px;border:1px solid var(--brand);background:var(--brand);color:#fff;font:inherit;font-weight:900;text-decoration:none;cursor:pointer;box-shadow:0 14px 26px rgba(178,30,39,.2);transition:background-color .15s ease,border-color .15s ease,box-shadow .15s ease,transform .12s ease}
    .btn:hover{background:var(--brand-hover);border-color:var(--brand-hover);box-shadow:0 16px 30px rgba(178,30,39,.26)}
    .btn:active{transform:translateY(1px)}
    .alert{display:flex;gap:10px;align-items:flex-start;margin:0 0 18px;padding:11px 12px;border-radius:11px;font-size:14px;font-weight:700}
    .alert.error{border:1px solid #fecaca;background:#fef2f2;color:#991b1b}
    .alert.status{border:1px solid #bbf7d0;background:#f0fdf4;color:#166534}
    .alert svg{width:18px;height:18px;flex:0 0 18px;margin-top:1px}
    .back-link{display:block;text-align:center;margin-top:16px;color:#64748b;font-size:13px;font-weight:800;text-decoration:none}
    .back-link:hover{color:var(--brand);text-decoration:underline}
    @media (max-width:480px){.wrap{align-items:flex-start;padding-top:34px}.card{padding:22px 18px;border-radius:14px}.title{font-size:22px}}
  </style>
</head>
<body>
  <div class="wrap">
    <main class="shell" aria-labelledby="reset-title">
      <section class="brand-panel" aria-label="Hibachi Admin">
        <div class="brand-mark" aria-hidden="true">
          <img src="/assets/brand/logo.png" alt="" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
          <span class="brand-fallback" hidden>HA</span>
        </div>
        <p class="brand-kicker">HIBACHI ADMIN</p>
        <p class="brand-context">Account recovery</p>
      </section>

      <section class="card">
        <h1 class="title" id="reset-title">Reset your password</h1>
        <p class="sub">Enter your email and we'll send you a reset link.</p>

        @if ($errors->any())
          <div class="alert error" role="alert">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11 7h2v7h-2V7zm0 9h2v2h-2v-2z"/><path d="m12 2 11 20H1L12 2zm0 4.15L4.38 20h15.24L12 6.15z"/></svg>
            <span>{{ $errors->first() }}</span>
          </div>
        @endif

        @if (session('status'))
          <div class="alert status" role="status">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm-1.2 14.6-4-4 1.4-1.4 2.6 2.58 5.98-5.98 1.42 1.42-7.4 7.38z"/></svg>
            <span>{{ session('status') }}</span>
          </div>
        @endif

        <form method="post" action="{{ route('password.email') }}">
          @csrf
          <div class="field">
            <label class="label" for="email">Email</label>
            <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" required autofocus>
          </div>
          <button class="btn" type="submit">Send Reset Link</button>
        </form>

        <a class="back-link" href="{{ route('login') }}">Back to login</a>
      </section>
    </main>
  </div>
</body>
</html>
