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
use Illuminate\Support\Facades\Schema;
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
                if (!Schema::hasColumn('complaints', 'team_members')) {
                    unset($validated['team_members']);
                }
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
