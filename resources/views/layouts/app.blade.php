<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>@yield('title','Reservations')</title>
  <link rel="stylesheet" href="/assets/reservations/css/reservations.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<div class="rs-shell">
  <div class="rs-header">
    <h1 class="rs-title">Reservations</h1>
    <div class="rs-step-meta">Step {{ $step ?? 1 }} of 5</div>
  </div>
  @yield('content')
</div>
@stack('scripts')
</body>
</html>
