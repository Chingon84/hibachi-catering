<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $user->name }} · Employee Profile</title>
  <link rel="stylesheet" href="/assets/admin.css">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900">
  @php
    $tabMeta = [
      'overview' => 'Overview',
      'documents' => 'Documents',
      'incidents' => 'Incidents',
      'permissions' => 'Permissions',
      'activity' => 'Activity',
    ];
    $canManageTeam = (bool) (auth()->user()?->hasPermission('team.manage') ?? false);
    $nameParts = preg_split('/\s+/', trim((string) $user->name)) ?: [];
    $initials = collect($nameParts)->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'U';
    $profilePhotoUrl = \App\Support\UploadedFiles::url($user->profile_photo_path);
  @endphp

  <div class="mx-auto max-w-7xl px-4 py-4 sm:px-5 lg:px-6">
    <div class="space-y-4">
      <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-3">
          <div class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
            Employee Profile
          </div>
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex aspect-square h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-900 text-lg font-semibold text-white ring-1 ring-slate-200">
              @if($profilePhotoUrl)
                <img src="{{ $profilePhotoUrl }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
              @else
                {{ $initials }}
              @endif
            </div>
            <div class="min-w-0 space-y-2">
              <div class="flex flex-col gap-2 md:flex-row md:items-start">
                <div class="min-w-0">
                  <h1 class="truncate text-2xl font-semibold tracking-tight text-slate-950">{{ $user->name }}</h1>
                  <p class="text-sm text-slate-500">{{ $user->position ?: 'Position not set' }} · {{ $user->staff_type ?: 'Unassigned staff type' }}</p>
                </div>
              </div>
              <div class="flex flex-wrap gap-1.5">
                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-700">{{ strtoupper($user->role) }}</span>
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-medium {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                  {{ $user->is_active ? 'Active' : 'Inactive' }}
                </span>
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-medium {{ $user->can_access_admin ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600' }}">
                  {{ $user->can_access_admin ? 'Admin Access' : 'No Admin Access' }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="flex flex-wrap gap-2">
          <a href="{{ route('admin.team.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50">Back to Team</a>
          <a href="{{ route('admin.team.edit', $user->id) }}" class="inline-flex items-center rounded-xl bg-slate-900 px-3 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-slate-800">Edit Team Member</a>
        </div>
      </div>

      @if (session('ok'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700">
          {{ session('ok') }}
        </div>
      @endif

      @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-medium text-rose-700">
          {{ $errors->first() }}
        </div>
      @endif

      <div class="flex flex-wrap gap-1 rounded-2xl border border-slate-200 bg-white p-1.5 shadow-sm">
        @foreach ($tabMeta as $tabKey => $label)
          <a
            href="{{ route('admin.team.show', ['id' => $user->id, 'tab' => $tabKey]) }}"
            class="inline-flex items-center rounded-xl px-3 py-2 text-sm font-medium transition {{ $activeTab === $tabKey ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
          >
            {{ $label }}
          </a>
        @endforeach
      </div>

      @if ($activeTab === 'overview')
        <div class="grid gap-4 xl:grid-cols-[1.6fr_minmax(0,1fr)]">
          <div class="space-y-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
              <div class="mb-4">
                <h2 class="text-base font-semibold text-slate-950">Overview</h2>
                <p class="text-sm text-slate-500">Core employee information used across team operations and permissions.</p>
              </div>
              <dl class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                  <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Full name</dt>
                  <dd class="mt-2 text-sm font-medium text-slate-900">{{ $user->name }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                  <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Email</dt>
                  <dd class="mt-2 text-sm font-medium text-slate-900">{{ $user->email }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                  <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Phone</dt>
                  <dd class="mt-2 text-sm font-medium text-slate-900">{{ $user->phone ?: 'Not set' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                  <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Position</dt>
                  <dd class="mt-2 text-sm font-medium text-slate-900">{{ $user->position ?: 'Not set' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                  <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Staff type</dt>
                  <dd class="mt-2 text-sm font-medium text-slate-900">{{ $user->staff_type ?: 'Unassigned' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                  <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Employee type</dt>
                  <dd class="mt-2 text-sm font-medium text-slate-900">{{ $user->employee_type ?: 'Not set' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                  <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Employee number</dt>
                  <dd class="mt-2 text-sm font-medium text-slate-900">{{ $user->employee_number ?: 'Not set' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                  <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Role</dt>
                  <dd class="mt-2 text-sm font-medium text-slate-900">{{ strtoupper($user->role) }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                  <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Status</dt>
                  <dd class="mt-2 text-sm font-medium text-slate-900">{{ $user->is_active ? 'Active' : 'Inactive' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                  <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Hire date</dt>
                  <dd class="mt-2 text-sm font-medium text-slate-900">{{ $user->hire_date ? $user->hire_date->format('M d, Y') : 'Not available' }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <div class="space-y-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
              <div class="mb-4">
                <h2 class="text-base font-semibold text-slate-950">Operational Stats</h2>
                <p class="mt-1 text-sm text-slate-500">Recent feedback center records tied to this employee.</p>
              </div>
              <div class="grid gap-3">
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-3">
                  <div class="text-xs font-semibold uppercase tracking-[0.16em] text-rose-500">Complaints</div>
                  <div class="mt-1 text-2xl font-semibold text-rose-700">{{ $overviewStats['complaints'] }}</div>
                </div>
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-3">
                  <div class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-500">Attendance Incidents</div>
                  <div class="mt-1 text-2xl font-semibold text-blue-700">{{ $overviewStats['attendance'] }}</div>
                </div>
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3">
                  <div class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-500">Recognition</div>
                  <div class="mt-1 text-2xl font-semibold text-emerald-700">{{ $overviewStats['recognition'] }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endif

      @if ($activeTab === 'documents')
        <div class="space-y-4">
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
              <div>
                <h2 class="text-base font-semibold text-slate-950">Documents</h2>
                <p class="mt-1 text-sm text-slate-500">Upload employee documents, warnings, contracts, and licenses.</p>
              </div>
              <form method="post" action="{{ route('admin.team.documents.store', $user->id) }}" enctype="multipart/form-data" class="grid gap-3 sm:grid-cols-[180px_minmax(0,1fr)_auto]">
                @csrf
                <select name="type" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-300 focus:outline-none">
                  @foreach ($documentTypes as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                  @endforeach
                </select>
                <input name="file" type="file" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-slate-700">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-slate-800">Upload Document</button>
              </form>
            </div>
          </div>

          <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                  <tr>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">File</th>
                    <th class="px-4 py-3">Uploaded By</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  @forelse ($documents as $document)
                    <tr class="hover:bg-slate-50">
                      <td class="px-4 py-3 font-medium text-slate-900">{{ $document->type }}</td>
                      <td class="px-4 py-3 text-slate-600">{{ basename($document->file_path) }}</td>
                      <td class="px-4 py-3 text-slate-600">{{ $document->uploader?->name ?: 'System' }}</td>
                      <td class="px-4 py-3 text-slate-600">{{ optional($document->created_at)->format('M d, Y g:i A') }}</td>
                      <td class="px-4 py-3">
                        <div class="flex justify-end gap-2">
                          <a href="{{ route('admin.team.documents.view', [$user->id, $document->id]) }}" target="_blank" rel="noopener" class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-100">View</a>
                          <a href="{{ route('admin.team.documents.download', [$user->id, $document->id]) }}" class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-100">Download</a>
                          <form method="post" action="{{ route('admin.team.documents.delete', [$user->id, $document->id]) }}" onsubmit="return confirm('Delete this document?')">
                            @csrf
                            <button type="submit" class="inline-flex items-center rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-medium text-rose-600 transition hover:bg-rose-50">Delete</button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">No documents uploaded for this employee yet.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>

          @include('admin.partials.pagination', ['paginator' => $documents])
        </div>
      @endif

      @if ($activeTab === 'incidents')
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-200 px-4 py-4">
            <h2 class="text-base font-semibold text-slate-950">Incidents</h2>
            <p class="mt-1 text-sm text-slate-500">Feedback Center records automatically filtered to {{ $user->name }}.</p>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
              <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                <tr>
                  <th class="px-4 py-3">Type</th>
                  <th class="px-4 py-3">Reference</th>
                  <th class="px-4 py-3">Date</th>
                  <th class="px-4 py-3">Summary</th>
                  <th class="px-4 py-3">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                @forelse ($incidentFeed as $incident)
                  <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                      <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $incident['tone'] }}">
                        {{ $incident['kind'] }}
                      </span>
                    </td>
                    <td class="px-4 py-3 font-medium text-slate-900">{{ $incident['reference'] }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $incident['date'] ? \Carbon\Carbon::parse($incident['date'])->format('M d, Y') : '—' }}</td>
                    <td class="px-4 py-3">
                      <div class="font-medium text-slate-900">{{ $incident['summary'] }}</div>
                      <div class="mt-1 max-w-2xl text-slate-500">{{ $incident['detail'] }}</div>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $incident['status'] }}</td>
                  </tr>
                @empty
                  <tr>
                  <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">No incidents or feedback records are currently linked to this employee.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        @include('admin.partials.pagination', ['paginator' => $incidentFeed])
      @endif

      @if ($activeTab === 'permissions')
        <div class="grid gap-4 xl:grid-cols-[300px_minmax(0,1fr)]">
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="text-base font-semibold text-slate-950">Access Summary</h2>
            <dl class="mt-4 space-y-3">
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Role</dt>
                <dd class="mt-2 text-sm font-medium text-slate-900">{{ strtoupper($user->role) }}</dd>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Admin access</dt>
                <dd class="mt-2 text-sm font-medium text-slate-900">{{ $user->can_access_admin ? 'Enabled' : 'Disabled' }}</dd>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Status</dt>
                <dd class="mt-2 text-sm font-medium text-slate-900">{{ $user->is_active ? 'Active' : 'Inactive' }}</dd>
              </div>
            </dl>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="mb-4">
              <h2 class="text-base font-semibold text-slate-950">Module Access</h2>
              <p class="mt-1 text-sm text-slate-500">Resolved permissions for this employee based on role and admin access.</p>
            </div>
            <div class="grid gap-3 md:grid-cols-2">
              @forelse ($moduleAccess as $module)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                  <h3 class="text-sm font-semibold text-slate-900">{{ $module['module'] }}</h3>
                  <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    @foreach ($module['permissions'] as $permission)
                      <li class="rounded-lg bg-white px-3 py-2">{{ $permission }}</li>
                    @endforeach
                  </ul>
                </div>
              @empty
                <div class="md:col-span-2 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-500">
                  No module permissions are currently assigned.
                </div>
              @endforelse
            </div>
          </div>
        </div>
      @endif

      @if ($activeTab === 'activity')
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
          <div class="mb-4">
            <h2 class="text-base font-semibold text-slate-950">Activity</h2>
            <p class="mt-1 text-sm text-slate-500">Profile history including password changes, document uploads, and warnings.</p>
          </div>
          <div class="space-y-3">
            @forelse ($activity as $item)
              <div class="flex gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                <div class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-slate-900"></div>
                <div class="min-w-0 flex-1">
                  <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm font-semibold text-slate-900">{{ $item->title }}</div>
                    <div class="text-xs font-medium uppercase tracking-[0.16em] text-slate-400">{{ optional($item->created_at)->format('M d, Y g:i A') }}</div>
                  </div>
                  <div class="mt-1 text-sm text-slate-600">{{ $item->description ?: 'No additional details provided.' }}</div>
                  <div class="mt-2 text-xs text-slate-400">By {{ $item->actor?->name ?: 'System' }}</div>
                </div>
              </div>
            @empty
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-500">
                No profile activity has been recorded yet.
              </div>
            @endforelse
          </div>
        </div>

        @include('admin.partials.pagination', ['paginator' => $activity])
      @endif
    </div>
  </div>
</body>
</html>
