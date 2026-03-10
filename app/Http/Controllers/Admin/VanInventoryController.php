<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Van;
use App\Models\VanLoadout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VanInventoryController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => trim((string) $request->query('status', '')),
        ];

        $vans = Van::query()
            ->with(['currentLoadout.checkedBy:id,name', 'currentLoadout.loadedBy:id,name', 'currentLoadout.reservation:id,customer_name,date,code'])
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($nested) use ($filters) {
                    $nested->where('name', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('code', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('license_plate', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('van_number', 'like', '%' . $filters['q'] . '%');
                });
            })
            ->when($filters['status'] !== '', fn ($query) => $query->where('status', $filters['status']))
            ->orderByRaw('van_number is null')
            ->orderBy('van_number')
            ->orderBy('name')
            ->get();

        return view('admin.inventory.vans.index', [
            'vans' => $vans,
            'filters' => $filters,
            'statuses' => Van::STATUSES,
            'vanNumbers' => range(1, 20),
            'teamMembers' => \App\Models\User::query()->active()->orderBy('name')->get(['id', 'name']),
            'reservationOptions' => Reservation::query()
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->limit(30)
                ->get(['id', 'customer_name', 'date', 'code']),
        ]);
    }

    public function createVan()
    {
        return view('admin.inventory.vans.form', [
            'van' => new Van(),
            'statuses' => Van::STATUSES,
        ]);
    }

    public function storeVan(Request $request): RedirectResponse
    {
        $data = $this->validatedVan($request);
        $data['name'] = 'Van ' . $data['van_number'];
        $van = Van::create($data);

        return redirect()->route('admin.inventory.vans.index')->with('ok', $van->displayName() . ' created successfully.');
    }

    public function show($id)
    {
        $van = Van::with([
            'currentLoadout.checkedBy:id,name',
            'currentLoadout.loadedBy:id,name',
            'currentLoadout.reservation:id,customer_name,date,code',
        ])->findOrFail($id);
        $loadout = $van->currentLoadout ?: new VanLoadout([
            'van_status' => 'neutral',
            'grills' => [],
            'tables_count' => 0,
            'chairs_count' => 0,
            'propane_tanks_count' => 0,
            'dolly_count' => 0,
            'straps_count' => 0,
            'floor_mats_count' => 0,
            'trash_cans_count' => 0,
            'heaters_count' => 0,
            'buffet_warmers_count' => 0,
        ]);
        $history = $van->loadouts()
            ->with(['checkedBy:id,name', 'loadedBy:id,name', 'reservation:id,customer_name,date,code'])
            ->latest('checked_at')
            ->latest('id')
            ->paginate(12);

        return view('admin.inventory.vans.show', [
            'van' => $van,
            'loadout' => $loadout,
            'history' => $history,
            'grillOptions' => range(1, 30),
            'teamMembers' => \App\Models\User::query()->active()->orderBy('name')->get(['id', 'name']),
            'reservationOptions' => Reservation::query()
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->limit(30)
                ->get(['id', 'customer_name', 'date', 'code']),
        ]);
    }

    public function editVan($id)
    {
        return view('admin.inventory.vans.form', [
            'van' => Van::findOrFail($id),
            'statuses' => Van::STATUSES,
        ]);
    }

    public function updateVan(Request $request, $id): RedirectResponse
    {
        $van = Van::findOrFail($id);
        $data = $this->validatedVan($request, $van);
        $data['name'] = 'Van ' . $data['van_number'];
        $van->update($data);

        return redirect()->route('admin.inventory.vans.index')->with('ok', $van->displayName() . ' updated successfully.');
    }

    public function storeLoadout(Request $request): RedirectResponse
    {
        $validated = $this->validatedLoadout($request);
        $vanNumber = (int) $validated['van_number'];

        $van = Van::query()
            ->where('van_number', $vanNumber)
            ->orWhere('name', 'Van ' . $vanNumber)
            ->first();

        if (!$van) {
            $van = Van::create([
                'van_number' => $vanNumber,
                'name' => 'Van ' . $vanNumber,
                'status' => 'active',
            ]);
        } elseif ((int) $van->van_number !== $vanNumber || $van->name !== 'Van ' . $vanNumber) {
            $van->update([
                'van_number' => $vanNumber,
                'name' => 'Van ' . $vanNumber,
            ]);
        }

        VanLoadout::create($validated + [
            'van_id' => $van->id,
            'grills' => collect($validated['grills'] ?? [])->map(fn ($grill) => (int) $grill)->sort()->values()->all(),
            'checked_at' => $validated['checked_at'] ?? now(),
        ]);

        return redirect()->route('admin.inventory.vans.index')->with('ok', $van->displayName() . ' loadout saved successfully.');
    }

    private function validatedVan(Request $request, ?Van $van = null): array
    {
        return $request->validate([
            'van_number' => ['required', 'integer', 'between:1,20', Rule::unique('vans', 'van_number')->ignore($van?->id)],
            'code' => ['nullable', 'string', 'max:100', Rule::unique('vans', 'code')->ignore($van?->id)],
            'license_plate' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(Van::STATUSES)],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    private function validatedLoadout(Request $request): array
    {
        return $request->validate([
            'van_number' => ['required', 'integer', 'between:1,20'],
            'van_status' => ['required', Rule::in(VanLoadout::VAN_STATUSES)],
            'grills' => ['nullable', 'array'],
            'grills.*' => ['integer', 'distinct', 'between:1,30'],
            'tables_count' => ['required', 'integer', 'min:0'],
            'chairs_count' => ['required', 'integer', 'min:0'],
            'propane_tanks_count' => ['required', 'integer', 'min:0'],
            'dolly_count' => ['required', 'integer', 'min:0'],
            'straps_count' => ['required', 'integer', 'min:0'],
            'floor_mats_count' => ['required', 'integer', 'min:0'],
            'trash_cans_count' => ['required', 'integer', 'min:0'],
            'heaters_count' => ['required', 'integer', 'min:0'],
            'buffet_warmers_count' => ['required', 'integer', 'min:0'],
            'loaded_by_user_id' => ['nullable', 'exists:users,id'],
            'checked_by_user_id' => ['nullable', 'exists:users,id'],
            'checked_at' => ['nullable', 'date'],
            'reservation_id' => ['nullable', 'exists:reservations,id'],
            'event_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ], [
            'grills.*.between' => 'Grill selections must be between Grill #1 and Grill #30.',
        ]);
    }
}
