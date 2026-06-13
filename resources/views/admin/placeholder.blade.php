<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'Coming soon' }}</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    .placeholder-note{color:var(--muted);font-size:14px}
    .placeholder-card{max-width:560px}
  </style>
  
</head>
<body>
  <div class="container">
    <div class="card placeholder-card">
      <div class="card-body">
        <p class="placeholder-note">This page is under construction.</p>
        <a class="btn" href="{{ route('admin.dashboard') }}">Back to Dashboard</a>
      </div>
    </div>
  </div>
</body>
</html>
