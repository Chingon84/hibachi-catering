@extends('layouts.admin')

@section('title', $title ?? 'Coming soon')

@push('styles')
  <style>
    .placeholder-note{color:var(--muted);font-size:14px}
    .placeholder-card{max-width:560px}
  </style>
@endpush

@section('content')
  <div class="container">
    <div class="card placeholder-card">
      <div class="card-body">
        <p class="placeholder-note">This page is under construction.</p>
        <a class="btn" href="{{ route('admin.dashboard') }}">Back to Dashboard</a>
      </div>
    </div>
  </div>
@endsection
