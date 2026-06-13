@extends('layouts.staff')

@section('title', 'My Events')

@php
  $fmtDate = fn($date) => $date ? $date->format('l, F j, Y') : 'N/A';
  $fmtTime = function ($time) {
      if (blank($time)) return 'N/A';
      try { return \Carbon\Carbon::parse($time)->format('g:i A'); } catch (\Throwable $e) { return (string) $time; }
  };
  $fmtMoney = fn($amount) => '$'.number_format((float) $amount, 2);
  $addressLine = fn($event) => collect([$event->address, $event->city, $event->zip_code])->filter(fn($v) => filled($v))->implode(', ');
  $mapsUrl = fn($event) => ($addressLine($event) !== '') ? 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($addressLine($event)) : null;
  $staffRows = function ($event) {
      $assignment = $event->scheduleAssignment;
      if (!$assignment) return collect();
      $rows = $assignment->assignedStaffSummaryRows();
      if ($assignment->assistant) {
          $rows->push(['label' => 'Assistant', 'value' => $assignment->assistant->name]);
      }
      return $rows;
  };
@endphp

@section('content')
  <div class="page-head">
    <div>
      <h1>My Assigned Events</h1>
      <p>Today and upcoming reservations assigned to you from Schedule.</p>
    </div>
  </div>

  @include('staff.partials.event-section', ['title' => 'Today', 'events' => $todayEvents, 'emptyMessage' => 'No assigned events today.', 'sectionState' => 'today'])
  @include('staff.partials.event-section', ['title' => 'Upcoming Events', 'events' => $upcomingEvents, 'emptyMessage' => 'No upcoming assigned events.', 'sectionState' => 'upcoming'])

  @include('staff.partials.event-section', ['title' => 'Past Events', 'events' => $pastEvents, 'emptyMessage' => 'No recent past events.', 'sectionState' => 'past'])
@endsection
