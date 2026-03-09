<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceIncident;
use App\Models\Complaint;
use App\Models\GoodFeedback;
use App\Models\TeamMemberActivity;
use App\Models\TeamMemberDocument;
use App\Models\User;
use App\Models\VanFeedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TeamController extends Controller
{
    public const STAFF_TYPES = ['Chef', 'Assistant', 'Server', 'Office', 'Manager', 'Fire Show', 'Driver', 'Fleet', 'Admin', 'Other'];
    public const DOCUMENT_TYPES = ['Warning', 'ID', 'Contract', 'License', 'Other'];

    public function index(Request $request)
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'staff_type' => trim((string) $request->query('staff_type', '')),
            'status' => trim((string) $request->query('status', '')),
        ];

        $users = User::query()
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($nested) use ($filters) {
                    $nested->where('name', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('email', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('username', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('position', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('phone', 'like', '%' . $filters['q'] . '%');
                });
            })
            ->when($filters['staff_type'] !== '', fn ($query) => $query->where('staff_type', $filters['staff_type']))
            ->when($filters['status'] !== '', fn ($query) => $query->where('is_active', $filters['status'] === 'active'))
            ->orderBy('name')
            ->get();

        return view('admin.team.index', [
            'users' => $users,
            'filters' => $filters,
            'staffTypes' => self::STAFF_TYPES,
        ]);
    }

    public function create()
    {
        $user = new User();

        return view('admin.team.form', [
            'user' => $user,
            'staffTypes' => self::STAFF_TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'position' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'staff_type' => ['nullable', 'string', 'max:40', Rule::in(self::STAFF_TYPES)],
            'role' => ['required', 'string', 'max:50', Rule::in(['owner', 'admin', 'manager', 'staff', 'readonly', 'office'])],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'can_access_admin' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $validator->validated();

        if (($data['role'] ?? '') === 'owner' && !Auth::user()->isOwner()) {
            abort(403, 'Only owner can assign owner role');
        }

        unset($data['password_confirmation']);
        $data['password'] = Hash::make($data['password']);
        $data['can_access_admin'] = (bool) ($data['can_access_admin'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $user = User::create($data);

        $this->logActivity($user, 'profile_created', 'Profile created', 'Employee profile was created in the team directory.');

        return redirect()->route('admin.team.index')->with('ok', 'User created');
    }

    public function show(Request $request, $id)
    {
        $user = User::with([
            'documents.uploader:id,name',
            'teamActivities.actor:id,name',
        ])->findOrFail($id);

        $activeTab = strtolower((string) $request->query('tab', 'overview'));
        $tabs = ['overview', 'documents', 'incidents', 'permissions', 'activity'];
        if (!in_array($activeTab, $tabs, true)) {
            $activeTab = 'overview';
        }

        $complaints = Complaint::query()
            ->where('chef', $user->name)
            ->latest('date_received')
            ->get();

        $attendanceIncidents = AttendanceIncident::query()
            ->where('chef', $user->name)
            ->latest('date')
            ->get();

        $recognition = GoodFeedback::query()
            ->where('chef', $user->name)
            ->latest('date_received')
            ->get();

        $vanFeedback = VanFeedback::query()
            ->where('chef', $user->name)
            ->latest('date_received')
            ->get();

        return view('admin.team.show', [
            'user' => $user,
            'activeTab' => $activeTab,
            'documentTypes' => self::DOCUMENT_TYPES,
            'documents' => $user->documents->sortByDesc('created_at')->values(),
            'incidentFeed' => $this->buildIncidentFeed($complaints, $attendanceIncidents, $recognition, $vanFeedback),
            'moduleAccess' => $this->buildModuleAccess($user),
            'activity' => $user->teamActivities->sortByDesc('created_at')->values(),
            'overviewStats' => [
                'complaints' => $complaints->count(),
                'attendance' => $attendanceIncidents->count(),
                'recognition' => $recognition->count(),
            ],
        ]);
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('admin.team.form', [
            'user' => $user,
            'staffTypes' => self::STAFF_TYPES,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'position' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'staff_type' => ['nullable', 'string', 'max:40', Rule::in(self::STAFF_TYPES)],
            'role' => ['required', 'string', 'max:50', Rule::in(['owner', 'admin', 'manager', 'staff', 'readonly', 'office'])],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'can_access_admin' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $validator->validated();

        if ($user->isOwner() && !Auth::user()->isOwner()) {
            abort(403, 'Only owner can modify owner');
        }
        if (($data['role'] ?? '') === 'owner' && !Auth::user()->isOwner()) {
            abort(403, 'Only owner can assign owner role');
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
            $this->logActivity($user, 'password_changed', 'Password updated', 'Account password was changed from the team management workflow.');
        } else {
            unset($data['password']);
        }
        unset($data['password_confirmation']);

        $data['can_access_admin'] = (bool) ($data['can_access_admin'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $user->update($data);

        return redirect()->route('admin.team.index')->with('ok', 'User updated');
    }

    public function storeDocument(Request $request, $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'type' => ['required', Rule::in(self::DOCUMENT_TYPES)],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $file = $validated['file'];
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = Str::slug($name) ?: 'document';
        $extension = $file->getClientOriginalExtension();
        $storedPath = $file->storeAs(
            'team-documents/' . $user->id,
            now()->format('YmdHis') . '-' . $slug . '.' . $extension
        );

        $document = TeamMemberDocument::create([
            'team_member_id' => $user->id,
            'type' => $validated['type'],
            'file_path' => $storedPath,
            'uploaded_by' => Auth::id(),
        ]);

        $this->logActivity(
            $user,
            'document_uploaded',
            $validated['type'] . ' uploaded',
            'A ' . strtolower($validated['type']) . ' document was uploaded.',
            ['document_id' => $document->id, 'type' => $validated['type']]
        );

        if ($validated['type'] === 'Warning') {
            $this->logActivity(
                $user,
                'warning_issued',
                'Warning issued',
                'A warning document was issued and added to the employee file.',
                ['document_id' => $document->id]
            );
        }

        return redirect()->route('admin.team.show', ['id' => $user->id, 'tab' => 'documents'])->with('ok', 'Document uploaded.');
    }

    public function downloadDocument($id, $documentId)
    {
        $user = User::findOrFail($id);
        $document = TeamMemberDocument::query()
            ->where('team_member_id', $user->id)
            ->findOrFail($documentId);

        return Storage::download($document->file_path, basename($document->file_path));
    }

    public function destroyDocument($id, $documentId): RedirectResponse
    {
        $user = User::findOrFail($id);
        $document = TeamMemberDocument::query()
            ->where('team_member_id', $user->id)
            ->findOrFail($documentId);

        Storage::delete($document->file_path);

        $this->logActivity(
            $user,
            'document_deleted',
            $document->type . ' removed',
            'A ' . strtolower($document->type) . ' document was removed from the employee file.',
            ['document_id' => $document->id, 'type' => $document->type]
        );

        $document->delete();

        return redirect()->route('admin.team.show', ['id' => $user->id, 'tab' => 'documents'])->with('ok', 'Document deleted.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->isOwner()) {
            abort(403, 'Owner cannot be deleted');
        }

        $user->delete();

        return redirect()->route('admin.team.index')->with('ok', 'User deleted');
    }

    public function toggleAccess($id)
    {
        $user = User::findOrFail($id);
        if ($user->isOwner()) {
            abort(403, 'Owner access cannot be toggled');
        }

        $user->can_access_admin = !$user->can_access_admin;
        $user->save();

        return back()->with('ok', 'Access updated');
    }

    private function buildIncidentFeed(
        Collection $complaints,
        Collection $attendanceIncidents,
        Collection $recognition,
        Collection $vanFeedback
    ): Collection {
        return $complaints->map(fn (Complaint $item) => [
            'kind' => 'Complaint',
            'tone' => 'text-red-600 bg-red-50',
            'date' => optional($item->date_received)->toDateString(),
            'summary' => $item->category,
            'detail' => $item->description,
            'status' => $item->resolution_status,
            'reference' => $item->complaint_id,
        ])->concat(
            $attendanceIncidents->map(fn (AttendanceIncident $item) => [
                'kind' => 'Attendance Incident',
                'tone' => 'text-blue-600 bg-blue-50',
                'date' => optional($item->date)->toDateString(),
                'summary' => $item->incident_type,
                'detail' => $item->notes ?: 'Manager-reviewed attendance issue.',
                'status' => $item->authorized ? 'Authorized' : 'Unauthorized',
                'reference' => $item->incident_id,
            ])
        )->concat(
            $recognition->map(fn (GoodFeedback $item) => [
                'kind' => 'Recognition',
                'tone' => 'text-green-600 bg-green-50',
                'date' => optional($item->date_received)->toDateString(),
                'summary' => $item->source,
                'detail' => $item->compliment,
                'status' => 'Logged',
                'reference' => $item->feedback_id,
            ])
        )->concat(
            $vanFeedback->map(fn (VanFeedback $item) => [
                'kind' => 'Van Feedback',
                'tone' => 'text-orange-600 bg-orange-50',
                'date' => optional($item->date_received)->toDateString(),
                'summary' => $item->van,
                'detail' => $item->description,
                'status' => filled($item->action_taken) ? 'In Review' : 'Open',
                'reference' => $item->vanfb_id,
            ])
        )->sortByDesc(fn (array $item) => $item['date'] ?? '')->values();
    }

    private function buildModuleAccess(User $user): Collection
    {
        return collect($user->permissions())
            ->reject(fn (string $permission) => $permission === '*')
            ->groupBy(fn (string $permission) => Str::before($permission, '.'))
            ->map(fn (Collection $items, string $module) => [
                'module' => Str::headline($module),
                'permissions' => $items->values()->all(),
            ])
            ->sortBy('module')
            ->values();
    }

    private function logActivity(User $user, string $type, string $title, ?string $description = null, array $meta = []): void
    {
        TeamMemberActivity::create([
            'team_member_id' => $user->id,
            'actor_id' => Auth::id(),
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'meta' => $meta ?: null,
        ]);
    }
}
