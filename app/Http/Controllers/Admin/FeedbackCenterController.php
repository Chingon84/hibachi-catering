<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceIncident;
use App\Models\Complaint;
use App\Models\DaysOffRequest;
use App\Models\GoodFeedback;
use App\Models\User;
use App\Models\VanFeedback;
use App\Http\Controllers\Admin\TeamController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class FeedbackCenterController extends Controller
{
    public function create(Request $request)
    {
        $selectedType = (string) $request->query('type', 'complaint');
        $typeMap = $this->typeMap();
        $teamMemberOptions = $this->teamMemberOptions();
        if (!array_key_exists($selectedType, $typeMap)) {
            $selectedType = 'complaint';
        }

        return view('admin.feedback_center_create', [
            'selectedType' => $selectedType,
            'typeMap' => $typeMap,
            'teamMemberOptions' => $teamMemberOptions,
            'backUrl' => (string) $request->query('back', route('admin.feedback')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $type = (string) $request->input('type', 'complaint');
        $typeMap = $this->typeMap();
        abort_unless(array_key_exists($type, $typeMap), 404);

        $validated = $request->validate($this->rulesFor($type));

        $routeParams = ['tab' => $typeMap[$type]['tab']];
        $flash = $typeMap[$type]['success'];

        switch ($type) {
            case 'complaint':
                $validated['team_members'] = collect($validated['team_members'] ?? [])
                    ->map(fn ($member) => trim((string) $member))
                    ->filter()
                    ->unique()
                    ->take(7)
                    ->values()
                    ->all();
                $validated['chef'] = (string) collect($validated['team_members'])->first();
                $record = Complaint::create($validated);
                $routeParams['item'] = $record->complaint_id;
                break;
            case 'good-feedback':
                $record = GoodFeedback::create($validated);
                $routeParams['item'] = $record->feedback_id;
                break;
            case 'van-feedback':
                $record = VanFeedback::create($validated);
                $routeParams['item'] = $record->vanfb_id;
                break;
            case 'attendance':
                $validated['authorized'] = strtolower((string) $validated['authorized']) === 'yes';
                $record = AttendanceIncident::create($validated);
                $routeParams['item'] = $record->incident_id;
                break;
            case 'days-off':
                $validated['request_id'] = $this->nextDaysOffRequestId();
                $validated['status'] = $validated['status'] ?? 'Pending';
                $validated['approved_by'] = filled($validated['approved_by'] ?? null) ? $validated['approved_by'] : 'Pending';
                $validated['unauthorized_days'] = (int) ($validated['unauthorized_days'] ?? 0);
                $validated['days'] = \Carbon\Carbon::parse($validated['start_date'])
                    ->diffInDays(\Carbon\Carbon::parse($validated['end_date'])) + 1;
                $record = DaysOffRequest::create($validated);
                $routeParams['item'] = $record->request_id;
                break;
            default:
                abort(404);
        }

        return redirect()->route('admin.feedback', $routeParams)->with('ok', $flash);
    }

    public function index(Request $request)
    {
        $canManageFeedback = (bool) ($request->user()?->hasPermission('feedback.manage') ?? false);
        $allowedViews = ['cases', 'analytics'];
        $legacyAnalyticsTabs = ['chef-summary', 'monthly-trends'];
        $requestedTab = (string) $request->query('tab', 'complaints');
        $activeView = (string) $request->query('view', in_array($requestedTab, $legacyAnalyticsTabs, true) ? 'analytics' : 'cases');
        if (!in_array($activeView, $allowedViews, true)) {
            $activeView = 'cases';
        }

        $allowedTabs = ['complaints', 'good-feedback', 'van-feedback', 'attendance', 'days-off', 'alerts'];
        $activeTab = in_array($requestedTab, $allowedTabs, true) ? $requestedTab : 'complaints';

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => trim((string) $request->query('status', '')),
            'type' => trim((string) $request->query('type', '')),
            'date' => trim((string) $request->query('date', '')),
            'from' => trim((string) $request->query('from', '')),
            'to' => trim((string) $request->query('to', '')),
            'chef' => trim((string) $request->query('chef', '')),
            'staff_type' => trim((string) $request->query('staff_type', '')),
            'source' => trim((string) $request->query('source', '')),
            'item' => trim((string) $request->query('item', '')),
            'sort' => trim((string) $request->query('sort', '')),
            'direction' => trim((string) $request->query('direction', 'desc')),
        ];

        $staffDirectory = User::query()
            ->where('is_active', true)
            ->whereIn('staff_type', TeamController::STAFF_TYPES)
            ->orderBy('name')
            ->get(['name', 'staff_type']);
        $complaintsHasTeamMembers = true;

        $staffTypeLookup = $staffDirectory
            ->pluck('staff_type', 'name')
            ->map(fn ($value) => (string) $value);

        $workflowOwners = $staffDirectory
            ->pluck('name')
            ->merge(['Operations', 'Fleet Ops', 'Pending', 'Unassigned'])
            ->filter()
            ->unique()
            ->values();

        $normalizeTeamMemberOption = function ($member) use ($staffTypeLookup) {
            if (is_array($member)) {
                $value = trim((string) ($member['value'] ?? $member['name'] ?? $member['label'] ?? ''));
                $label = trim((string) ($member['label'] ?? $member['name'] ?? $value));
                $meta = trim((string) ($member['meta'] ?? $member['staff_type'] ?? $staffTypeLookup->get($value, 'Unclassified')));
            } else {
                $value = trim((string) $member);
                $label = $value;
                $meta = trim((string) $staffTypeLookup->get($value, 'Unclassified'));
            }

            if ($value === '') {
                return null;
            }

            return [
                'value' => $value,
                'label' => $label !== '' ? $label : $value,
                'meta' => $meta !== '' ? $meta : 'Unclassified',
            ];
        };

        $teamMemberOptions = $staffDirectory
            ->map(fn ($user) => $normalizeTeamMemberOption([
                'value' => $user->name,
                'label' => $user->name,
                'meta' => $user->staff_type ?: 'Unclassified',
            ]))
            ->filter()
            ->unique('value')
            ->values();

        $normalizeTeamMemberSelection = function ($members) use ($teamMemberOptions) {
            $validNames = $teamMemberOptions->pluck('value');

            return collect($members)
                ->map(function ($member) {
                    if (is_array($member)) {
                        return trim((string) ($member['value'] ?? $member['name'] ?? $member['label'] ?? ''));
                    }

                    return trim((string) $member);
                })
                ->filter()
                ->unique()
                ->filter(fn ($member) => $validNames->contains($member))
                ->values();
        };

        $compactTeamMembers = function ($members) {
            $members = collect($members)->filter()->values();
            if ($members->isEmpty()) {
                return ['label' => 'Unassigned', 'count' => 0];
            }

            $primary = (string) $members->first();
            $extraCount = max(0, $members->count() - 1);

            return [
                'label' => $extraCount > 0 ? $primary . ' +' . $extraCount : $primary,
                'count' => $extraCount,
            ];
        };

        $normalizeDate = function (?string $value) {
            if (!filled($value)) {
                return null;
            }

            try {
                return \Carbon\Carbon::parse($value)->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        };

        $inclusiveDaysBetween = function (?string $startDate, ?string $endDate): ?int {
            if (!filled($startDate) || !filled($endDate)) {
                return null;
            }

            try {
                return \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1;
            } catch (\Throwable) {
                return null;
            }
        };

        $daysOff = DaysOffRequest::query()
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($row) => [
                'request_id' => $row->request_id,
                'chef' => $row->chef,
                'staff_type' => $staffTypeLookup->get($row->chef),
                'start_date' => optional($row->start_date)->toDateString(),
                'end_date' => optional($row->end_date)->toDateString(),
                'status' => $row->status,
                'days' => $row->days,
                'approved_by' => $row->approved_by ?: 'Pending',
                'notes' => $row->notes,
                'unauthorized_days' => $row->unauthorized_days,
            ])
            ->map(function (array $row) use ($inclusiveDaysBetween) {
                $overrides = Cache::get($this->feedbackWorkflowCacheKey('days-off', (string) $row['request_id']), []);
                if (is_array($overrides) && $overrides !== []) {
                    foreach (['start_date', 'end_date', 'status', 'days', 'approved_by', 'notes'] as $field) {
                        if (array_key_exists($field, $overrides) && $overrides[$field] !== null && $overrides[$field] !== '') {
                            $row[$field] = $overrides[$field];
                        }
                    }
                }

                $computedDays = $inclusiveDaysBetween($row['start_date'] ?? null, $row['end_date'] ?? null);
                if ($computedDays !== null) {
                    $row['days'] = $computedDays;
                }

                return $row;
            })
            ->values();

        $goodFeedback = GoodFeedback::query()
            ->orderByDesc('date_received')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($row) => [
                'feedback_id' => $row->feedback_id,
                'event_date' => optional($row->event_date)->toDateString(),
                'date_received' => optional($row->date_received)->toDateString(),
                'chef' => $row->chef,
                'staff_type' => $staffTypeLookup->get($row->chef),
                'source' => $row->source,
                'compliment' => $row->compliment,
                'assistant' => $row->assistant,
                'status' => 'Logged',
                'history' => [['title' => 'Feedback logged', 'note' => 'Created through Feedback Center workflow.']],
            ]);

        $complaintPriorityForStatus = function (?string $status): string {
            return match ((string) $status) {
                'Escalated' => 'High',
                'Resolved', 'Closed' => 'Low',
                default => 'Medium',
            };
        };

        $complaints = Complaint::query()
            ->orderByDesc('date_received')
            ->orderByDesc('id')
            ->get()
            ->map(function ($row) use ($staffTypeLookup, $complaintPriorityForStatus, $compactTeamMembers, $complaintsHasTeamMembers, $normalizeTeamMemberSelection) {
                $resolutionStatus = $row->resolution_status === 'Open' ? 'Pending' : $row->resolution_status;
                $teamMembers = $normalizeTeamMemberSelection($complaintsHasTeamMembers ? ($row->team_members ?? []) : []);
                if ($teamMembers->isEmpty() && filled($row->chef)) {
                    $teamMembers = $normalizeTeamMemberSelection([$row->chef]);
                }
                $primaryMember = (string) $teamMembers->first();
                $display = $compactTeamMembers($teamMembers);

                return [
                    'complaint_id' => $row->complaint_id,
                    'event_date' => optional($row->event_date)->toDateString(),
                    'date_received' => optional($row->date_received)->toDateString(),
                    'chef' => $primaryMember,
                    'team_members' => $teamMembers->all(),
                    'team_member_display' => $display['label'],
                    'team_member_more_count' => $display['count'],
                    'team_member_search' => $teamMembers->implode(' '),
                    'staff_type' => $staffTypeLookup->get($primaryMember),
                    'staff_types' => $teamMembers->map(fn ($member) => $staffTypeLookup->get($member))->filter()->values()->all(),
                    'category' => $row->category,
                    'description' => $row->description,
                    'resolution_status' => $resolutionStatus,
                    'assistant' => $row->assistant,
                    'action_taken' => $row->action_taken,
                    'priority' => $complaintPriorityForStatus($resolutionStatus),
                    'history' => [['title' => 'Complaint logged', 'note' => 'Created through Feedback Center workflow.']],
                ];
            })
            ->map(function (array $row) use ($complaintPriorityForStatus, $compactTeamMembers, $staffTypeLookup, $normalizeTeamMemberSelection) {
                $overrides = Cache::get($this->feedbackWorkflowCacheKey('complaints', (string) $row['complaint_id']), []);
                if (!is_array($overrides) || $overrides === []) {
                    return $row;
                }

                if (!empty($overrides['team_members']) && is_array($overrides['team_members'])) {
                    $teamMembers = $normalizeTeamMemberSelection($overrides['team_members']);
                    if ($teamMembers->isNotEmpty()) {
                        $row['team_members'] = $teamMembers->all();
                        $row['chef'] = (string) $teamMembers->first();
                        $row['staff_type'] = $staffTypeLookup->get($row['chef']);
                        $row['staff_types'] = $teamMembers->map(fn ($member) => $staffTypeLookup->get($member))->filter()->values()->all();
                        $display = $compactTeamMembers($teamMembers);
                        $row['team_member_display'] = $display['label'];
                        $row['team_member_more_count'] = $display['count'];
                        $row['team_member_search'] = $teamMembers->implode(' ');
                    }
                }
                if (filled($overrides['status'] ?? null)) {
                    $row['resolution_status'] = (string) $overrides['status'];
                }
                if (filled($overrides['owner'] ?? null)) {
                    $row['assistant'] = (string) $overrides['owner'];
                }
                if (filled($overrides['source'] ?? null)) {
                    $row['category'] = (string) $overrides['source'];
                }
                if (filled($overrides['summary'] ?? null)) {
                    $row['description'] = (string) $overrides['summary'];
                }
                if (array_key_exists('internal_note', $overrides) && filled($overrides['internal_note'])) {
                    $row['action_taken'] = (string) $overrides['internal_note'];
                }
                if (!empty($overrides['history']) && is_array($overrides['history'])) {
                    $row['history'] = array_merge($row['history'] ?? [], $overrides['history']);
                }

                $row['priority'] = $complaintPriorityForStatus($row['resolution_status'] ?? null);

                return $row;
            });

        $vanFeedback = VanFeedback::query()
            ->orderByDesc('date_received')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($row) => [
                'vanfb_id' => $row->vanfb_id,
                'event_date' => optional($row->event_date)->toDateString(),
                'date_received' => optional($row->date_received)->toDateString(),
                'chef' => $row->chef,
                'staff_type' => $staffTypeLookup->get($row->chef),
                'van' => $row->van,
                'description' => $row->description,
                'action_taken' => $row->action_taken,
                'status' => filled($row->action_taken) ? 'In Review' : 'Open',
                'history' => [['title' => 'Van feedback logged', 'note' => 'Created through Feedback Center workflow.']],
            ]);

        $attendance = AttendanceIncident::query()
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($row) => [
                'incident_id' => $row->incident_id,
                'date' => optional($row->date)->toDateString(),
                'chef' => $row->chef,
                'staff_type' => $staffTypeLookup->get($row->chef),
                'incident_type' => $row->incident_type,
                'units' => (int) $row->units,
                'authorized' => (bool) $row->authorized,
                'manager' => $row->manager,
                'notes' => $row->notes,
                'status' => $row->authorized ? 'Authorized' : 'Unauthorized',
                'history' => [['title' => 'Attendance incident logged', 'note' => 'Created through Feedback Center workflow.']],
            ]);

        $alerts = $complaints
            ->filter(fn ($row) => ($row['resolution_status'] ?? '') === 'Escalated')
            ->map(fn ($row) => [
                'alert_id' => 'CP-' . $row['complaint_id'],
                'type' => 'Escalated complaint',
                'chef' => $row['chef'],
                'staff_type' => $row['staff_type'],
                'date' => $row['date_received'] ?: $row['event_date'],
                'details' => $row['description'],
                'severity' => 'Urgent',
                'status' => 'Escalated',
            ])
            ->merge(
                $daysOff
                    ->filter(fn ($row) => (int) ($row['unauthorized_days'] ?? 0) > 0)
                    ->map(fn ($row) => [
                        'alert_id' => 'DO-' . $row['request_id'],
                        'type' => 'Unauthorized time off',
                        'chef' => $row['chef'],
                        'staff_type' => $row['staff_type'],
                        'date' => $row['end_date'] ?: $row['start_date'],
                        'details' => trim(((string) ($row['notes'] ?? '')) . ' Unauthorized days: ' . (int) ($row['unauthorized_days'] ?? 0)),
                        'severity' => 'High',
                        'status' => 'Open',
                    ])
            )
            ->merge(
                $attendance
                    ->filter(fn ($row) => !($row['authorized'] ?? false))
                    ->map(fn ($row) => [
                        'alert_id' => 'AT-' . $row['incident_id'],
                        'type' => 'Attendance incident',
                        'chef' => $row['chef'],
                        'staff_type' => $row['staff_type'],
                        'date' => $row['date'],
                        'details' => $row['notes'] ?: $row['incident_type'],
                        'severity' => (int) ($row['units'] ?? 0) > 1 ? 'High' : 'Medium',
                        'status' => 'Open',
                    ])
            )
            ->sortByDesc('date')
            ->values();

        $filterItems = function ($items, array $config, bool $applyDateRange = true) use ($filters, $normalizeDate) {
            return collect($items)->filter(function ($item) use ($filters, $config, $applyDateRange, $normalizeDate) {
                if ($filters['type'] !== '' && isset($config['record_type']) && $filters['type'] !== $config['record_type']) {
                    return false;
                }

                if ($filters['status'] !== '') {
                    $statusKey = $config['status_key'] ?? null;
                    $statusValue = $statusKey ? (string) ($item[$statusKey] ?? '') : '';
                    if ($statusValue !== $filters['status']) {
                        return false;
                    }
                }

                if ($filters['chef'] !== '') {
                    $memberKey = $config['member_key'] ?? 'chef';
                    $memberValue = $item[$memberKey] ?? '';
                    if (is_array($memberValue)) {
                        if (!in_array($filters['chef'], $memberValue, true)) {
                            return false;
                        }
                    } elseif ((string) $memberValue !== $filters['chef']) {
                        return false;
                    }
                }

                if ($filters['staff_type'] !== '') {
                    $staffTypeKey = $config['staff_type_key'] ?? 'staff_type';
                    $staffTypeValue = $item[$staffTypeKey] ?? '';
                    if (is_array($staffTypeValue)) {
                        if (!in_array($filters['staff_type'], $staffTypeValue, true)) {
                            return false;
                        }
                    } elseif ((string) $staffTypeValue !== $filters['staff_type']) {
                        return false;
                    }
                }

                if ($filters['source'] !== '') {
                    $sourceKey = $config['source_key'] ?? null;
                    $sourceValue = $sourceKey ? (string) ($item[$sourceKey] ?? '') : '';
                    if ($sourceValue !== $filters['source']) {
                        return false;
                    }
                }

                if ($filters['date'] !== '') {
                    $dateKeys = $config['date_keys'] ?? [];
                    $hasDateMatch = false;
                    foreach ($dateKeys as $dateKey) {
                        if ((string) ($item[$dateKey] ?? '') === $filters['date']) {
                            $hasDateMatch = true;
                            break;
                        }
                    }
                    if (!$hasDateMatch) {
                        return false;
                    }
                }

                if ($applyDateRange && ($filters['from'] !== '' || $filters['to'] !== '')) {
                    $fromDate = $normalizeDate($filters['from']);
                    $toDate = $normalizeDate($filters['to'])?->endOfDay();
                    $matchedRange = false;

                    foreach (($config['date_keys'] ?? []) as $dateKey) {
                        $candidate = $normalizeDate((string) ($item[$dateKey] ?? ''));
                        if (!$candidate) {
                            continue;
                        }
                        if ($fromDate && $candidate->lt($fromDate)) {
                            continue;
                        }
                        if ($toDate && $candidate->gt($toDate)) {
                            continue;
                        }
                        $matchedRange = true;
                        break;
                    }

                    if (!$matchedRange) {
                        return false;
                    }
                }

                if ($filters['q'] !== '') {
                    $searchKeys = $config['search_keys'] ?? array_keys($item);
                    $haystack = collect($searchKeys)
                        ->map(function ($key) use ($item) {
                            $value = $item[$key] ?? '';
                            if (is_bool($value)) {
                                return $value ? 'yes' : 'no';
                            }
                            if (is_array($value)) {
                                return implode(' ', array_map(fn ($part) => is_scalar($part) ? (string) $part : '', $value));
                            }

                            return is_scalar($value) ? (string) $value : '';
                        })
                        ->implode(' ');

                    if (stripos($haystack, $filters['q']) === false) {
                        return false;
                    }
                }

                return true;
            })->values();
        };

        $daysOffFiltered = $filterItems($daysOff, [
            'record_type' => 'Days Off',
            'status_key' => 'status',
            'member_key' => 'chef',
            'staff_type_key' => 'staff_type',
            'source_key' => 'approved_by',
            'date_keys' => ['start_date', 'end_date'],
            'search_keys' => ['request_id', 'chef', 'approved_by', 'notes'],
        ]);

        $daysOffSort = $filters['sort'];
        $daysOffDirection = strtolower($filters['direction']) === 'asc' ? 'asc' : 'desc';
        $sortableDaysOffFields = ['chef', 'start_date', 'status', 'end_date'];

        if (in_array($daysOffSort, $sortableDaysOffFields, true)) {
            $statusOrder = ['Pending' => 0, 'Approved' => 1, 'Denied' => 2, 'Cancelled' => 3];

            $daysOffFiltered = $daysOffFiltered
                ->sortBy(function ($row) use ($daysOffSort, $statusOrder) {
                    return match ($daysOffSort) {
                        'chef' => strtolower((string) ($row['chef'] ?? '')),
                        'start_date' => (string) ($row['start_date'] ?? ''),
                        'end_date' => (string) ($row['end_date'] ?? ''),
                        'status' => $statusOrder[$row['status'] ?? ''] ?? 999,
                        default => (string) ($row[$daysOffSort] ?? ''),
                    };
                }, SORT_NATURAL, $daysOffDirection === 'desc')
                ->values();
        } else {
            $filters['sort'] = '';
            $filters['direction'] = 'desc';
        }

        $goodFeedbackFiltered = $filterItems($goodFeedback, [
            'record_type' => 'Good Feedback',
            'status_key' => 'status',
            'member_key' => 'chef',
            'staff_type_key' => 'staff_type',
            'source_key' => 'source',
            'date_keys' => ['event_date', 'date_received'],
            'search_keys' => ['feedback_id', 'chef', 'source', 'compliment', 'assistant'],
        ]);

        $complaintsFiltered = $filterItems($complaints, [
            'record_type' => 'Complaint',
            'status_key' => 'resolution_status',
            'member_key' => 'team_members',
            'staff_type_key' => 'staff_types',
            'source_key' => 'category',
            'date_keys' => ['event_date', 'date_received'],
            'search_keys' => ['complaint_id', 'team_member_search', 'category', 'description', 'assistant', 'action_taken'],
        ]);

        $vanFeedbackFiltered = $filterItems($vanFeedback, [
            'record_type' => 'Van Feedback',
            'status_key' => 'status',
            'member_key' => 'chef',
            'staff_type_key' => 'staff_type',
            'source_key' => 'van',
            'date_keys' => ['event_date', 'date_received'],
            'search_keys' => ['vanfb_id', 'chef', 'van', 'description', 'action_taken'],
        ]);

        $attendanceFiltered = $filterItems($attendance, [
            'record_type' => 'Attendance',
            'status_key' => 'status',
            'member_key' => 'chef',
            'staff_type_key' => 'staff_type',
            'source_key' => 'incident_type',
            'date_keys' => ['date'],
            'search_keys' => ['incident_id', 'chef', 'incident_type', 'manager', 'notes'],
        ]);

        $alertsFiltered = $filterItems($alerts, [
            'record_type' => 'Alert',
            'status_key' => 'status',
            'member_key' => 'chef',
            'staff_type_key' => 'staff_type',
            'source_key' => 'type',
            'date_keys' => ['date'],
            'search_keys' => ['alert_id', 'type', 'chef', 'details', 'severity'],
        ]);

        $totalFilteredCases = $complaintsFiltered->count()
            + $goodFeedbackFiltered->count()
            + $vanFeedbackFiltered->count()
            + $attendanceFiltered->count()
            + $daysOffFiltered->count()
            + $alertsFiltered->count();

        $openWorkflowCount = $complaintsFiltered->whereIn('resolution_status', ['Pending', 'In Review', 'Escalated'])->count()
            + $goodFeedbackFiltered->whereIn('status', ['Pending', 'In Review'])->count()
            + $vanFeedbackFiltered->whereIn('status', ['Open', 'In Review', 'Escalated'])->count()
            + $attendanceFiltered->where('status', 'Unauthorized')->count()
            + $daysOffFiltered->where('status', 'Pending')->count()
            + $alertsFiltered->whereIn('status', ['Open', 'In Review', 'Escalated'])->count();

        $normalizeCase = function (array $row, string $type) {
            return match ($type) {
                'Complaint' => [
                    'id' => $row['complaint_id'],
                    'type' => 'Complaint',
                    'date' => $row['date_received'],
                    'event_date' => $row['event_date'],
                    'team_member' => $row['chef'],
                    'staff_type' => $row['staff_type'],
                    'source' => $row['category'],
                    'summary' => $row['description'],
                    'status' => $row['resolution_status'],
                    'owner' => $row['assistant'],
                    'priority' => $row['priority'],
                    'action_taken' => $row['action_taken'],
                    'history' => $row['history'],
                    'group' => 'complaints',
                ],
                'Good Feedback' => [
                    'id' => $row['feedback_id'],
                    'type' => 'Good Feedback',
                    'date' => $row['date_received'],
                    'event_date' => $row['event_date'],
                    'team_member' => $row['chef'],
                    'staff_type' => $row['staff_type'],
                    'source' => $row['source'],
                    'summary' => $row['compliment'],
                    'status' => $row['status'],
                    'owner' => $row['assistant'],
                    'priority' => 'Positive',
                    'action_taken' => 'Share with team leadership and add to recognition notes.',
                    'history' => $row['history'],
                    'group' => 'good-feedback',
                ],
                'Van Feedback' => [
                    'id' => $row['vanfb_id'],
                    'type' => 'Van Feedback',
                    'date' => $row['date_received'],
                    'event_date' => $row['event_date'],
                    'team_member' => $row['chef'],
                    'staff_type' => $row['staff_type'],
                    'source' => $row['van'],
                    'summary' => $row['description'],
                    'status' => $row['status'],
                    'owner' => 'Fleet Ops',
                    'priority' => 'Operational',
                    'action_taken' => $row['action_taken'],
                    'history' => $row['history'],
                    'group' => 'van-feedback',
                ],
                'Attendance' => [
                    'id' => $row['incident_id'],
                    'type' => 'Attendance',
                    'date' => $row['date'],
                    'event_date' => $row['date'],
                    'team_member' => $row['chef'],
                    'staff_type' => $row['staff_type'],
                    'source' => $row['incident_type'],
                    'summary' => $row['notes'],
                    'status' => $row['status'],
                    'owner' => $row['manager'],
                    'priority' => $row['authorized'] ? 'Reviewed' : 'Flagged',
                    'action_taken' => $row['authorized'] ? 'Manager authorization documented.' : 'Requires follow-up and incident review.',
                    'history' => $row['history'],
                    'group' => 'attendance',
                ],
                'Days Off' => [
                    'id' => $row['request_id'],
                    'type' => 'Days Off',
                    'date' => $row['start_date'],
                    'event_date' => $row['end_date'],
                    'team_member' => $row['chef'],
                    'staff_type' => $row['staff_type'],
                    'source' => $row['approved_by'],
                    'summary' => $row['notes'],
                    'status' => $row['status'],
                    'owner' => $row['approved_by'],
                    'priority' => $row['unauthorized_days'] > 0 ? 'Flagged' : 'Scheduled',
                    'action_taken' => $row['unauthorized_days'] > 0 ? 'Unauthorized time requires operations review.' : 'Time-off request logged for schedule planning.',
                    'history' => [['title' => 'Time-off request logged', 'note' => 'Tracked inside the Feedback Center operations workflow.']],
                    'group' => 'days-off',
                    'start_date' => $row['start_date'],
                    'end_date' => $row['end_date'],
                    'days' => $row['days'],
                    'approved_by' => $row['approved_by'],
                    'notes' => $row['notes'],
                    'unauthorized_days' => $row['unauthorized_days'],
                ],
                'Alert' => [
                    'id' => $row['alert_id'],
                    'type' => 'Alert',
                    'date' => $row['date'],
                    'event_date' => $row['date'],
                    'team_member' => $row['chef'],
                    'staff_type' => $row['staff_type'],
                    'source' => $row['type'],
                    'summary' => $row['details'],
                    'status' => $row['status'],
                    'owner' => 'Operations',
                    'priority' => $row['severity'],
                    'action_taken' => 'Escalate through operations leadership when unresolved.',
                    'history' => [['title' => 'Alert generated', 'note' => 'Derived from operational records.']],
                    'group' => 'alerts',
                ],
            };
        };

        $humanDate = function (?string $value) {
            if (!filled($value)) {
                return 'Not set';
            }

            try {
                return \Carbon\Carbon::parse($value)->format('M d, Y');
            } catch (\Throwable) {
                return (string) $value;
            }
        };

        $applyWorkflowOverrides = function (array $case) {
            $overrides = Cache::get($this->feedbackWorkflowCacheKey((string) $case['group'], (string) $case['id']), []);

            if (!is_array($overrides) || $overrides === []) {
                return $case;
            }

            if (filled($overrides['status'] ?? null)) {
                $case['status'] = (string) $overrides['status'];
            }

            if (filled($overrides['owner'] ?? null)) {
                $case['owner'] = (string) $overrides['owner'];
            }

            if (array_key_exists('internal_note', $overrides)) {
                $case['workflow_note'] = (string) ($overrides['internal_note'] ?? '');
            }

            if (!empty($overrides['history']) && is_array($overrides['history'])) {
                $case['history'] = array_merge($case['history'] ?? [], $overrides['history']);
            }

            return $case;
        };

        $allCases = $complaintsFiltered->map(fn ($row) => $normalizeCase($row, 'Complaint'))
            ->merge($goodFeedbackFiltered->map(fn ($row) => $normalizeCase($row, 'Good Feedback')))
            ->merge($vanFeedbackFiltered->map(fn ($row) => $normalizeCase($row, 'Van Feedback')))
            ->merge($attendanceFiltered->map(fn ($row) => $normalizeCase($row, 'Attendance')))
            ->merge($daysOffFiltered->map(fn ($row) => $normalizeCase($row, 'Days Off')))
            ->merge($alertsFiltered->map(fn ($row) => $normalizeCase($row, 'Alert')))
            ->map($applyWorkflowOverrides)
            ->sortByDesc('date')
            ->values();

        $allCasesNoDate = $filterItems($complaints, [
            'record_type' => 'Complaint',
            'status_key' => 'resolution_status',
            'member_key' => 'team_members',
            'staff_type_key' => 'staff_types',
            'source_key' => 'category',
            'date_keys' => ['event_date', 'date_received'],
            'search_keys' => ['complaint_id', 'team_member_search', 'category', 'description', 'assistant', 'action_taken'],
        ], false)->map(fn ($row) => $normalizeCase($row, 'Complaint'))
            ->merge($filterItems($goodFeedback, [
                'record_type' => 'Good Feedback',
                'status_key' => 'status',
                'member_key' => 'chef',
                'staff_type_key' => 'staff_type',
                'source_key' => 'source',
                'date_keys' => ['event_date', 'date_received'],
                'search_keys' => ['feedback_id', 'chef', 'source', 'compliment', 'assistant'],
            ], false)->map(fn ($row) => $normalizeCase($row, 'Good Feedback')))
            ->merge($filterItems($vanFeedback, [
                'record_type' => 'Van Feedback',
                'status_key' => 'status',
                'member_key' => 'chef',
                'staff_type_key' => 'staff_type',
                'source_key' => 'van',
                'date_keys' => ['event_date', 'date_received'],
                'search_keys' => ['vanfb_id', 'chef', 'van', 'description', 'action_taken'],
            ], false)->map(fn ($row) => $normalizeCase($row, 'Van Feedback')))
            ->merge($filterItems($attendance, [
                'record_type' => 'Attendance',
                'status_key' => 'status',
                'member_key' => 'chef',
                'staff_type_key' => 'staff_type',
                'source_key' => 'incident_type',
                'date_keys' => ['date'],
                'search_keys' => ['incident_id', 'chef', 'incident_type', 'manager', 'notes'],
            ], false)->map(fn ($row) => $normalizeCase($row, 'Attendance')))
            ->merge($filterItems($daysOff, [
                'record_type' => 'Days Off',
                'status_key' => 'status',
                'member_key' => 'chef',
                'staff_type_key' => 'staff_type',
                'source_key' => 'approved_by',
                'date_keys' => ['start_date', 'end_date'],
                'search_keys' => ['request_id', 'chef', 'approved_by', 'notes'],
            ], false)->map(fn ($row) => $normalizeCase($row, 'Days Off')))
            ->merge($filterItems($alerts, [
                'record_type' => 'Alert',
                'status_key' => 'status',
                'member_key' => 'chef',
                'staff_type_key' => 'staff_type',
                'source_key' => 'type',
                'date_keys' => ['date'],
                'search_keys' => ['alert_id', 'type', 'chef', 'details', 'severity'],
            ], false)->map(fn ($row) => $normalizeCase($row, 'Alert')))
            ->map($applyWorkflowOverrides)
            ->sortByDesc('date')
            ->values();

        $chefOptions = $teamMemberOptions->pluck('value')->unique()->values();
        $staffTypeOptions = $staffDirectory->pluck('staff_type')->filter()->unique()->values();
        $statusOptions = $allCasesNoDate->pluck('status')->filter()->unique()->sort()->values();
        $sourceOptions = $allCasesNoDate->pluck('source')->filter()->unique()->sort()->values();
        $typeOptions = collect(['Complaint', 'Good Feedback', 'Van Feedback', 'Attendance', 'Days Off', 'Alert']);

        $teamSummaries = $allCases
            ->groupBy('team_member')
            ->map(function ($rows, $member) {
                $daysOffRows = $rows->where('type', 'Days Off');
                $attendanceRows = $rows->where('type', 'Attendance');
                $goodCount = $rows->where('type', 'Good Feedback')->count();
                $complaintCount = $rows->where('type', 'Complaint')->count();
                $vanCount = $rows->where('type', 'Van Feedback')->count();

                return [
                    'team_member' => $member,
                    'staff_type' => (string) $rows->pluck('staff_type')->filter()->first(),
                    'total_cases' => $rows->count(),
                    'requests' => $daysOffRows->count(),
                    'total_days' => $daysOffRows->count(),
                    'good_feedback' => $goodCount,
                    'complaints' => $complaintCount,
                    'van_issues' => $vanCount,
                    'attendance_incidents' => $attendanceRows->count(),
                    'net_score' => $goodCount - $complaintCount,
                    'open_cases' => $rows->whereIn('status', ['Open', 'In Review', 'Escalated', 'Pending', 'Unauthorized'])->count(),
                ];
            })
            ->sortByDesc('open_cases')
            ->values();

        $periodFrom = null;
        $periodTo = null;
        if ($filters['from'] !== '' || $filters['to'] !== '' || $filters['date'] !== '') {
            $periodFrom = $normalizeDate($filters['from'] ?: $filters['date']);
            $periodTo = $normalizeDate($filters['to'] ?: $filters['date']);
            if ($periodFrom && !$periodTo) {
                $periodTo = $periodFrom->copy();
            }
            if ($periodTo && !$periodFrom) {
                $periodFrom = $periodTo->copy();
            }
        }

        $previousCases = collect();
        if ($periodFrom && $periodTo) {
            $days = $periodFrom->diffInDays($periodTo) + 1;
            $previousFrom = $periodFrom->copy()->subDays($days);
            $previousTo = $periodFrom->copy()->subDay();

            $previousCases = $allCasesNoDate->filter(function ($row) use ($normalizeDate, $previousFrom, $previousTo) {
                $caseDate = $normalizeDate($row['date'] ?? null);
                if (!$caseDate) {
                    return false;
                }

                return !$caseDate->lt($previousFrom) && !$caseDate->gt($previousTo);
            })->values();
        }

        $formatDelta = function (int|float $current, int|float $previous, string $suffix = '') {
            $delta = $current - $previous;
            if ($delta === 0) {
                return ['text' => 'Flat vs previous period', 'direction' => 'flat'];
            }

            return [
                'text' => sprintf('%s%s%s vs previous period', $delta > 0 ? '+' : '', number_format($delta), $suffix),
                'direction' => $delta > 0 ? 'up' : 'down',
            ];
        };

        $buildStat = function (string $label, int|float|string $value, string $tone, string $note, int|float|null $previousValue = null) use ($formatDelta) {
            $delta = $previousValue === null ? ['text' => 'No comparison window', 'direction' => 'flat'] : $formatDelta((float) $value, $previousValue);

            return [
                'label' => $label,
                'value' => $value,
                'tone' => $tone,
                'note' => $note,
                'trend' => $delta['text'],
                'trend_direction' => $delta['direction'],
            ];
        };

        $normalizeAnalyticsStatus = function (?string $status): string {
            return match (trim((string) $status)) {
                'Pending', 'Open', 'Unauthorized' => 'Open',
                'In Review' => 'In Review',
                'Escalated' => 'Escalated',
                'Resolved' => 'Resolved',
                'Closed', 'Approved', 'Denied', 'Cancelled', 'Logged', 'Shared', 'Reviewed', 'Authorized' => 'Closed',
                default => 'Closed',
            };
        };

        $currentOpenComplaints = $complaintsFiltered->whereIn('resolution_status', ['Pending', 'In Review', 'Escalated'])->count();
        $previousOpenComplaints = $previousCases->where('type', 'Complaint')->whereIn('status', ['Pending', 'In Review', 'Escalated'])->count();
        $currentNetScore = $goodFeedbackFiltered->count() - $complaintsFiltered->count();
        $previousNetScore = $previousCases->where('type', 'Good Feedback')->count() - $previousCases->where('type', 'Complaint')->count();
        $currentTotalCases = $allCases->count();
        $previousTotalCases = $previousCases->count();
        $topGoodFeedbackKpi = $goodFeedbackFiltered
            ->pluck('chef')
            ->map(fn ($member) => trim((string) $member))
            ->filter()
            ->countBy()
            ->sortDesc();
        $topComplaintsKpi = $complaintsFiltered
            ->flatMap(function ($row) {
                $members = collect($row['team_members'] ?? [])
                    ->map(fn ($member) => trim((string) (is_array($member) ? ($member['label'] ?? $member['name'] ?? $member['value'] ?? '') : $member)))
                    ->filter()
                    ->values();

                return $members->isNotEmpty()
                    ? $members
                    : collect([trim((string) ($row['chef'] ?? ''))])->filter();
            })
            ->countBy()
            ->sortDesc();
        $topGoodFeedbackName = (string) ($topGoodFeedbackKpi->keys()->first() ?? '—');
        $topGoodFeedbackCount = (int) ($topGoodFeedbackKpi->first() ?? 0);
        $topComplaintsName = (string) ($topComplaintsKpi->keys()->first() ?? '—');
        $topComplaintsCount = (int) ($topComplaintsKpi->first() ?? 0);

        $stats = [
            $buildStat('Total Cases', $currentTotalCases, 'neutral', 'All filtered operational records', $periodFrom ? $previousTotalCases : null),
            $buildStat('Open Complaints', $currentOpenComplaints, 'open', 'Pending, In Review, or Escalated', $periodFrom ? $previousOpenComplaints : null),
            $buildStat('Net Score', $currentNetScore, 'positive', 'Good feedback minus complaints', $periodFrom ? $previousNetScore : null),
            [
                'label' => 'Top Good Feedback',
                'value' => $topGoodFeedbackName,
                'tone' => 'positive',
                'note' => $topGoodFeedbackCount > 0 ? number_format($topGoodFeedbackCount) . ' feedback' : 'No good feedback in current filters',
                'trend' => 'Most recognized employee',
                'trend_direction' => 'flat',
            ],
            [
                'label' => 'Top Complaints',
                'value' => $topComplaintsName,
                'tone' => 'escalated',
                'note' => $topComplaintsCount > 0 ? number_format($topComplaintsCount) . ' complaints' : 'No complaints in current filters',
                'trend' => 'Highest complaint volume',
                'trend_direction' => 'flat',
            ],
        ];

        $monthBuckets = $allCases
            ->pluck('date')
            ->filter()
            ->map(fn ($date) => substr((string) $date, 0, 7))
            ->unique()
            ->sort()
            ->values();

        $complaintsByMember = $complaintsFiltered
            ->flatMap(function ($row) {
                $members = collect($row['team_members'] ?? [])
                    ->map(fn ($member) => trim((string) (is_array($member) ? ($member['label'] ?? $member['name'] ?? $member['value'] ?? '') : $member)))
                    ->filter()
                    ->values();

                return $members->isNotEmpty()
                    ? $members
                    : collect([trim((string) ($row['chef'] ?? ''))])->filter();
            })
            ->countBy()
            ->sortDesc()
            ->take(10);
        $goodFeedbackByMember = $goodFeedbackFiltered
            ->pluck('chef')
            ->map(fn ($member) => trim((string) $member))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(10);
        $vanIssuesByMember = $vanFeedbackFiltered
            ->pluck('chef')
            ->map(fn ($member) => trim((string) $member))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(10);
        $attendanceByMember = $attendanceFiltered
            ->pluck('chef')
            ->map(fn ($member) => trim((string) $member))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(10);
        $daysOffByMember = $daysOffFiltered
            ->groupBy(fn ($row) => trim((string) ($row['chef'] ?? '')))
            ->map(fn ($rows) => $rows->sum(fn ($row) => (int) ($row['days'] ?? 0)))
            ->filter(fn ($total, $member) => $member !== '' && $total > 0)
            ->sortDesc()
            ->take(10);
        $casesByTypeCounts = collect([
            'Complaint' => $complaintsFiltered->count(),
            'Good Feedback' => $goodFeedbackFiltered->count(),
            'Van Issue' => $vanFeedbackFiltered->count(),
            'Attendance' => $attendanceFiltered->count(),
            'Days Off' => $daysOffFiltered->count(),
            'Alert' => $alertsFiltered->count(),
        ])->filter(fn ($count) => $count > 0);
        $statusOrder = ['Open', 'In Review', 'Escalated', 'Resolved', 'Closed'];
        $statusCounts = $allCases
            ->map(fn ($row) => $normalizeAnalyticsStatus($row['status'] ?? null))
            ->countBy();
        $orderedStatusCounts = collect($statusOrder)
            ->mapWithKeys(fn ($status) => [$status => $statusCounts->get($status, 0)])
            ->filter(fn ($count) => $count > 0);
        $topGoodFeedbackLeaderboard = $goodFeedbackFiltered
            ->pluck('chef')
            ->map(fn ($member) => trim((string) $member))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(2)
            ->map(fn ($count, $member) => ['name' => $member, 'count' => $count])
            ->values();
        $topComplaintsLeaderboard = $complaintsFiltered
            ->flatMap(function ($row) {
                $members = collect($row['team_members'] ?? [])
                    ->map(fn ($member) => trim((string) (is_array($member) ? ($member['label'] ?? $member['name'] ?? $member['value'] ?? '') : $member)))
                    ->filter()
                    ->values();

                return $members->isNotEmpty()
                    ? $members
                    : collect([trim((string) ($row['chef'] ?? ''))])->filter();
            })
            ->countBy()
            ->sortDesc()
            ->take(2)
            ->map(fn ($count, $member) => ['name' => $member, 'count' => $count])
            ->values();
        $employeePerformanceCounts = collect();
        $goodFeedbackFiltered
            ->pluck('chef')
            ->map(fn ($member) => trim((string) $member))
            ->filter()
            ->each(function ($member) use (&$employeePerformanceCounts) {
                $employeePerformanceCounts[$member] = ($employeePerformanceCounts[$member] ?? 0) + 2;
            });
        $complaintsFiltered
            ->flatMap(function ($row) {
                $members = collect($row['team_members'] ?? [])
                    ->map(fn ($member) => trim((string) (is_array($member) ? ($member['label'] ?? $member['name'] ?? $member['value'] ?? '') : $member)))
                    ->filter()
                    ->values();

                return $members->isNotEmpty()
                    ? $members
                    : collect([trim((string) ($row['chef'] ?? ''))])->filter();
            })
            ->each(function ($member) use (&$employeePerformanceCounts) {
                $employeePerformanceCounts[$member] = ($employeePerformanceCounts[$member] ?? 0) - 2;
            });
        $attendanceFiltered
            ->pluck('chef')
            ->map(fn ($member) => trim((string) $member))
            ->filter()
            ->each(function ($member) use (&$employeePerformanceCounts) {
                $employeePerformanceCounts[$member] = ($employeePerformanceCounts[$member] ?? 0) - 1;
            });
        $daysOffFiltered
            ->filter(fn ($row) => (int) ($row['unauthorized_days'] ?? 0) > 0)
            ->each(function ($row) use (&$employeePerformanceCounts) {
                $member = trim((string) ($row['chef'] ?? ''));
                if ($member === '') {
                    return;
                }

                $employeePerformanceCounts[$member] = ($employeePerformanceCounts[$member] ?? 0) - (int) ($row['unauthorized_days'] ?? 0);
            });
        $employeePerformanceScores = collect($employeePerformanceCounts)
            ->sortDesc()
            ->take(10);
        $complaintCategoryCounts = $complaintsFiltered
            ->pluck('category')
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(8);
        $analyticsPalette = [
            'complaints' => '#2563EB',
            'good_feedback' => '#22C55E',
            'days_off' => '#F59E0B',
            'alerts_van' => '#8B5CF6',
            'attendance' => '#06B6D4',
            'neutral' => '#64748B',
            'negative' => '#DC2626',
        ];
        $hexToRgba = function (string $hex, float $alpha): string {
            $hex = ltrim($hex, '#');
            $rgb = sscanf($hex, '%02x%02x%02x');
            if ($rgb === null || count($rgb) !== 3) {
                return "rgba(100,116,139,{$alpha})";
            }

            return sprintf('rgba(%d,%d,%d,%.2f)', $rgb[0], $rgb[1], $rgb[2], $alpha);
        };

        $analyticsCharts = [
            'teamPerformanceByMember' => [
                'complaints' => [
                    'labels' => $complaintsByMember->keys()->values(),
                    'datasets' => [[
                        'label' => 'Complaints',
                        'data' => $complaintsByMember->values(),
                        'backgroundColor' => $analyticsPalette['complaints'],
                        'borderRadius' => 10,
                    ]],
                ],
                'good-feedback' => [
                    'labels' => $goodFeedbackByMember->keys()->values(),
                    'datasets' => [[
                        'label' => 'Good Feedback',
                        'data' => $goodFeedbackByMember->values(),
                        'backgroundColor' => $analyticsPalette['good_feedback'],
                        'borderRadius' => 10,
                    ]],
                ],
                'van-feedback' => [
                    'labels' => $vanIssuesByMember->keys()->values(),
                    'datasets' => [[
                        'label' => 'Van Issues',
                        'data' => $vanIssuesByMember->values(),
                        'backgroundColor' => $analyticsPalette['alerts_van'],
                        'borderRadius' => 10,
                    ]],
                ],
                'attendance' => [
                    'labels' => $attendanceByMember->keys()->values(),
                    'datasets' => [[
                        'label' => 'Attendance',
                        'data' => $attendanceByMember->values(),
                        'backgroundColor' => $analyticsPalette['attendance'],
                        'borderRadius' => 10,
                    ]],
                ],
                'days-off' => [
                    'labels' => $daysOffByMember->keys()->values(),
                    'datasets' => [[
                        'label' => 'Days Off',
                        'data' => $daysOffByMember->values(),
                        'backgroundColor' => $analyticsPalette['days_off'],
                        'borderRadius' => 10,
                    ]],
                ],
            ],
            'monthlyTrend' => [
                'labels' => $monthBuckets->map(fn ($month) => \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y'))->values(),
                'datasets' => [
                    [
                        'label' => 'Complaints',
                        'data' => $monthBuckets->map(fn ($month) => $complaintsFiltered->filter(fn ($row) => str_starts_with((string) $row['date_received'], $month) || str_starts_with((string) $row['event_date'], $month))->count())->values(),
                        'borderColor' => $analyticsPalette['complaints'],
                        'backgroundColor' => $hexToRgba($analyticsPalette['complaints'], 0.12),
                        'tension' => 0.35,
                    ],
                    [
                        'label' => 'Alerts',
                        'data' => $monthBuckets->map(fn ($month) => $alertsFiltered->filter(fn ($row) => str_starts_with((string) $row['date'], $month))->count())->values(),
                        'borderColor' => $analyticsPalette['alerts_van'],
                        'backgroundColor' => $hexToRgba($analyticsPalette['alerts_van'], 0.12),
                        'tension' => 0.35,
                    ],
                    [
                        'label' => 'Days Off',
                        'data' => $monthBuckets->map(fn ($month) => $daysOffFiltered->filter(fn ($row) => str_starts_with((string) $row['start_date'], $month))->count())->values(),
                        'borderColor' => $analyticsPalette['days_off'],
                        'backgroundColor' => $hexToRgba($analyticsPalette['days_off'], 0.12),
                        'tension' => 0.35,
                    ],
                    [
                        'label' => 'Attendance',
                        'data' => $monthBuckets->map(fn ($month) => $attendanceFiltered->filter(fn ($row) => str_starts_with((string) $row['date'], $month))->count())->values(),
                        'borderColor' => $analyticsPalette['attendance'],
                        'backgroundColor' => $hexToRgba($analyticsPalette['attendance'], 0.12),
                        'tension' => 0.35,
                    ],
                ],
            ],
            'casesByType' => [
                'labels' => $casesByTypeCounts->keys()->values(),
                'datasets' => [[
                    'data' => $casesByTypeCounts->values(),
                    'backgroundColor' => [
                        $analyticsPalette['complaints'],
                        $analyticsPalette['good_feedback'],
                        $analyticsPalette['alerts_van'],
                        $analyticsPalette['attendance'],
                        $analyticsPalette['days_off'],
                        $analyticsPalette['neutral'],
                    ],
                    'borderWidth' => 0,
                ]],
            ],
            'staffTypeBreakdown' => [
                'labels' => $allCases->groupBy('staff_type')->keys()->map(fn ($value) => $value ?: 'Unclassified')->values(),
                'datasets' => [[
                    'label' => 'Cases',
                    'data' => $allCases->groupBy('staff_type')->map->count()->values(),
                    'backgroundColor' => [
                        $analyticsPalette['complaints'],
                        $analyticsPalette['good_feedback'],
                        $analyticsPalette['attendance'],
                        $analyticsPalette['days_off'],
                        $analyticsPalette['alerts_van'],
                        $analyticsPalette['neutral'],
                        $analyticsPalette['complaints'],
                    ],
                    'borderRadius' => 10,
                ]],
            ],
            'statusBreakdown' => [
                'labels' => $orderedStatusCounts->keys()->values(),
                'datasets' => [[
                    'data' => $orderedStatusCounts->values(),
                    'backgroundColor' => [
                        $analyticsPalette['neutral'],
                        $analyticsPalette['complaints'],
                        $analyticsPalette['alerts_van'],
                        $analyticsPalette['good_feedback'],
                        $analyticsPalette['neutral'],
                    ],
                    'borderWidth' => 0,
                ]],
            ],
            'complaintCategories' => [
                'labels' => $complaintCategoryCounts->keys()->values(),
                'datasets' => [[
                    'label' => 'Complaints',
                    'data' => $complaintCategoryCounts->values(),
                    'backgroundColor' => $analyticsPalette['complaints'],
                    'borderRadius' => 10,
                ]],
            ],
            'employeePerformanceScore' => [
                'labels' => $employeePerformanceScores->keys()->values(),
                'datasets' => [[
                    'label' => 'Performance Score',
                    'data' => $employeePerformanceScores->values(),
                    'backgroundColor' => $employeePerformanceScores->map(function ($score) {
                        if ($score > 0) {
                            return '#22C55E';
                        }
                        if ($score < 0) {
                            return '#DC2626';
                        }

                        return '#64748B';
                    })->values(),
                    'borderRadius' => 10,
                ]],
            ],
            'netScoreTrend' => [
                'labels' => $monthBuckets->map(fn ($month) => \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y'))->values(),
                'datasets' => [[
                    'label' => 'Net Score',
                    'data' => $monthBuckets->map(function ($month) use ($allCases) {
                        $rows = $allCases->filter(fn ($row) => str_starts_with((string) $row['date'], $month));
                        return $rows->where('type', 'Good Feedback')->count() - $rows->where('type', 'Complaint')->count();
                    })->values(),
                    'borderColor' => $analyticsPalette['complaints'],
                    'backgroundColor' => $hexToRgba($analyticsPalette['complaints'], 0.12),
                    'tension' => 0.35,
                    'fill' => true,
                ]],
            ],
            'operationalIncidentsTrend' => [
                'labels' => $monthBuckets->map(fn ($month) => \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y'))->values(),
                'datasets' => [
                    [
                        'label' => 'Attendance',
                        'data' => $monthBuckets->map(fn ($month) => $attendanceFiltered->filter(fn ($row) => str_starts_with((string) $row['date'], $month))->count())->values(),
                        'borderColor' => $analyticsPalette['attendance'],
                        'backgroundColor' => $hexToRgba($analyticsPalette['attendance'], 0.12),
                        'tension' => 0.35,
                    ],
                    [
                        'label' => 'Unauthorized Days',
                        'data' => $monthBuckets->map(fn ($month) => $daysOffFiltered->filter(fn ($row) => str_starts_with((string) $row['start_date'], $month))->sum('unauthorized_days'))->values(),
                        'borderColor' => $analyticsPalette['days_off'],
                        'backgroundColor' => $hexToRgba($analyticsPalette['days_off'], 0.12),
                        'tension' => 0.35,
                    ],
                    [
                        'label' => 'Van Issues',
                        'data' => $monthBuckets->map(fn ($month) => $vanFeedbackFiltered->filter(fn ($row) => str_starts_with((string) $row['date_received'], $month))->count())->values(),
                        'borderColor' => $analyticsPalette['alerts_van'],
                        'backgroundColor' => $hexToRgba($analyticsPalette['alerts_van'], 0.12),
                        'tension' => 0.35,
                    ],
                ],
            ],
        ];

        $tabMeta = [
            'complaints' => ['title' => 'Complaints log', 'subtitle' => 'Customer issues with owner, action, and resolution status.', 'count' => $complaintsFiltered->count()],
            'good-feedback' => ['title' => 'Good feedback log', 'subtitle' => 'Positive recognition captured by event date, source, and assistant.', 'count' => $goodFeedbackFiltered->count()],
            'van-feedback' => ['title' => 'Van feedback log', 'subtitle' => 'Fleet and equipment issues tied to staff operations.', 'count' => $vanFeedbackFiltered->count()],
            'attendance' => ['title' => 'Attendance log', 'subtitle' => 'Incident tracking with units, authorization state, manager, and notes.', 'count' => $attendanceFiltered->count()],
            'days-off' => ['title' => 'Days off log', 'subtitle' => 'Request tracking for approvals, denied days, unauthorized time, and notes.', 'count' => $daysOffFiltered->count()],
            'alerts' => ['title' => 'Alerts and escalations', 'subtitle' => 'Unauthorized patterns, escalations, and urgent operational alerts.', 'count' => $alertsFiltered->count()],
        ];

        $preview = ['tag' => 'No item selected', 'status' => '', 'title' => 'No record selected', 'subtitle' => 'Choose a record to inspect operational context.', 'facts' => [], 'sections' => [], 'history' => [], 'editable' => false];
        $timelineize = function (array $history, ?string $anchorDate = null) {
            return collect($history)->values()->map(function ($item, $index) use ($anchorDate) {
                $baseDate = $item['date'] ?? $anchorDate;
                $dateLabel = null;
                if ($baseDate) {
                    try {
                        $dateLabel = \Carbon\Carbon::parse($baseDate)->addDays($index)->format('M d, Y');
                    } catch (\Throwable) {
                        $dateLabel = null;
                    }
                }

                return [
                    'title' => $item['title'] ?? 'Update',
                    'note' => $item['note'] ?? '',
                    'date' => $dateLabel,
                ];
            })->all();
        };

        if ($activeView === 'cases') {
            if ($activeTab === 'complaints') {
                $selected = $complaintsFiltered->firstWhere('complaint_id', $filters['item']) ?: $complaintsFiltered->first();
                if ($selected) {
                    $preview = [
                        'tag' => 'Complaint case',
                        'status' => $selected['resolution_status'],
                        'title' => $selected['complaint_id'] . ' • ' . $selected['team_member_display'],
                        'subtitle' => 'Complaint case for ' . $selected['team_member_display'] . ' with ' . strtolower($selected['priority']) . ' priority.',
                        'facts' => ['Event Date' => $humanDate($selected['event_date']), 'Received Date' => $humanDate($selected['date_received']), 'Team Members' => $selected['team_member_display'], 'Priority' => $selected['priority'], 'Source' => $selected['category']],
                        'sections' => [
                            ['label' => 'Summary', 'value' => $selected['description']],
                            ['label' => 'Internal Note', 'value' => $selected['action_taken']],
                        ],
                        'history' => $timelineize($selected['history'], $selected['date_received']),
                        'editable' => $canManageFeedback,
                        'item_id' => $selected['complaint_id'],
                        'item_group' => 'complaints',
                        'status_options' => ['In Review', 'Pending', 'Escalated', 'Resolved', 'Closed'],
                        'owner' => $selected['assistant'],
                        'owner_options' => $workflowOwners->all(),
                        'team_members' => $normalizeTeamMemberSelection($selected['team_members'] ?? [])->all(),
                        'team_member_options' => $teamMemberOptions->all(),
                        'source' => $selected['category'],
                        'summary' => $selected['description'],
                        'internal_note' => $selected['action_taken'],
                        'team_member' => $selected['team_member_display'],
                        'case_type' => 'Complaint',
                        'priority' => $selected['priority'],
                        'event_date' => $selected['event_date'],
                        'received_date' => $selected['date_received'],
                    ];
                }
            } elseif ($activeTab === 'good-feedback') {
                $selected = $goodFeedbackFiltered->firstWhere('feedback_id', $filters['item']) ?: $goodFeedbackFiltered->first();
                if ($selected) {
                    $preview = [
                        'tag' => 'Good feedback preview',
                        'status' => $selected['status'],
                        'title' => $selected['compliment'],
                        'subtitle' => $selected['chef'] . ' • Feedback ' . $selected['feedback_id'],
                        'facts' => ['Event Date' => $selected['event_date'], 'Date Received' => $selected['date_received'], 'Staff Type' => $selected['staff_type'] ?: 'Not classified', 'Source' => $selected['source'], 'Assistant' => $selected['assistant']],
                        'sections' => [['label' => 'Recognition', 'value' => $selected['compliment']]],
                        'history' => $timelineize($selected['history'], $selected['date_received']),
                    ];
                }
            } elseif ($activeTab === 'van-feedback') {
                $selected = $vanFeedbackFiltered->firstWhere('vanfb_id', $filters['item']) ?: $vanFeedbackFiltered->first();
                if ($selected) {
                    $preview = [
                        'tag' => 'Van issue preview',
                        'status' => $selected['status'],
                        'title' => $selected['van'] . ' • ' . $selected['description'],
                        'subtitle' => $selected['chef'] . ' • Van feedback ' . $selected['vanfb_id'],
                        'facts' => ['Event Date' => $selected['event_date'], 'Date Received' => $selected['date_received'], 'Staff Type' => $selected['staff_type'] ?: 'Not classified', 'Van' => $selected['van']],
                        'sections' => [
                            ['label' => 'Description', 'value' => $selected['description']],
                            ['label' => 'Action Taken', 'value' => $selected['action_taken']],
                        ],
                        'history' => $timelineize($selected['history'], $selected['date_received']),
                    ];
                }
            } elseif ($activeTab === 'attendance') {
                $selected = $attendanceFiltered->firstWhere('incident_id', $filters['item']) ?: $attendanceFiltered->first();
                if ($selected) {
                    $preview = [
                        'tag' => 'Attendance incident',
                        'status' => $selected['status'],
                        'title' => $selected['incident_type'],
                        'subtitle' => $selected['chef'] . ' • Incident ' . $selected['incident_id'],
                        'facts' => ['Date' => $selected['date'], 'Staff Type' => $selected['staff_type'] ?: 'Not classified', 'Manager' => $selected['manager'], 'Units' => $selected['units'], 'Authorized' => $selected['authorized'] ? 'Yes' : 'No'],
                        'sections' => [['label' => 'Notes', 'value' => $selected['notes']], ['label' => 'Operational Impact', 'value' => $selected['authorized'] ? 'Documented and authorized by manager.' : 'Requires follow-up due to attendance impact.']],
                        'history' => $timelineize($selected['history'], $selected['date']),
                    ];
                }
            } elseif ($activeTab === 'days-off') {
                $selected = $daysOffFiltered->firstWhere('request_id', $filters['item']) ?: $daysOffFiltered->first();
                if ($selected) {
                    $preview = [
                        'tag' => 'Days off request',
                        'status' => $selected['status'],
                        'title' => $selected['request_id'] . ' • ' . $selected['chef'],
                        'subtitle' => 'Request spanning ' . $humanDate($selected['start_date']) . ' to ' . $humanDate($selected['end_date']),
                        'facts' => ['Days' => $selected['days'], 'Approved By' => $selected['approved_by'], 'Unauthorized Days' => $selected['unauthorized_days']],
                        'sections' => [['label' => 'Notes', 'value' => $selected['notes']], ['label' => 'Operational Guidance', 'value' => $selected['unauthorized_days'] > 0 ? 'Triggers alert review and team summary exception count.' : 'No unauthorized time attached to this request.']],
                        'history' => $timelineize([['title' => 'Current status', 'note' => $selected['status'] . ' request tracked in the time-off log.']], $selected['start_date']),
                        'editable' => $canManageFeedback,
                        'item_id' => $selected['request_id'],
                        'item_group' => 'days-off',
                        'status_options' => ['Pending', 'Approved', 'Denied', 'Cancelled'],
                        'approved_by' => $selected['approved_by'],
                        'approved_by_options' => $workflowOwners->all(),
                        'start_date' => $selected['start_date'],
                        'end_date' => $selected['end_date'],
                        'days' => $selected['days'],
                        'notes' => $selected['notes'],
                        'team_member' => $selected['chef'],
                        'case_type' => 'Days Off',
                    ];
                }
            } elseif ($activeTab === 'alerts') {
                $selected = $alertsFiltered->firstWhere('alert_id', $filters['item']) ?: $alertsFiltered->first();
                if ($selected) {
                    $preview = [
                        'tag' => 'Alert preview',
                        'status' => $selected['status'],
                        'title' => $selected['type'],
                        'subtitle' => $selected['chef'] . ' • Alert ' . $selected['alert_id'],
                        'facts' => ['Date' => $selected['date'], 'Severity' => $selected['severity']],
                        'sections' => [['label' => 'Details', 'value' => $selected['details']], ['label' => 'Escalation Path', 'value' => 'Route urgent patterns to operations leadership and retain them in team trend history.']],
                        'history' => $timelineize([['title' => 'Alert generated', 'note' => 'Derived from operational records.']], $selected['date']),
                    ];
                }
            }
        }

        $perPage = $this->perPage($request);

        return view('admin.feedback_center', [
            'activeView' => $activeView,
            'activeTab' => $activeTab,
            'filters' => $filters,
            'stats' => $stats,
            'statusOptions' => $statusOptions,
            'typeOptions' => $typeOptions,
            'chefOptions' => $chefOptions,
            'staffTypeOptions' => $staffTypeOptions,
            'sourceOptions' => $sourceOptions,
            'complaints' => $complaintsFiltered,
            'goodFeedback' => $goodFeedbackFiltered,
            'vanFeedback' => $vanFeedbackFiltered,
            'attendance' => $attendanceFiltered,
            'daysOff' => $daysOffFiltered,
            'alerts' => $alertsFiltered,
            'complaintsPage' => $this->paginateCollection($complaintsFiltered, $request, $perPage),
            'goodFeedbackPage' => $this->paginateCollection($goodFeedbackFiltered, $request, $perPage),
            'vanFeedbackPage' => $this->paginateCollection($vanFeedbackFiltered, $request, $perPage),
            'attendancePage' => $this->paginateCollection($attendanceFiltered, $request, $perPage),
            'daysOffPage' => $this->paginateCollection($daysOffFiltered, $request, $perPage),
            'alertsPage' => $this->paginateCollection($alertsFiltered, $request, $perPage),
            'perPage' => $perPage,
            'totalFilteredCases' => $totalFilteredCases,
            'openWorkflowCount' => $openWorkflowCount,
            'teamSummaries' => $teamSummaries,
            'topGoodFeedbackLeaderboard' => $topGoodFeedbackLeaderboard,
            'topComplaintsLeaderboard' => $topComplaintsLeaderboard,
            'analyticsCharts' => $analyticsCharts,
            'tabMeta' => $tabMeta,
            'preview' => $preview,
            'canManageFeedback' => $canManageFeedback,
        ]);
    }

    public function updateWorkflow(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'item_id' => ['required', 'string', 'max:100'],
            'item_group' => ['required', 'in:complaints,days-off'],
            'status' => ['nullable', 'string', 'max:50'],
            'owner' => ['nullable', 'string', 'max:255'],
            'approved_by' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date', 'required_if:item_group,days-off'],
            'end_date' => ['nullable', 'date', 'required_if:item_group,days-off', 'after_or_equal:start_date'],
            'team_members' => ['nullable', 'array', 'max:7'],
            'team_members.*' => ['nullable', 'string', 'max:160'],
            'source' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:10000'],
            'internal_note' => ['nullable', 'string', 'max:2000'],
            'workflow_action' => ['nullable', 'in:save,resolve,escalate,reopen'],
            'back' => ['nullable', 'string', 'max:2000'],
        ]);

        $group = $payload['item_group'];
        $status = trim((string) ($payload['status'] ?? ''));
        $owner = trim((string) ($payload['owner'] ?? ''));
        $approvedBy = trim((string) ($payload['approved_by'] ?? $owner));
        $startDate = filled($payload['start_date'] ?? null) ? (string) $payload['start_date'] : '';
        $endDate = filled($payload['end_date'] ?? null) ? (string) $payload['end_date'] : '';
        $daysValue = null;
        $teamMembers = collect($payload['team_members'] ?? [])
            ->map(fn ($member) => trim((string) $member))
            ->filter()
            ->unique()
            ->values();
        $source = trim((string) ($payload['source'] ?? ''));
        $summary = trim((string) ($payload['summary'] ?? ''));
        $internalNote = trim((string) ($payload['internal_note'] ?? ''));
        $action = (string) ($payload['workflow_action'] ?? 'save');

        if ($action === 'resolve') {
            $status = 'Resolved';
        } elseif ($action === 'escalate') {
            $status = 'Escalated';
        } elseif ($action === 'reopen') {
            $status = 'Pending';
        }

        $backUrl = filled($payload['back'] ?? null)
            ? (string) $payload['back']
            : route('admin.feedback');

        if ($group === 'complaints' && $status !== '' && !in_array($status, ['In Review', 'Pending', 'Escalated', 'Resolved', 'Closed'], true)) {
            return redirect()->to($backUrl)
                ->withErrors(['status' => 'Complaint status must be In Review, Pending, Escalated, Resolved, or Closed.']);
        }

        if ($group === 'complaints') {
            if ($teamMembers->isEmpty()) {
                return redirect()->to($backUrl)
                    ->withErrors(['team_members' => 'Select at least one team member for the complaint.']);
            }

            $validTeamMembers = User::query()
                ->where('is_active', true)
                ->whereIn('staff_type', TeamController::STAFF_TYPES)
                ->pluck('name')
                ->all();

            if ($teamMembers->count() > 7 || $teamMembers->diff($validTeamMembers)->isNotEmpty()) {
                return redirect()->to($backUrl)
                    ->withErrors(['team_members' => 'Maximum 7 active team members per complaint.']);
            }
        }

        $itemId = $payload['item_id'];
        $recordFound = false;
        $resolveDisplayId = function (string $value, string $prefix): ?int {
            if (!str_starts_with($value, $prefix . '-')) {
                return ctype_digit($value) ? (int) $value : null;
            }

            $numeric = substr($value, strlen($prefix) + 1);

            return ctype_digit($numeric) ? ((int) ltrim($numeric, '0') ?: 0) : null;
        };

        switch ($group) {
            case 'complaints':
                $modelId = $resolveDisplayId($itemId, 'CP');
                if ($modelId !== null && $model = Complaint::query()->find($modelId)) {
                    $recordFound = true;
                    if ($status !== '') {
                        $model->resolution_status = $status;
                    }
                    if ($owner !== '') {
                        $model->assistant = $owner;
                    }
                    if ($source !== '') {
                        $model->category = $source;
                    }
                    if ($summary !== '') {
                        $model->description = $summary;
                    }
                    if ($teamMembers->isNotEmpty()) {
                        $model->chef = (string) $teamMembers->first();
                        $model->team_members = $teamMembers->all();
                    }
                    $model->action_taken = $internalNote;
                    $model->save();
                }
                break;

            case 'days-off':
                $recordFound = true;
                if ($startDate !== '' && $endDate !== '') {
                    $daysValue = \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1;
                }
                if ($model = DaysOffRequest::query()->where('request_id', $itemId)->first()) {
                    if ($status !== '') {
                        $model->status = $status;
                    }
                    if ($approvedBy !== '') {
                        $model->approved_by = $approvedBy;
                    }
                    if ($startDate !== '') {
                        $model->start_date = $startDate;
                    }
                    if ($endDate !== '') {
                        $model->end_date = $endDate;
                    }
                    if ($daysValue !== null) {
                        $model->days = $daysValue;
                    }
                    $model->notes = $internalNote;
                    $model->save();
                }
                break;
        }

        if (!$recordFound) {
            return redirect()->to($backUrl)->with('ok', 'Unable to locate that workflow record.');
        }

        $existing = Cache::get($this->feedbackWorkflowCacheKey($group, $itemId), []);
        if (!is_array($existing)) {
            $existing = [];
        }

        $historyTitle = match ($action) {
            'resolve' => 'Case resolved',
            'escalate' => 'Case escalated',
            'reopen' => 'Case reopened',
            default => 'Workflow updated',
        };

        $historyNoteParts = [];
        if ($status !== '') {
            $historyNoteParts[] = 'Status set to ' . $status . '.';
        }
        if ($owner !== '') {
            $historyNoteParts[] = 'Owner set to ' . $owner . '.';
        }
        if ($internalNote !== '') {
            $historyNoteParts[] = $internalNote;
        }

        $existing['status'] = $status !== '' ? $status : ($existing['status'] ?? '');
        $existing['owner'] = $owner !== '' ? $owner : ($existing['owner'] ?? '');
        $existing['team_members'] = $teamMembers->isNotEmpty() ? $teamMembers->all() : ($existing['team_members'] ?? []);
        $existing['source'] = $source !== '' ? $source : ($existing['source'] ?? '');
        $existing['summary'] = $summary !== '' ? $summary : ($existing['summary'] ?? '');
        $existing['approved_by'] = $group === 'days-off' ? $approvedBy : ($approvedBy !== '' ? $approvedBy : ($existing['approved_by'] ?? ''));
        $existing['start_date'] = $startDate !== '' ? $startDate : ($existing['start_date'] ?? '');
        $existing['end_date'] = $endDate !== '' ? $endDate : ($existing['end_date'] ?? '');
        $existing['days'] = $daysValue !== null ? $daysValue : ($existing['days'] ?? null);
        $existing['notes'] = $group === 'days-off' ? $internalNote : ($existing['notes'] ?? '');
        $existing['internal_note'] = $internalNote !== '' ? $internalNote : ($existing['internal_note'] ?? '');
        $existing['history'] = array_values(array_merge($existing['history'] ?? [], [[
            'title' => $historyTitle,
            'note' => $historyNoteParts !== [] ? implode(' ', $historyNoteParts) : 'Case workflow updated.',
            'date' => now()->toDateString(),
        ]]));

        Cache::put($this->feedbackWorkflowCacheKey($group, $itemId), $existing, now()->addDays(30));

        return redirect()->to(
            filled($payload['back'] ?? null)
                ? (string) $payload['back']
                : route('admin.feedback', ['view' => 'cases', 'tab' => $group, 'item' => $itemId])
        )->with('ok', 'Workflow updated.');
    }

    private function feedbackWorkflowCacheKey(string $group, string $itemId): string
    {
        return 'feedback-center-workflow:' . $group . ':' . $itemId;
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 25);

        return in_array($perPage, [10, 15, 25], true) ? $perPage : 25;
    }

    private function paginateCollection(Collection $items, Request $request, int $perPage): LengthAwarePaginator
    {
        $page = max(1, (int) $request->query('page', 1));
        $items = $items->values();

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->except('page'),
            ]
        );
    }

    private function rulesFor(string $type): array
    {
        return match ($type) {
            'complaint' => [
                'type' => ['required', 'in:complaint'],
                'event_date' => ['required', 'date'],
                'date_received' => ['required', 'date'],
                'team_members' => ['required', 'array', 'min:1', 'max:7'],
                'team_members.*' => ['required', 'string', 'max:160', Rule::in($this->staffOptions())],
                'chef' => ['nullable', 'string', 'max:160'],
                'category' => ['required', 'string', 'max:160', Rule::in($this->complaintCategories())],
                'description' => ['required', 'string', 'max:10000'],
                'resolution_status' => ['required', 'string', 'max:80', Rule::in(['In Review', 'Pending', 'Escalated', 'Resolved', 'Closed'])],
                'assistant' => ['nullable', 'string', 'max:160'],
                'action_taken' => ['nullable', 'string', 'max:10000'],
            ],
            'good-feedback' => [
                'type' => ['required', 'in:good-feedback'],
                'event_date' => ['required', 'date'],
                'date_received' => ['required', 'date'],
                'chef' => ['required', 'string', 'max:160'],
                'source' => ['required', 'string', 'max:160'],
                'compliment' => ['required', 'string', 'max:10000'],
                'assistant' => ['nullable', 'string', 'max:160'],
            ],
            'van-feedback' => [
                'type' => ['required', 'in:van-feedback'],
                'event_date' => ['required', 'date'],
                'date_received' => ['required', 'date'],
                'chef' => ['required', 'string', 'max:160'],
                'van' => ['required', 'string', 'max:120'],
                'description' => ['required', 'string', 'max:10000'],
                'action_taken' => ['nullable', 'string', 'max:10000'],
            ],
            'attendance' => [
                'type' => ['required', 'in:attendance'],
                'date' => ['required', 'date'],
                'chef' => ['required', 'string', 'max:160'],
                'incident_type' => ['required', 'string', 'max:160'],
                'units' => ['required', 'integer', 'min:0', 'max:100'],
                'authorized' => ['required', 'in:Yes,No'],
                'manager' => ['nullable', 'string', 'max:160'],
                'notes' => ['nullable', 'string', 'max:10000'],
            ],
            'days-off' => [
                'type' => ['required', 'in:days-off'],
                'chef' => ['required', 'string', 'max:160'],
                'request_type' => ['required', 'string', 'max:80'],
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date', 'after_or_equal:start_date'],
                'status' => ['required', 'string', 'max:80'],
                'approved_by' => ['nullable', 'string', 'max:160'],
                'notes' => ['nullable', 'string', 'max:10000'],
                'unauthorized_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            ],
            default => [],
        };
    }

    private function typeMap(): array
    {
        $staffOptions = $this->staffOptions();

        return [
            'complaint' => [
                'title' => 'New Complaint',
                'subtitle' => 'Log a customer issue, service problem, or operational complaint tied to an active team member.',
                'tab' => 'complaints',
                'success' => 'Complaint created successfully.',
                'fields' => [
                    ['name' => 'event_date', 'label' => 'Event Date', 'type' => 'date', 'required' => true],
                    ['name' => 'date_received', 'label' => 'Date Received', 'type' => 'date', 'required' => true],
                    ['name' => 'team_members', 'label' => 'Team Members', 'type' => 'team-members', 'required' => true],
                    ['name' => 'category', 'label' => 'Category', 'type' => 'select', 'options' => $this->complaintCategories(), 'required' => true],
                    ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'required' => true],
                    ['name' => 'resolution_status', 'label' => 'Resolution Status', 'type' => 'select', 'options' => ['In Review', 'Pending', 'Escalated', 'Resolved', 'Closed'], 'required' => true],
                    ['name' => 'assistant', 'label' => 'Assistant', 'type' => 'text', 'required' => false],
                    ['name' => 'action_taken', 'label' => 'Action Taken', 'type' => 'textarea', 'required' => false],
                ],
            ],
            'good-feedback' => [
                'title' => 'New Good Feedback',
                'subtitle' => 'Capture positive customer or partner feedback tied to an active team member or event.',
                'tab' => 'good-feedback',
                'success' => 'Good feedback created successfully.',
                'fields' => [
                    ['name' => 'event_date', 'label' => 'Event Date', 'type' => 'date', 'required' => true],
                    ['name' => 'date_received', 'label' => 'Date Received', 'type' => 'date', 'required' => true],
                    ['name' => 'chef', 'label' => 'Team Member', 'type' => 'select', 'options' => $staffOptions, 'required' => true],
                    ['name' => 'source', 'label' => 'Source', 'type' => 'select', 'options' => ['Post-event survey', 'Account manager', 'Google review', 'Phone call'], 'required' => true],
                    ['name' => 'compliment', 'label' => 'Compliment', 'type' => 'textarea', 'required' => true],
                    ['name' => 'assistant', 'label' => 'Assistant', 'type' => 'text', 'required' => false],
                ],
            ],
            'van-feedback' => [
                'title' => 'New Van Feedback',
                'subtitle' => 'Track fleet, loading, and equipment issues reported by the field team.',
                'tab' => 'van-feedback',
                'success' => 'Van feedback created successfully.',
                'fields' => [
                    ['name' => 'event_date', 'label' => 'Event Date', 'type' => 'date', 'required' => true],
                    ['name' => 'date_received', 'label' => 'Date Received', 'type' => 'date', 'required' => true],
                    ['name' => 'chef', 'label' => 'Team Member', 'type' => 'select', 'options' => $staffOptions, 'required' => true],
                    ['name' => 'van', 'label' => 'Van', 'type' => 'select', 'options' => ['Van 1', 'Van 2', 'Van 3', 'Van 4', 'Van 5'], 'required' => true],
                    ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'required' => true],
                    ['name' => 'action_taken', 'label' => 'Action Taken', 'type' => 'textarea', 'required' => false],
                ],
            ],
            'attendance' => [
                'title' => 'New Attendance Incident',
                'subtitle' => 'Record lateness, missed shifts, call-outs, and manager-reviewed attendance issues for active team members.',
                'tab' => 'attendance',
                'success' => 'Attendance incident created successfully.',
                'fields' => [
                    ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true],
                    ['name' => 'chef', 'label' => 'Team Member', 'type' => 'select', 'options' => $staffOptions, 'required' => true],
                    ['name' => 'incident_type', 'label' => 'Incident Type', 'type' => 'select', 'options' => ['Late clock-in', 'Call-out', 'Missed training', 'No-show'], 'required' => true],
                    ['name' => 'units', 'label' => 'Units', 'type' => 'number', 'required' => true],
                    ['name' => 'authorized', 'label' => 'Authorized', 'type' => 'select', 'options' => ['Yes', 'No'], 'required' => true],
                    ['name' => 'manager', 'label' => 'Manager', 'type' => 'text', 'required' => false],
                    ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'required' => false],
                ],
            ],
            'days-off' => [
                'title' => 'New Days Off Request',
                'subtitle' => 'Log vacation, sick, and personal time requests in the same operations workflow used by the Days Off log.',
                'tab' => 'days-off',
                'success' => 'Days off request created successfully.',
                'fields' => [
                    ['name' => 'chef', 'label' => 'Team Member', 'type' => 'select', 'options' => $staffOptions, 'required' => true],
                    ['name' => 'request_type', 'label' => 'Request Type', 'type' => 'select', 'options' => ['Vacation', 'Personal', 'Sick', 'Emergency', 'Other'], 'required' => true],
                    ['name' => 'start_date', 'label' => 'Start Date', 'type' => 'date', 'required' => true],
                    ['name' => 'end_date', 'label' => 'End Date', 'type' => 'date', 'required' => true],
                    ['name' => 'status', 'label' => 'Approval Status', 'type' => 'select', 'options' => ['Pending', 'Approved', 'Denied'], 'required' => true],
                    ['name' => 'approved_by', 'label' => 'Approved By', 'type' => 'text', 'required' => false],
                    ['name' => 'unauthorized_days', 'label' => 'Unauthorized Days', 'type' => 'number', 'required' => false],
                    ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'required' => false],
                ],
            ],
        ];
    }

    private function nextDaysOffRequestId(): string
    {
        $maxPersisted = DaysOffRequest::query()
            ->pluck('request_id')
            ->map(function ($requestId) {
                if (preg_match('/DO-(\d+)/', (string) $requestId, $matches)) {
                    return (int) $matches[1];
                }

                return 0;
            })
            ->max() ?? 0;

        $next = max(1048, $maxPersisted) + 1;

        return 'DO-' . $next;
    }

    private function complaintCategories(): array
    {
        return ['Late arrival', 'Food pacing', 'Setup issue', 'Billing confusion', 'Other'];
    }

    private function staffOptions(): array
    {
        return User::query()
            ->active()
            ->whereIn('staff_type', TeamController::STAFF_TYPES)
            ->orderBy('name')
            ->pluck('name')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function teamMemberOptions(): array
    {
        return User::query()
            ->active()
            ->whereIn('staff_type', TeamController::STAFF_TYPES)
            ->orderBy('name')
            ->get(['name', 'staff_type'])
            ->map(fn ($user) => [
                'value' => $user->name,
                'label' => $user->name,
                'meta' => $user->staff_type ?: 'Unclassified',
            ])
            ->unique('value')
            ->values()
            ->all();
    }
}
