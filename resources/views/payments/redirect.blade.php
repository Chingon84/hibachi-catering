<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Redirecting to Payment…</title>
  <meta http-equiv="refresh" content="0;url={{ $url }}">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;margin:0;padding:24px;background:#f7f7fb;color:#111}
    .wrap{max-width:540px;margin:40px auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,.04);padding:18px}
    .btn{display:inline-block;background:#b21e27;color:#fff;padding:10px 14px;border-radius:10px;text-decoration:none}
  </style>
</head>
<body>
  <script>
    (function(){
      try { if (window.top) { window.top.location.href = {{ json_encode($url) }}; } else { window.location.href = {{ json_encode($url) }}; } } catch(e) { window.location.href = {{ json_encode($url) }}; }
    })();
  </script>
  <div class="wrap">
    <h2 style="margin-top:0">Redirecting to secure checkout…</h2>
    <p>If you are not redirected automatically, click the button below:</p>
    <p><a class="btn" href="{{ $url }}" target="_top" rel="noopener">Open Payment</a></p>
  </div>
</body>
</html>
