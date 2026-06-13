<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Van;
use App\Models\VanChecklist;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VanChecklistController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'from' => trim((string) $request->query('from', '')),
            'to' => trim((string) $request->query('to', '')),
            'van_number' => trim((string) $request->query('van_number', '')),
            'clean' => trim((string) $request->query('clean', '')),
            'checklist_type' => trim((string) $request->query('checklist_type', '')),
            'trip_status' => trim((string) $request->query('trip_status', '')),
            'preset' => trim((string) $request->query('preset', '')),
        ];

        $filters = $this->normalizedFilters($filters);

        $perPage = $this->adminPerPage($request);
        $records = $this->filteredQuery($filters)
            ->orderByDesc('date_time')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.inventory.checklists.index', [
            'records' => $records,
            'filters' => $filters,
            'vanOptions' => $this->vanOptions(),
            'cleanOptions' => VanChecklist::CLEAN_STATUSES,
            'checklistTypeOptions' => VanChecklist::CHECKLIST_TYPES,
            'tripStatusOptions' => VanChecklist::TRIP_STATUSES,
            'perPage' => $perPage,
        ]);
    }

    private function adminPerPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 25);

        return in_array($perPage, [10, 15, 25], true) ? $perPage : 25;
    }

    public function export(Request $request, string $format): StreamedResponse
    {
        abort_unless(in_array($format, ['csv', 'excel'], true), 404);

        $filters = $this->normalizedFilters([
            'q' => trim((string) $request->query('q', '')),
            'from' => trim((string) $request->query('from', '')),
            'to' => trim((string) $request->query('to', '')),
            'van_number' => trim((string) $request->query('van_number', '')),
            'clean' => trim((string) $request->query('clean', '')),
            'checklist_type' => trim((string) $request->query('checklist_type', '')),
            'trip_status' => trim((string) $request->query('trip_status', '')),
            'preset' => trim((string) $request->query('preset', '')),
        ]);

        $records = $this->filteredQuery($filters)
            ->orderByDesc('date_time')
            ->orderByDesc('id')
            ->get();

        $filename = 'checklist-records-' . now()->format('Ymd-His') . ($format === 'excel' ? '.xls' : '.csv');
        $headers = [
            'Content-Type' => $format === 'excel' ? 'application/vnd.ms-excel; charset=UTF-8' : 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Date Time',
                'User',
                'Van Number',
                'Checklist Type',
                'Trip Status',
                'Gas Level',
                'Clean',
                'Grills',
                'Grills Numbers',
                'Propane',
                'Tables',
                'Chairs',
                'Chairs Covers',
                'Dolly',
                'Ramps',
                'Mats',
                'Notes',
                'Picture 1',
                'Picture 2',
                'Created At',
                'Updated At',
            ]);

            foreach ($records as $record) {
                fputcsv($handle, [
                    optional($record->date_time)->format('Y-m-d H:i:s'),
                    $record->user,
                    $record->van_number,
                    $record->checklist_type,
                    $record->trip_status,
                    $record->gas_level,
                    $record->clean,
                    $record->grills,
                    $record->grills_numbers,
                    $record->propane,
                    $record->tables,
                    $record->chairs,
                    $record->chairs_covers,
                    $record->dolly,
                    $record->ramps,
                    $record->mats,
                    $record->notes,
                    $record->picture1,
                    $record->picture2,
                    optional($record->created_at)->format('Y-m-d H:i:s'),
                    optional($record->updated_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, $headers);
    }

    public function create(): View
    {
        return view('admin.inventory.checklists.form', [
            'record' => new VanChecklist([
                'date_time' => now(),
                'user' => (string) (auth()->user()->name ?? ''),
                'checklist_type' => VanChecklist::CHECKLIST_TYPES[0],
                'trip_status' => VanChecklist::TRIP_STATUSES[0],
                'gas_level' => VanChecklist::GAS_LEVEL_OPTIONS[4],
                'clean' => VanChecklist::CLEAN_STATUSES[0],
            ]),
            'vanOptions' => $this->vanOptions(),
            'checklistTypeOptions' => VanChecklist::CHECKLIST_TYPES,
            'tripStatusOptions' => VanChecklist::TRIP_STATUSES,
            'cleanOptions' => VanChecklist::CLEAN_STATUSES,
            'gasLevelOptions' => VanChecklist::GAS_LEVEL_OPTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data = $this->storeImages($request, $data);

        $record = VanChecklist::create($data);

        return redirect()->route('admin.inventory.checklists.show', $record->id)
            ->with('ok', 'Checklist saved successfully.');
    }

    public function show(int $id): View
    {
        return view('admin.inventory.checklists.show', [
            'record' => VanChecklist::findOrFail($id),
        ]);
    }

    public function edit(int $id): View
    {
        return view('admin.inventory.checklists.form', [
            'record' => VanChecklist::findOrFail($id),
            'vanOptions' => $this->vanOptions(),
            'checklistTypeOptions' => VanChecklist::CHECKLIST_TYPES,
            'tripStatusOptions' => VanChecklist::TRIP_STATUSES,
            'cleanOptions' => VanChecklist::CLEAN_STATUSES,
            'gasLevelOptions' => VanChecklist::GAS_LEVEL_OPTIONS,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $record = VanChecklist::findOrFail($id);
        $data = $this->validated($request, $record);
        $data = $this->storeImages($request, $data, $record);

        $record->update($data);

        return redirect()->route('admin.inventory.checklists.index')
            ->with('ok', 'Checklist updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $record = VanChecklist::findOrFail($id);

        foreach (['picture1', 'picture2'] as $field) {
            if ($record->{$field}) {
                Storage::disk('public')->delete($record->{$field});
            }
        }

        $record->delete();

        return redirect()->route('admin.inventory.checklists.index')
            ->with('ok', 'Checklist deleted successfully.');
    }

    private function validated(Request $request, ?VanChecklist $record = null): array
    {
        return $request->validate([
            'date_time' => ['required', 'date'],
            'user' => ['required', 'string', 'max:160'],
            'van_number' => ['required', 'string', 'max:60'],
            'checklist_type' => ['required', Rule::in(VanChecklist::CHECKLIST_TYPES)],
            'trip_status' => ['required', Rule::in(VanChecklist::TRIP_STATUSES)],
            'gas_level' => ['required', Rule::in(VanChecklist::GAS_LEVEL_OPTIONS)],
            'grills' => ['required', 'integer', 'min:0'],
            'grills_numbers' => ['nullable', 'string', 'max:255'],
            'propane' => ['required', 'integer', 'min:0'],
            'tables' => ['required', 'integer', 'min:0'],
            'chairs' => ['required', 'integer', 'min:0'],
            'chairs_covers' => ['required', 'integer', 'min:0'],
            'dolly' => ['required', 'integer', 'min:0'],
            'ramps' => ['required', 'integer', 'min:0'],
            'mats' => ['required', 'integer', 'min:0'],
            'clean' => ['nullable', Rule::in(VanChecklist::CLEAN_STATUSES)],
            'notes' => ['nullable', 'string', 'max:5000'],
            'picture1' => ['nullable', 'image', 'max:5120'],
            'picture2' => ['nullable', 'image', 'max:5120'],
        ]);
    }

    private function storeImages(Request $request, array $data, ?VanChecklist $record = null): array
    {
        foreach (['picture1', 'picture2'] as $field) {
            if ($request->hasFile($field)) {
                if ($record && $record->{$field}) {
                    Storage::disk('public')->delete($record->{$field});
                }

                $data[$field] = $request->file($field)->store('inventory/checklists', 'public');
            } elseif ($request->boolean('remove_' . $field) && $record) {
                if ($record->{$field}) {
                    Storage::disk('public')->delete($record->{$field});
                }
                $data[$field] = null;
            } else {
                unset($data[$field]);
            }
        }

        return $data;
    }

    private function filteredQuery(array $filters)
    {
        return VanChecklist::query()
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($nested) use ($filters) {
                    $nested->where('user', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('van_number', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('checklist_type', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('trip_status', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('notes', 'like', '%' . $filters['q'] . '%');
                });
            })
            ->when($filters['from'] !== '', fn ($query) => $query->whereDate('date_time', '>=', $filters['from']))
            ->when($filters['to'] !== '', fn ($query) => $query->whereDate('date_time', '<=', $filters['to']))
            ->when($filters['van_number'] !== '', fn ($query) => $query->where('van_number', $filters['van_number']))
            ->when($filters['clean'] !== '', fn ($query) => $query->where('clean', $filters['clean']))
            ->when($filters['checklist_type'] !== '', fn ($query) => $query->where('checklist_type', $filters['checklist_type']))
            ->when($filters['trip_status'] !== '', fn ($query) => $query->where('trip_status', $filters['trip_status']));
    }

    private function vanOptions(): array
    {
        return Van::query()
            ->orderByRaw('van_number is null')
            ->orderBy('van_number')
            ->orderBy('name')
            ->get()
            ->map(function (Van $van) {
                $label = $van->displayName();
                if ($van->name && $van->name !== $label) {
                    $label .= ' · ' . $van->name;
                }

                if ($van->status) {
                    $label .= ' · ' . ucfirst((string) $van->status);
                }

                return [
                    'value' => $van->displayName(),
                    'label' => $label,
                ];
            })
            ->unique('value')
            ->values()
            ->all();
    }

    private function resolvePresetRange(string $preset, string $from, string $to): array
    {
        if ($preset === '') {
            return [$from, $to];
        }

        $today = now()->startOfDay();

        return match ($preset) {
            'today' => [$today->toDateString(), $today->toDateString()],
            'this-week' => [$today->copy()->startOfWeek(Carbon::MONDAY)->toDateString(), $today->copy()->endOfWeek(Carbon::SUNDAY)->toDateString()],
            'this-month' => [$today->copy()->startOfMonth()->toDateString(), $today->copy()->endOfMonth()->toDateString()],
            default => [$from, $to],
        };
    }

    private function normalizedFilters(array $filters): array
    {
        [$filters['from'], $filters['to']] = $this->resolvePresetRange(
            $filters['preset'] ?? '',
            $filters['from'] ?? '',
            $filters['to'] ?? ''
        );

        return $filters;
    }
}
