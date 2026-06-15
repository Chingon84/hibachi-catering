<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Team\StoreTeamMemberRequest;
use App\Http\Requests\Admin\Team\UpdateTeamMemberRequest;
use App\Models\AttendanceIncident;
use App\Models\Complaint;
use App\Models\GoodFeedback;
use App\Models\TeamMemberActivity;
use App\Models\TeamMemberDocument;
use App\Support\UploadedFiles;
use App\Models\User;
use App\Models\VanFeedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TeamController extends Controller
{
    public const STAFF_TYPES = ['Chef', 'Assistant', 'Server', 'Office', 'Manager', 'Fire Show', 'Driver', 'Fleet', 'Admin', 'Other'];
    public const EMPLOYEE_TYPES = ['Full Time', 'Part Time', 'Seasonal', 'Contractor', 'Temporary', 'Intern', 'Other'];
    public const DOCUMENT_TYPES = ['Warning', 'ID', 'Contract', 'License', 'Other'];
    public const PASSWORD_PLACEHOLDER = '__KEEP_EXISTING_PASSWORD__';

    public function index(Request $request)
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'staff_type' => trim((string) $request->query('staff_type', '')),
            'status' => trim((string) $request->query('status', '')),
        ];

        $query = User::query()
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
            ->when($filters['status'] !== '', fn ($query) => $query->where('is_active', $filters['status'] === 'active'));

        $perPage = $this->adminPerPage($request);
        $summary = [
            'total' => (clone $query)->count(),
            'active_chefs' => (clone $query)->where('is_active', true)->where('staff_type', 'Chef')->count(),
            'admin_access' => (clone $query)->where('can_access_admin', true)->count(),
            'inactive' => (clone $query)->where('is_active', false)->count(),
        ];

        $users = $query->orderBy('name')->paginate($perPage)->withQueryString();

        return view('admin.team.index', [
            'users' => $users,
            'filters' => $filters,
            'staffTypes' => self::STAFF_TYPES,
            'summary' => $summary,
            'perPage' => $perPage,
        ]);
    }

    private function adminPerPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 25);

        return in_array($perPage, [10, 15, 25], true) ? $perPage : 25;
    }

    public function create()
    {
        $user = new User();

        return view('admin.team.form', [
            'user' => $user,
            'staffTypes' => self::STAFF_TYPES,
            'employeeTypes' => self::EMPLOYEE_TYPES,
        ]);
    }

    public function store(StoreTeamMemberRequest $request)
    {
        $data = $request->validated();

        $data['password'] = Hash::make($data['password']);
        $data['can_access_admin'] = (bool) ($data['can_access_admin'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $user = User::create($data);

        $this->logActivity($user, 'profile_created', 'Profile created', 'Employee profile was created in the team directory.');

        return redirect()->route('admin.team.index')->with('ok', 'User created');
    }

    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $activeTab = strtolower((string) $request->query('tab', 'overview'));
        $tabs = ['overview', 'documents', 'incidents', 'permissions', 'activity'];
        if (!in_array($activeTab, $tabs, true)) {
            $activeTab = 'overview';
        }

        $documents = $activeTab === 'documents'
            ? $user->documents()->with('uploader:id,name')->latest()->paginate(25, ['*'], 'documents_page')->withQueryString()
            : collect();
        $incidentFeed = $activeTab === 'incidents'
            ? $this->incidentFeedPaginator($user->name, $request)
            : collect();
        $activity = $activeTab === 'activity'
            ? $user->teamActivities()->with('actor:id,name')->latest()->paginate(25, ['*'], 'activity_page')->withQueryString()
            : collect();

        return view('admin.team.show', [
            'user' => $user,
            'activeTab' => $activeTab,
            'documentTypes' => self::DOCUMENT_TYPES,
            'documents' => $documents,
            'incidentFeed' => $incidentFeed,
            'moduleAccess' => $this->buildModuleAccess($user),
            'activity' => $activity,
            'overviewStats' => [
                'complaints' => Complaint::query()->where('chef', $user->name)->count(),
                'attendance' => AttendanceIncident::query()->where('chef', $user->name)->count(),
                'recognition' => GoodFeedback::query()->where('chef', $user->name)->count(),
            ],
        ]);
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('admin.team.form', [
            'user' => $user,
            'staffTypes' => self::STAFF_TYPES,
            'employeeTypes' => self::EMPLOYEE_TYPES,
        ]);
    }

    public function update(UpdateTeamMemberRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validated();

        // Hash the new password if one was provided (placeholder already replaced by FormRequest).
        $passwordInput = (string) ($data['password'] ?? '');
        if ($passwordInput !== '') {
            $data['password'] = Hash::make($data['password']);
            $this->logActivity($user, 'password_changed', 'Password updated', 'Account password was changed from the team management workflow.');
        } else {
            unset($data['password']);
        }

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
            now()->format('YmdHis') . '-' . $slug . '.' . $extension,
            UploadedFiles::disk()
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

    public function updateProfilePhoto(Request $request, $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'profile_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $file = $validated['profile_photo'];
        $safeName = now()->format('YmdHis') . '-' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $safeName = trim($safeName, '-') ?: now()->format('YmdHis') . '-profile';
        $path = $file->storeAs(
            'team-profile-photos/' . $user->id,
            $safeName . '.' . $file->getClientOriginalExtension(),
            UploadedFiles::disk()
        );

        if (!empty($user->profile_photo_path)) {
            Storage::disk(UploadedFiles::disk())->delete($user->profile_photo_path);
        }

        $user->profile_photo_path = $path;
        $user->save();

        $this->logActivity(
            $user,
            'profile_photo_updated',
            'Profile photo updated',
            'The employee profile photo was updated.',
            ['profile_photo_path' => $path]
        );

        $redirect = $request->query('back') === 'edit'
            ? redirect()->route('admin.team.edit', $user->id)
            : redirect()->route('admin.team.show', ['id' => $user->id, 'tab' => $request->query('tab', 'overview')]);

        return $redirect->with('ok', 'Profile photo updated.');
    }

    public function destroyProfilePhoto(Request $request, $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if (!empty($user->profile_photo_path)) {
            Storage::disk(UploadedFiles::disk())->delete($user->profile_photo_path);
        }

        $user->profile_photo_path = null;
        $user->save();

        $this->logActivity(
            $user,
            'profile_photo_removed',
            'Profile photo removed',
            'The employee profile photo was removed.'
        );

        $redirect = $request->query('back') === 'edit'
            ? redirect()->route('admin.team.edit', $user->id)
            : redirect()->route('admin.team.show', ['id' => $user->id, 'tab' => $request->query('tab', 'overview')]);

        return $redirect->with('ok', 'Profile photo removed.');
    }

    public function downloadDocument($id, $documentId)
    {
        $user = User::findOrFail($id);
        $document = TeamMemberDocument::query()
            ->where('team_member_id', $user->id)
            ->findOrFail($documentId);

        return Storage::disk(UploadedFiles::disk())->download($document->file_path, basename($document->file_path));
    }

    public function viewDocument($id, $documentId)
    {
        $user = User::findOrFail($id);
        $document = TeamMemberDocument::query()
            ->where('team_member_id', $user->id)
            ->findOrFail($documentId);

        return Storage::disk(UploadedFiles::disk())->response($document->file_path, basename($document->file_path), [
            'Content-Disposition' => 'inline; filename="' . basename($document->file_path) . '"',
        ]);
    }

    public function destroyDocument($id, $documentId): RedirectResponse
    {
        $user = User::findOrFail($id);
        $document = TeamMemberDocument::query()
            ->where('team_member_id', $user->id)
            ->findOrFail($documentId);

        Storage::disk(UploadedFiles::disk())->delete($document->file_path);

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

    private function incidentFeedPaginator(string $chefName, Request $request): LengthAwarePaginator
    {
        $queries = [
            Complaint::query()->selectRaw("
                'Complaint' as kind,
                'text-red-600 bg-red-50' as tone,
                complaints.date_received as date,
                complaints.category as summary,
                complaints.description as detail,
                complaints.resolution_status as status,
                CONCAT('CMP-', complaints.id) as reference,
                complaints.date_received as sort_date
            ")->where('chef', $chefName)->toBase(),
            AttendanceIncident::query()->selectRaw("
                'Attendance Incident' as kind,
                'text-blue-600 bg-blue-50' as tone,
                attendance_incidents.date as date,
                attendance_incidents.incident_type as summary,
                COALESCE(attendance_incidents.notes, 'Manager-reviewed attendance issue.') as detail,
                CASE WHEN attendance_incidents.authorized = 1 THEN 'Authorized' ELSE 'Unauthorized' END as status,
                CONCAT('ATT-', attendance_incidents.id) as reference,
                attendance_incidents.date as sort_date
            ")->where('chef', $chefName)->toBase(),
            GoodFeedback::query()->selectRaw("
                'Recognition' as kind,
                'text-green-600 bg-green-50' as tone,
                good_feedback.date_received as date,
                good_feedback.source as summary,
                good_feedback.compliment as detail,
                'Logged' as status,
                CONCAT('GF-', good_feedback.id) as reference,
                good_feedback.date_received as sort_date
            ")->where('chef', $chefName)->toBase(),
            VanFeedback::query()->selectRaw("
                'Van Feedback' as kind,
                'text-orange-600 bg-orange-50' as tone,
                van_feedback.date_received as date,
                van_feedback.van as summary,
                van_feedback.description as detail,
                CASE WHEN van_feedback.action_taken IS NOT NULL AND van_feedback.action_taken <> '' THEN 'In Review' ELSE 'Open' END as status,
                CONCAT('VAN-', van_feedback.id) as reference,
                van_feedback.date_received as sort_date
            ")->where('chef', $chefName)->toBase(),
        ];

        $combined = array_shift($queries);
        foreach ($queries as $query) {
            $combined->unionAll($query);
        }

        $paginator = DB::query()
            ->fromSub($combined, 'incident_feed')
            ->select(['kind', 'tone', 'date', 'summary', 'detail', 'status', 'reference'])
            ->orderByDesc('sort_date')
            ->paginate(25, ['*'], 'incidents_page')
            ->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()->map(fn ($item) => [
                'kind' => $item->kind,
                'tone' => $item->tone,
                'date' => $item->date,
                'summary' => $item->summary,
                'detail' => $item->detail,
                'status' => $item->status,
                'reference' => $item->reference,
            ])
        );

        return $paginator;
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
