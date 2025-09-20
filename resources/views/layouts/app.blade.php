<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>@yield('title','Reservations')</title>
  <link rel="stylesheet" href="/assets/reservations/css/reservations.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<div class="container">
  <div class="header">
    <h1>Reservations</h1>
    <div><small>Step {{ $step ?? 1 }} of 5</small></div>
  </div>
  <div class="card">
    @yield('content')
  </div>
</div>
@stack('scripts')
</body>
</html>
