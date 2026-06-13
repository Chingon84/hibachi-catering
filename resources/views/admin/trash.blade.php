@extends('layouts.admin')

@section('title', 'Trash')

@push('styles')
  <style>
    .header.actions-only{justify-content:flex-end}
  </style>
@endpush

@section('content')
  <div class="container">
    <div class="header actions-only">
      <a href="{{ route('admin.reservations') }}" class="btn secondary">Back</a>
    </div>

    @if(session('ok'))
      <div class="card"><div class="card-body"><div class="alert success">{{ session('ok') }}</div></div></div>
    @endif
    @if($errors->any())
      <div class="card"><div class="card-body"><div class="alert error">{{ $errors->first() }}</div></div></div>
    @endif

    <div class="card">
      <div class="card-body">
        <table class="table">
          <thead><tr>
            <th>ID</th>
            <th>Invoice #</th>
            <th>Customer</th>
            <th>Date</th>
            <th>Deleted at</th>
            <th>Actions</th>
          </tr></thead>
          <tbody>
            @forelse($rows as $r)
              <tr>
                <td>{{ $r->id }}</td>
                <td>{{ $r->invoice_number ?? '—' }}</td>
                <td>{{ $r->customer_name ?? '—' }}</td>
                <td>{{ $r->date?->format('m/d/Y') }}</td>
                <td>{{ $r->deleted_at?->format('m/d/Y H:i') }}</td>
                <td>
                  <form method="post" action="{{ route('admin.trash.restore', ['id'=>$r->id]) }}" style="display:inline-block">
                    @csrf
                    <button class="btn ghost" type="submit">Restore</button>
                  </form>
                  <form method="post" action="{{ route('admin.trash.force', ['id'=>$r->id]) }}" style="display:inline-block" onsubmit="return confirm('Permanently delete this reservation and its items/payments?')">
                    @csrf
                    <button class="icon-btn danger" type="submit" title="Delete permanently" aria-label="Delete permanently"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 3h6l1 2h5v2H3V5h5l1-2zm1 6h2v10h-2V9zm4 0h2v10h-2V9zM7 9h2v10H7V9z"/></svg></button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="muted">Trash is empty.</td></tr>
            @endforelse
          </tbody>
        </table>
        @include('admin.partials.pagination', ['paginator' => $rows])
      </div>
    </div>
  </div>
@endsection
