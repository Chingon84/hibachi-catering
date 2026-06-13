@extends('layouts.admin')

@section('title', 'Notifications')

@push('styles')
  <style>
    .notifications-page{padding:24px;background:#f8fafc;min-height:100vh}
    .notifications-shell{display:flex;flex-direction:column;gap:14px}
    .notifications-header{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap}
    .notifications-title{margin:0;font-size:24px;line-height:1.1;letter-spacing:-.02em;color:#111827}
    .notifications-subtitle{margin:6px 0 0;color:#64748b;font-size:13px}
    .notifications-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
    .notifications-filter-card{padding:14px;border:1px solid #dfe5ee;border-radius:14px;background:#fff;box-shadow:0 8px 22px rgba(15,23,42,.04)}
    .notifications-filters{display:grid;grid-template-columns:1fr 180px 180px auto;gap:10px;align-items:end}
    .filter-label{display:block;font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#64748b;margin-bottom:6px}
    .notification-list{display:flex;flex-direction:column;border:1px solid #dfe5ee;border-radius:14px;background:#fff;box-shadow:0 12px 30px rgba(15,23,42,.05);overflow:hidden}
    .notification-row{display:grid;grid-template-columns:42px 1fr auto;gap:12px;padding:14px;border-bottom:1px solid #edf1f6;background:#fff}
    .notification-row:last-child{border-bottom:0}
    .notification-row.unread{background:#f8fbff}
    .notification-icon{width:38px;height:38px;border-radius:12px;display:flex;align-items:center;justify-content:center;border:1px solid #dbe4ef;background:#f8fafc;color:#243b53}
    .notification-icon svg{width:18px;height:18px}
    .notification-body{min-width:0}
    .notification-topline{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
    .notification-title{font-size:14px;font-weight:800;color:#111827}
    .notification-message{font-size:13px;color:#475569;margin-top:3px}
    .notification-meta{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:8px;color:#64748b;font-size:12px}
    .notification-pill{display:inline-flex;align-items:center;min-height:22px;padding:3px 8px;border-radius:999px;border:1px solid #dbe4ef;background:#fff;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#475569}
    .notification-pill.unread{background:#eaf2ff;border-color:#c9daf5;color:#1d4ed8}
    .notification-row-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end}
    .notification-empty{padding:44px 18px;text-align:center;color:#64748b}
    .notification-empty strong{display:block;color:#111827;font-size:15px;margin-bottom:4px}
    .inline-form{display:inline}
    @media (max-width:860px){
      .notifications-page{padding:16px}
      .notifications-filters{grid-template-columns:1fr}
      .notification-row{grid-template-columns:36px 1fr}
      .notification-row-actions{grid-column:2;justify-content:flex-start}
    }
  </style>
@endpush

@section('content')
  <div class="notifications-page">
    <div class="notifications-shell">
      <header class="notifications-header">
        <div>
          <h1 class="notifications-title">Notifications</h1>
          <p class="notifications-subtitle">Internal alerts for your assigned work, updates, and admin follow-up.</p>
        </div>
        <div class="notifications-actions">
          @if($unreadCount > 0)
            <form method="post" action="{{ route('admin.notifications.read-all') }}">
              @csrf
              <button class="btn secondary" type="submit">Mark all read</button>
            </form>
          @endif
        </div>
      </header>

      <section class="notifications-filter-card">
        <form class="notifications-filters" method="get" action="{{ route('admin.notifications.index') }}">
          <label>
            <span class="filter-label">Search</span>
            <input class="input" type="search" name="q" value="{{ $q }}" placeholder="Search notifications...">
          </label>
          <label>
            <span class="filter-label">Status</span>
            <select class="select" name="status">
              <option value="all" @selected($status === 'all')>All</option>
              <option value="unread" @selected($status === 'unread')>Unread</option>
              <option value="read" @selected($status === 'read')>Read</option>
            </select>
          </label>
          <label>
            <span class="filter-label">Type</span>
            <select class="select" name="type">
              @foreach($typeOptions as $value => $label)
                <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
              @endforeach
            </select>
          </label>
          <button class="btn" type="submit">Filter</button>
        </form>
      </section>

      <section class="notification-list" aria-label="Notifications list">
        @forelse($notifications as $notification)
          @php
            $icon = match($notification->type) {
              'event', 'staff_booking' => 'calendar',
              'task' => 'check',
              'note' => 'note',
              'invoice' => 'invoice',
              'employee' => 'user',
              default => 'bell',
            };
          @endphp
          <article class="notification-row {{ $notification->read_at ? '' : 'unread' }}">
            <div class="notification-icon" aria-hidden="true">
              @if($icon === 'calendar')
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v4M16 2v4M3 10h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/></svg>
              @elseif($icon === 'check')
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
              @elseif($icon === 'invoice')
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 3h10a2 2 0 0 1 2 2v16l-4-2-3 2-3-2-4 2V5a2 2 0 0 1 2-2Z"/><path d="M9 8h6M9 12h6"/></svg>
              @elseif($icon === 'user')
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
              @else
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
              @endif
            </div>
            <div class="notification-body">
              <div class="notification-topline">
                <span class="notification-title">{{ $notification->title }}</span>
                <span class="notification-pill">{{ str_replace('_', ' ', $notification->type) }}</span>
                @unless($notification->read_at)
                  <span class="notification-pill unread">Unread</span>
                @endunless
              </div>
              <div class="notification-message">{{ $notification->message }}</div>
              <div class="notification-meta">
                <span>{{ $notification->created_at?->format('M j, Y g:i A') }}</span>
                @if($notification->created_at)
                  <span>{{ $notification->created_at->diffForHumans() }}</span>
                @endif
              </div>
            </div>
            <div class="notification-row-actions">
              @if($notification->url)
                <a class="btn ghost" href="{{ $notification->url }}">Open</a>
              @endif
              @unless($notification->read_at)
                <form class="inline-form" method="post" action="{{ route('admin.notifications.read', $notification) }}">
                  @csrf
                  <button class="btn secondary" type="submit">Mark read</button>
                </form>
              @endunless
              <form class="inline-form" method="post" action="{{ route('admin.notifications.destroy', $notification) }}">
                @csrf
                @method('DELETE')
                <button class="btn ghost" type="submit">Delete</button>
              </form>
            </div>
          </article>
        @empty
          <div class="notification-empty">
            <strong>No notifications found</strong>
            <span>New internal alerts will appear here.</span>
          </div>
        @endforelse
      </section>

      @include('admin.partials.pagination', ['paginator' => $notifications])
    </div>
  </div>
@endsection
