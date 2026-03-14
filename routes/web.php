<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\Admin\TimeslotController;
use App\Http\Controllers\Admin\ReservationAdminController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\MenuAdminController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\StaffBookingController;
use App\Http\Controllers\Admin\TrashController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\ClientPhotoController;
use App\Http\Controllers\Admin\FeedbackCenterController;
use App\Http\Controllers\Admin\FinancialOverviewController;
use App\Http\Controllers\Admin\InventoryAlertController;
use App\Http\Controllers\Admin\InventoryDashboardController;
use App\Http\Controllers\Admin\InventoryItemController;
use App\Http\Controllers\Admin\InventoryMovementController;
use App\Http\Controllers\Admin\VanInventoryController;

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Público (wizard)
Route::get('/', fn() => redirect()->route('admin.dashboard'));

// Iniciar nueva reservación (limpia estado de sesión)
Route::get('/reservations/new', function(){
    session()->forget('resv');
    return redirect()->route('reservations.step', ['step'=>1]);
})->name('reservations.new');

// Si prefieres paso opcional:
Route::get('/reservations/{step?}', [ReservationController::class, 'show'])
    ->whereNumber('step')
    ->name('reservations.step');

Route::post('/reservations/{step}', [ReservationController::class, 'submit'])
    ->whereNumber('step')
    ->name('reservations.submit');

// Admin (protegido)
Route::middleware(['admin'])->group(function () {
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Timeslots
    Route::get('/admin/timeslots', [TimeslotController::class, 'index'])->middleware('perm:timeslots.view')->name('admin.timeslots');
    Route::post('/admin/timeslots', [TimeslotController::class, 'store'])->middleware('perm:timeslots.manage');
    Route::get('/admin/timeslots/json', [TimeslotController::class, 'json'])->middleware('perm:timeslots.view')->name('admin.timeslots.json');
    Route::get('/admin/timeslots/bookings', [TimeslotController::class, 'bookingsJson'])->middleware('perm:timeslots.view')->name('admin.timeslots.bookings');
    Route::get('/admin/timeslots/month-status', [TimeslotController::class, 'monthStatusJson'])->middleware('perm:timeslots.view')->name('admin.timeslots.month_status');
    Route::post('/admin/timeslots/auto-fill-month', [TimeslotController::class, 'autoFillMonth'])->middleware('perm:timeslots.manage')->name('admin.timeslots.auto_fill_month');
    Route::post('/admin/timeslots/clear-month', [TimeslotController::class, 'clearMonth'])->middleware('perm:timeslots.manage')->name('admin.timeslots.clear_month');
    Route::get('/admin/timeslots/delete/{id}', [TimeslotController::class, 'delete'])->middleware('perm:timeslots.manage')->name('admin.timeslots.delete');
    Route::post('/admin/timeslots/{id}/status', [TimeslotController::class, 'updateStatus'])->middleware('perm:timeslots.manage')->name('admin.timeslots.status');
    Route::post('/admin/timeslots/{id}/update', [TimeslotController::class, 'updateCapacity'])->middleware('perm:timeslots.manage')->name('admin.timeslots.update');
    Route::post('/admin/timeslots/bulk-update', [TimeslotController::class, 'bulkUpdate'])->middleware('perm:timeslots.manage')->name('admin.timeslots.bulk_update');

    // Reservations
    Route::get('/admin/reservations', [ReservationAdminController::class, 'index'])->middleware('perm:reservations.view')->name('admin.reservations');
    Route::get('/admin/reservations/{id}', [ReservationAdminController::class, 'show'])->middleware('perm:reservations.view')->name('admin.reservations.show');
    Route::get('/admin/reservations/{id}/event', [ReservationAdminController::class, 'event'])->middleware('perm:reservations.view')->name('admin.reservations.event');
    Route::post('/admin/reservations/{id}', [ReservationAdminController::class, 'update'])->middleware('perm:reservations.manage')->name('admin.reservations.update');
    Route::post('/admin/reservations/{id}/items/update', [ReservationAdminController::class, 'updateItems'])->middleware('perm:reservations.manage')->name('admin.reservations.items.update');
    Route::post('/admin/reservations/{id}/items/add', [ReservationAdminController::class, 'addItem'])->middleware('perm:reservations.manage')->name('admin.reservations.items.add');
    Route::post('/admin/reservations/{id}/items/{itemId}/delete', [ReservationAdminController::class, 'deleteItem'])->middleware('perm:reservations.manage')->name('admin.reservations.items.delete');
    Route::post('/admin/reservations/{id}/invoice-status', [ReservationAdminController::class, 'updateInvoiceStatus'])->middleware('perm:reservations.manage')->name('admin.reservations.invoice_status');
    Route::post('/admin/reservations/{id}/color', [ReservationAdminController::class, 'updateColor'])->middleware('perm:reservations.manage')->name('admin.reservations.color');
    // Manual payments
    Route::post('/admin/reservations/{id}/payments/manual/save', [ReservationAdminController::class, 'manualPaymentSave'])->middleware('perm:reservations.manage')->name('admin.reservations.payments.manual.save');
    Route::post('/admin/reservations/{id}/payments/manual/delete', [ReservationAdminController::class, 'manualPaymentDelete'])->middleware('perm:reservations.manage')->name('admin.reservations.payments.manual.delete');
    Route::post('/admin/reservations/{id}/delete', [ReservationAdminController::class, 'destroy'])->middleware('perm:reservations.manage')->name('admin.reservations.delete');
    Route::post('/admin/reservations/{id}/add-to-clients', [ReservationAdminController::class, 'addToClients'])->middleware('perm:reservations.manage')->name('admin.reservations.add_to_clients');
    Route::get('/admin/reservations/{id}/invoice', [ReservationAdminController::class, 'invoice'])->middleware('perm:reservations.view')->name('admin.reservations.invoice');

    // Calendar & Staff bookings
    Route::get('/admin/calendar', [CalendarController::class, 'index'])->middleware('perm:calendar.view')->name('admin.calendar');
    Route::get('/admin/staff-bookings', [StaffBookingController::class, 'step1'])->middleware('perm:staff.view')->name('admin.staff_bookings.step1');
    Route::post('/admin/staff-bookings/step1', [StaffBookingController::class, 'submitStep1'])->middleware('perm:staff.manage')->name('admin.staff_bookings.step1.submit');
    Route::get('/admin/staff-bookings/step2', [StaffBookingController::class, 'step2'])->middleware('perm:staff.view')->name('admin.staff_bookings.step2');
    Route::post('/admin/staff-bookings/step2', [StaffBookingController::class, 'submitStep2'])->middleware('perm:staff.manage')->name('admin.staff_bookings.step2.submit');
    Route::get('/admin/staff-bookings/step3', [StaffBookingController::class, 'step3'])->middleware('perm:staff.view')->name('admin.staff_bookings.step3');
    Route::post('/admin/staff-bookings/confirm', [StaffBookingController::class, 'confirm'])->middleware('perm:staff.manage')->name('admin.staff_bookings.confirm');

    // Clients
    Route::get('/admin/clients', [ClientController::class, 'index'])->middleware('perm:clients.view')->name('admin.clients');
    Route::get('/admin/clients/create', [ClientController::class, 'create'])->middleware('perm:clients.manage')->name('admin.clients.create');
    Route::get('/admin/clients/{id}', [ClientController::class, 'show'])->middleware('perm:clients.view')->name('admin.clients.show');
    Route::post('/admin/clients/{id}/notes', [ClientController::class, 'storeNote'])->middleware('perm:clients.manage')->name('admin.clients.notes.store');
    Route::post('/admin/clients/{id}/tasks', [ClientController::class, 'storeTask'])->middleware('perm:clients.manage')->name('admin.clients.tasks.store');
    Route::post('/admin/clients', [ClientController::class, 'store'])->middleware('perm:clients.manage')->name('admin.clients.store');
    Route::get('/admin/clients/{id}/edit', [ClientController::class, 'edit'])->middleware('perm:clients.manage')->name('admin.clients.edit');
    Route::post('/admin/clients/{id}', [ClientController::class, 'update'])->middleware('perm:clients.manage')->name('admin.clients.update');
    Route::post('/admin/clients/{id}/delete', [ClientController::class, 'destroy'])->middleware('perm:clients.manage')->name('admin.clients.delete');
    Route::post('/admin/clients/{id}/status', [ClientController::class, 'updateStatus'])->middleware('perm:clients.manage')->name('admin.clients.status');
    Route::post('/admin/clients/{client}/photos', [ClientPhotoController::class, 'store'])->middleware('perm:clients.manage')->name('admin.clients.photos.store');
    Route::delete('/admin/clients/{client}/photos/{photo}', [ClientPhotoController::class, 'destroy'])->middleware('perm:clients.manage')->name('admin.clients.photos.destroy');
    Route::get('/admin/clients-export', [ClientController::class, 'exportCsv'])->middleware('perm:clients.manage')->name('admin.clients.export');
    Route::post('/admin/clients-import', [ClientController::class, 'importCsv'])->middleware('perm:clients.manage')->name('admin.clients.import');
    Route::get('/admin/clients-template', [ClientController::class, 'templateCsv'])->middleware('perm:clients.manage')->name('admin.clients.template');

    // Reports
    Route::get('/admin/reports', [ReportsController::class, 'index'])->middleware('perm:reports.view')->name('admin.reports');
    Route::get('/admin/reports/financial-overview', [FinancialOverviewController::class, 'index'])->middleware('perm:reports.view')->name('admin.reports.financial');
    Route::get('/admin/reports/financial-overview/expenses/create', [FinancialOverviewController::class, 'create'])->middleware('perm:reports.view')->name('admin.expenses.create');
    Route::post('/admin/reports/financial-overview/expenses', [FinancialOverviewController::class, 'store'])->middleware('perm:reports.view')->name('admin.expenses.store');
    Route::get('/admin/reports/financial-overview/expenses/{id}/edit', [FinancialOverviewController::class, 'edit'])->middleware('perm:reports.view')->name('admin.expenses.edit');
    Route::post('/admin/reports/financial-overview/expenses/{id}', [FinancialOverviewController::class, 'update'])->middleware('perm:reports.view')->name('admin.expenses.update');
    Route::post('/admin/reports/financial-overview/expenses/{id}/delete', [FinancialOverviewController::class, 'destroy'])->middleware('perm:reports.view')->name('admin.expenses.delete');
    Route::get('/admin/orders-breakdown', fn() => view('admin.orders_breakdown'))
        ->middleware('perm:orders.view')
        ->name('admin.orders.breakdown');
    Route::get('/admin/orders-breakdown/search', [ReservationAdminController::class, 'searchOrdersBreakdown'])
        ->middleware('perm:orders.view')
        ->name('admin.orders.breakdown.search');
    Route::get('/admin/orders-breakdown/details', [ReservationAdminController::class, 'ordersBreakdownDetails'])
        ->middleware('perm:orders.view')
        ->name('admin.orders.breakdown.details');
    Route::get('/admin/orders-breakdown/portions', [ReservationAdminController::class, 'getOrderPortions'])
        ->middleware('perm:orders.view')
        ->name('admin.orders.breakdown.portions');
    Route::post('/admin/orders-breakdown/portions', [ReservationAdminController::class, 'saveOrderPortions'])
        ->middleware('perm:orders.manage')
        ->name('admin.orders.breakdown.portions.save');

    // Inventory
    Route::get('/admin/inventory', [InventoryDashboardController::class, 'index'])->middleware('perm:inventory.view')->name('admin.inventory.dashboard');
    Route::get('/admin/inventory/items', [InventoryItemController::class, 'index'])->middleware('perm:inventory.view')->name('admin.inventory.items.index');
    Route::get('/admin/inventory/items/create', [InventoryItemController::class, 'create'])->middleware('perm:inventory.manage')->name('admin.inventory.items.create');
    Route::post('/admin/inventory/items', [InventoryItemController::class, 'store'])->middleware('perm:inventory.manage')->name('admin.inventory.items.store');
    Route::get('/admin/inventory/items/{id}', [InventoryItemController::class, 'show'])->middleware('perm:inventory.view')->name('admin.inventory.items.show');
    Route::get('/admin/inventory/items/{id}/edit', [InventoryItemController::class, 'edit'])->middleware('perm:inventory.manage')->name('admin.inventory.items.edit');
    Route::post('/admin/inventory/items/{id}', [InventoryItemController::class, 'update'])->middleware('perm:inventory.manage')->name('admin.inventory.items.update');
    Route::post('/admin/inventory/items/{id}/delete', [InventoryItemController::class, 'destroy'])->middleware('perm:inventory.manage')->name('admin.inventory.items.delete');

    Route::get('/admin/inventory/vans', [VanInventoryController::class, 'index'])->middleware('perm:inventory.view')->name('admin.inventory.vans.index');
    Route::get('/admin/inventory/vans/create', [VanInventoryController::class, 'createVan'])->middleware('perm:inventory.manage')->name('admin.inventory.vans.create');
    Route::post('/admin/inventory/vans', [VanInventoryController::class, 'storeVan'])->middleware('perm:inventory.manage')->name('admin.inventory.vans.store');
    Route::post('/admin/inventory/vans/loadout', [VanInventoryController::class, 'storeLoadout'])->middleware('perm:inventory.manage')->name('admin.inventory.vans.loadout.store');
    Route::get('/admin/inventory/vans/{id}', [VanInventoryController::class, 'show'])->middleware('perm:inventory.view')->name('admin.inventory.vans.show');
    Route::get('/admin/inventory/vans/{id}/edit', [VanInventoryController::class, 'editVan'])->middleware('perm:inventory.manage')->name('admin.inventory.vans.edit');
    Route::post('/admin/inventory/vans/{id}', [VanInventoryController::class, 'updateVan'])->middleware('perm:inventory.manage')->name('admin.inventory.vans.update');

    Route::get('/admin/inventory/movements', [InventoryMovementController::class, 'index'])->middleware('perm:inventory.view')->name('admin.inventory.movements.index');
    Route::get('/admin/inventory/movements/create', [InventoryMovementController::class, 'create'])->middleware('perm:inventory.manage')->name('admin.inventory.movements.create');
    Route::post('/admin/inventory/movements', [InventoryMovementController::class, 'store'])->middleware('perm:inventory.manage')->name('admin.inventory.movements.store');

    Route::get('/admin/inventory/alerts', [InventoryAlertController::class, 'index'])->middleware('perm:inventory.view')->name('admin.inventory.alerts.index');

    // Event JSON (for popover)
    Route::get('/events/{id}', [CalendarController::class, 'eventJson'])->middleware('perm:calendar.view')->name('events.show.json');

    // Team Management
    Route::get('/admin/team', [TeamController::class, 'index'])->middleware('perm:team.view')->name('admin.team.index');
    Route::get('/admin/team/create', [TeamController::class, 'create'])->middleware('perm:team.manage')->name('admin.team.create');
    Route::post('/admin/team', [TeamController::class, 'store'])->middleware('perm:team.manage')->name('admin.team.store');
    Route::get('/team/{id}', [TeamController::class, 'show'])->middleware('perm:team.view')->name('admin.team.show');
    Route::post('/team/{id}/documents', [TeamController::class, 'storeDocument'])->middleware('perm:team.manage')->name('admin.team.documents.store');
    Route::get('/team/{id}/documents/{documentId}/download', [TeamController::class, 'downloadDocument'])->middleware('perm:team.view')->name('admin.team.documents.download');
    Route::post('/team/{id}/documents/{documentId}/delete', [TeamController::class, 'destroyDocument'])->middleware('perm:team.manage')->name('admin.team.documents.delete');
    Route::get('/admin/team/{id}/edit', [TeamController::class, 'edit'])->middleware('perm:team.manage')->name('admin.team.edit');
    Route::post('/admin/team/{id}', [TeamController::class, 'update'])->middleware('perm:team.manage')->name('admin.team.update');
    Route::post('/admin/team/{id}/toggle', [TeamController::class, 'toggleAccess'])->middleware('perm:team.manage')->name('admin.team.toggle');
    Route::post('/admin/team/{id}/delete', [TeamController::class, 'destroy'])->middleware('perm:team.manage')->name('admin.team.delete');

    // Permissions Matrix
    Route::get('/admin/team/permissions', [PermissionsController::class, 'index'])->middleware('perm:team.manage')->name('admin.team.permissions');
    Route::post('/admin/team/permissions', [PermissionsController::class, 'update'])->middleware('perm:team.manage')->name('admin.team.permissions.update');

    $feedbackCenterPage = function () {
        $allowedTabs = ['all-cases', 'complaints', 'good-feedback', 'van-feedback', 'attendance', 'days-off', 'alerts', 'chef-summary', 'monthly-trends'];
        $activeTab = (string) request('tab', 'all-cases');
        if (!in_array($activeTab, $allowedTabs, true)) {
            $activeTab = 'all-cases';
        }

        $viewMode = request('view', 'cases');
        if (!in_array($viewMode, ['cases', 'analytics'], true)) {
            $viewMode = 'cases';
        }

        $filters = [
            'q' => trim((string) request('q', '')),
            'status' => trim((string) request('status', '')),
            'type' => trim((string) request('type', '')),
            'date' => trim((string) request('date', '')),
            'chef' => trim((string) request('chef', '')),
            'staff_type' => trim((string) request('staff_type', '')),
            'source' => trim((string) request('source', '')),
            'item' => trim((string) request('item', '')),
        ];

        $staffDirectory = \App\Models\User::query()
            ->where('is_active', true)
            ->whereIn('staff_type', \App\Http\Controllers\Admin\TeamController::STAFF_TYPES)
            ->orderBy('name')
            ->get(['name', 'staff_type']);

        $staffTypeLookup = $staffDirectory
            ->pluck('staff_type', 'name')
            ->map(fn ($value) => (string) $value);
        $staffNames = $staffDirectory->pluck('name')->values();
        $staffNameAt = fn (int $index) => $staffNames->get($index, 'Unassigned Staff Member');

        $daysOff = collect([
            ['request_id' => 'DO-1048', 'chef' => $staffNameAt(0), 'staff_type' => $staffTypeLookup->get($staffNameAt(0)), 'start_date' => '2026-03-10', 'end_date' => '2026-03-12', 'status' => 'Approved', 'days' => 3, 'approved_by' => 'Elena Brooks', 'notes' => 'Family travel approved two weeks in advance.', 'unauthorized_days' => 0],
            ['request_id' => 'DO-1042', 'chef' => $staffNameAt(1), 'staff_type' => $staffTypeLookup->get($staffNameAt(1)), 'start_date' => '2026-03-06', 'end_date' => '2026-03-06', 'status' => 'Pending', 'days' => 1, 'approved_by' => 'Pending', 'notes' => 'Awaiting schedule confirmation.', 'unauthorized_days' => 0],
            ['request_id' => 'DO-1039', 'chef' => $staffNameAt(2), 'staff_type' => $staffTypeLookup->get($staffNameAt(2)), 'start_date' => '2026-03-01', 'end_date' => '2026-03-02', 'status' => 'Denied', 'days' => 2, 'approved_by' => 'Maya Chen', 'notes' => 'Request conflicts with peak weekend demand.', 'unauthorized_days' => 1],
            ['request_id' => 'DO-1030', 'chef' => $staffNameAt(3), 'staff_type' => $staffTypeLookup->get($staffNameAt(3)), 'start_date' => '2026-02-18', 'end_date' => '2026-02-18', 'status' => 'Worked', 'days' => 1, 'approved_by' => 'Sofia Nguyen', 'notes' => 'Team member withdrew request and worked assigned event.', 'unauthorized_days' => 0],
            ['request_id' => 'DO-1024', 'chef' => $staffNameAt(1), 'staff_type' => $staffTypeLookup->get($staffNameAt(1)), 'start_date' => '2026-02-08', 'end_date' => '2026-02-10', 'status' => 'Cancelled', 'days' => 3, 'approved_by' => 'Elena Brooks', 'notes' => 'Cancelled after venue date moved.', 'unauthorized_days' => 0],
        ]);

        $goodFeedback = \App\Models\GoodFeedback::query()
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

        $complaints = \App\Models\Complaint::query()
            ->orderByDesc('date_received')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($row) => [
                'complaint_id' => $row->complaint_id,
                'event_date' => optional($row->event_date)->toDateString(),
                'date_received' => optional($row->date_received)->toDateString(),
                'chef' => $row->chef,
                'staff_type' => $staffTypeLookup->get($row->chef),
                'category' => $row->category,
                'description' => $row->description,
                'resolution_status' => $row->resolution_status,
                'assistant' => $row->assistant,
                'action_taken' => $row->action_taken,
                'priority' => in_array($row->resolution_status, ['Escalated', 'Open'], true) ? 'High' : 'Medium',
                'escalated' => $row->resolution_status === 'Escalated',
                'history' => [['title' => 'Complaint logged', 'note' => 'Created through Feedback Center workflow.']],
            ]);

        $vanFeedback = \App\Models\VanFeedback::query()
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

        $attendance = \App\Models\AttendanceIncident::query()
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

        $filterItems = function ($items, array $config) use ($filters) {
            return $items->filter(function ($item) use ($filters, $config) {
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
                    $chefKey = $config['chef_key'] ?? 'chef';
                    if ((string) ($item[$chefKey] ?? '') !== $filters['chef']) {
                        return false;
                    }
                }

                if ($filters['staff_type'] !== '') {
                    $staffTypeKey = $config['staff_type_key'] ?? 'staff_type';
                    if ((string) ($item[$staffTypeKey] ?? '') !== $filters['staff_type']) {
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

                if ($filters['q'] !== '') {
                    $searchKeys = $config['search_keys'] ?? array_keys($item);
                    $haystack = collect($searchKeys)
                        ->map(function ($key) use ($item) {
                            $value = $item[$key] ?? '';
                            if (is_bool($value)) {
                                return $value ? 'yes' : 'no';
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
            'chef_key' => 'chef',
            'staff_type_key' => 'staff_type',
            'date_keys' => ['start_date', 'end_date'],
            'search_keys' => ['request_id', 'chef', 'approved_by', 'notes'],
        ]);

        $goodFeedbackFiltered = $filterItems($goodFeedback, [
            'record_type' => 'Good Feedback',
            'status_key' => 'status',
            'chef_key' => 'chef',
            'staff_type_key' => 'staff_type',
            'source_key' => 'source',
            'date_keys' => ['event_date', 'date_received'],
            'search_keys' => ['feedback_id', 'chef', 'source', 'compliment', 'assistant'],
        ]);

        $complaintsFiltered = $filterItems($complaints, [
            'record_type' => 'Complaint',
            'status_key' => 'resolution_status',
            'chef_key' => 'chef',
            'staff_type_key' => 'staff_type',
            'date_keys' => ['event_date', 'date_received'],
            'search_keys' => ['complaint_id', 'chef', 'category', 'description', 'assistant', 'action_taken'],
        ]);

        $vanFeedbackFiltered = $filterItems($vanFeedback, [
            'record_type' => 'Van Feedback',
            'status_key' => 'status',
            'chef_key' => 'chef',
            'staff_type_key' => 'staff_type',
            'date_keys' => ['event_date', 'date_received'],
            'search_keys' => ['vanfb_id', 'chef', 'van', 'description', 'action_taken'],
        ]);

        $attendanceFiltered = $filterItems($attendance, [
            'record_type' => 'Attendance',
            'status_key' => 'status',
            'chef_key' => 'chef',
            'staff_type_key' => 'staff_type',
            'date_keys' => ['date'],
            'search_keys' => ['incident_id', 'chef', 'incident_type', 'manager', 'notes'],
        ]);

        $alerts = collect([
            ['alert_id' => 'AL-91', 'type' => 'Unauthorized time off', 'chef' => $staffNameAt(2), 'staff_type' => $staffTypeLookup->get($staffNameAt(2)), 'date' => '2026-03-02', 'details' => 'One denied day was still taken without final approval.', 'severity' => 'High', 'status' => 'Open'],
            ['alert_id' => 'AL-88', 'type' => 'Escalated complaint', 'chef' => $staffNameAt(1), 'staff_type' => $staffTypeLookup->get($staffNameAt(1)), 'date' => '2026-02-25', 'details' => 'Venue access setup issue escalated with refund risk attached.', 'severity' => 'Urgent', 'status' => 'Escalated'],
            ['alert_id' => 'AL-82', 'type' => 'Attendance pattern', 'chef' => $staffNameAt(0), 'staff_type' => $staffTypeLookup->get($staffNameAt(0)), 'date' => '2026-02-21', 'details' => 'Missed training created two unexcused units in the last 30 days.', 'severity' => 'Medium', 'status' => 'In Review'],
        ]);

        $alertsFiltered = $filterItems($alerts, [
            'record_type' => 'Alert',
            'status_key' => 'status',
            'chef_key' => 'chef',
            'staff_type_key' => 'staff_type',
            'date_keys' => ['date'],
            'search_keys' => ['alert_id', 'type', 'chef', 'details', 'severity'],
        ]);

        $allCases = $complaintsFiltered->map(function ($item) {
            return [
                'id' => $item['complaint_id'],
                'type' => 'Complaint',
                'date' => $item['date_received'],
                'event_date' => $item['event_date'],
                'chef' => $item['chef'],
                'staff_type' => $item['staff_type'],
                'source' => $item['category'],
                'summary' => $item['description'],
                'status' => $item['resolution_status'],
                'owner' => $item['assistant'],
                'priority' => $item['priority'],
                'action_taken' => $item['action_taken'],
                'history' => $item['history'],
            ];
        })->merge($goodFeedbackFiltered->map(function ($item) {
            return [
                'id' => $item['feedback_id'],
                'type' => 'Good Feedback',
                'date' => $item['date_received'],
                'event_date' => $item['event_date'],
                'chef' => $item['chef'],
                'staff_type' => $item['staff_type'],
                'source' => $item['source'],
                'summary' => $item['compliment'],
                'status' => $item['status'],
                'owner' => $item['assistant'],
                'priority' => 'Positive',
                'action_taken' => 'Share with chef leadership and add to recognition notes.',
                'history' => $item['history'],
            ];
        }))->merge($vanFeedbackFiltered->map(function ($item) {
            return [
                'id' => $item['vanfb_id'],
                'type' => 'Van Feedback',
                'date' => $item['date_received'],
                'event_date' => $item['event_date'],
                'chef' => $item['chef'],
                'staff_type' => $item['staff_type'],
                'source' => $item['van'],
                'summary' => $item['description'],
                'status' => $item['status'],
                'owner' => 'Fleet Ops',
                'priority' => 'Operational',
                'action_taken' => $item['action_taken'],
                'history' => $item['history'],
            ];
        }))->merge($attendanceFiltered->map(function ($item) {
            return [
                'id' => $item['incident_id'],
                'type' => 'Attendance',
                'date' => $item['date'],
                'event_date' => $item['date'],
                'chef' => $item['chef'],
                'staff_type' => $item['staff_type'],
                'source' => $item['incident_type'],
                'summary' => $item['notes'],
                'status' => $item['status'],
                'owner' => $item['manager'],
                'priority' => $item['authorized'] ? 'Reviewed' : 'Flagged',
                'action_taken' => $item['authorized'] ? 'Manager authorization documented.' : 'Requires follow-up and incident review.',
                'history' => $item['history'],
            ];
        }))->sortByDesc('date')->values();

        $directoryChefs = $staffDirectory->pluck('name')->unique()->values();
        $staffTypeOptions = $staffDirectory->pluck('staff_type')->filter()->unique()->values();

        $allChefs = $directoryChefs;

        $chefSummaries = $allChefs->map(function ($chef) use ($daysOff, $goodFeedback, $complaints, $vanFeedback, $attendance) {
            $chefDaysOff = $daysOff->where('chef', $chef);
            $chefAttendance = $attendance->where('chef', $chef);
            $goodCount = $goodFeedback->where('chef', $chef)->count();
            $complaintCount = $complaints->where('chef', $chef)->count();
            $vanCount = $vanFeedback->where('chef', $chef)->count();
            $requests = $chefDaysOff->count();
            $approvedDays = $chefDaysOff->where('status', 'Approved')->sum('days');
            $deniedDays = $chefDaysOff->where('status', 'Denied')->sum('days');
            $pendingDays = $chefDaysOff->where('status', 'Pending')->sum('days');
            $cancelledWorkedDays = $chefDaysOff->filter(fn ($row) => in_array($row['status'], ['Cancelled', 'Worked'], true))->sum('days');
            $unauthorizedDays = $chefDaysOff->sum('unauthorized_days');
            $attendanceIncidents = $chefAttendance->count();
            $unexcused = $chefAttendance->where('authorized', false);

            return [
                'chef' => $chef,
                'requests' => $requests,
                'total_days' => $chefDaysOff->sum('days'),
                'approved_days' => $approvedDays,
                'denied_days' => $deniedDays,
                'pending_days' => $pendingDays,
                'cancelled_worked_days' => $cancelledWorkedDays,
                'unauthorized_days' => $unauthorizedDays,
                'good_feedback' => $goodCount,
                'complaints' => $complaintCount,
                'van_issues' => $vanCount,
                'net_score' => $goodCount - $complaintCount,
                'attendance_incidents' => $attendanceIncidents,
                'unexcused_incidents' => $unexcused->count(),
                'unexcused_units' => $unexcused->sum('units'),
            ];
        })->values();

        if ($filters['chef'] !== '') {
            $chefSummaries = $chefSummaries->where('chef', $filters['chef'])->values();
        }
        if ($filters['q'] !== '') {
            $chefSummaries = $chefSummaries->filter(fn ($row) => stripos($row['chef'], $filters['q']) !== false)->values();
        }

        $months = $daysOff->map(fn ($row) => substr($row['start_date'], 0, 7))
            ->merge($goodFeedback->map(fn ($row) => substr($row['date_received'], 0, 7)))
            ->merge($complaints->map(fn ($row) => substr($row['date_received'], 0, 7)))
            ->merge($attendance->map(fn ($row) => substr($row['date'], 0, 7)))
            ->unique()
            ->sortDesc()
            ->values();

        $monthlyTrends = $months->map(function ($month) use ($daysOff, $goodFeedback, $complaints, $attendance) {
            return [
                'month' => $month,
                'label' => \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y'),
                'days_off' => $daysOff->filter(fn ($row) => str_starts_with($row['start_date'], $month))->sum('days'),
                'unauthorized_days' => $daysOff->filter(fn ($row) => str_starts_with($row['start_date'], $month))->sum('unauthorized_days'),
                'good_feedback' => $goodFeedback->filter(fn ($row) => str_starts_with($row['date_received'], $month))->count(),
                'complaints' => $complaints->filter(fn ($row) => str_starts_with($row['date_received'], $month))->count(),
                'attendance_incidents' => $attendance->filter(fn ($row) => str_starts_with($row['date'], $month))->count(),
            ];
        })->values();

        if ($filters['date'] !== '') {
            $monthPrefix = substr($filters['date'], 0, 7);
            $monthlyTrends = $monthlyTrends->filter(fn ($row) => $row['month'] === $monthPrefix)->values();
        }

        $teamMemberAnalytics = $allCases
            ->groupBy('chef')
            ->map(fn ($rows, $chef) => [
                'label' => $chef,
                'value' => $rows->count(),
            ])
            ->sortByDesc('value')
            ->take(10)
            ->values();

        $casesByMonth = $allCases
            ->groupBy(fn ($row) => substr($row['date'], 0, 7))
            ->map(function ($rows, $month) {
                return [
                    'month' => $month,
                    'label' => \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                    'value' => $rows->count(),
                ];
            })
            ->sortBy('month')
            ->values();

        $typeDistribution = collect([
            ['label' => 'Complaints', 'value' => $complaintsFiltered->count()],
            ['label' => 'Good Feedback', 'value' => $goodFeedbackFiltered->count()],
            ['label' => 'Van Issues', 'value' => $vanFeedbackFiltered->count()],
            ['label' => 'Attendance', 'value' => $attendanceFiltered->count()],
            ['label' => 'Alerts', 'value' => $alertsFiltered->count()],
        ]);

        $staffTypeBreakdown = $allCases
            ->filter(fn ($row) => !empty($row['staff_type']))
            ->groupBy('staff_type')
            ->map(fn ($rows, $staffType) => [
                'label' => $staffType,
                'value' => $rows->count(),
            ])
            ->sortByDesc('value')
            ->values();

        $netScoreTrend = $months
            ->sort()
            ->values()
            ->map(function ($month) use ($goodFeedbackFiltered, $complaintsFiltered) {
                $goodCount = $goodFeedbackFiltered->filter(fn ($row) => str_starts_with($row['date_received'], $month))->count();
                $complaintCount = $complaintsFiltered->filter(fn ($row) => str_starts_with($row['date_received'], $month))->count();

                return [
                    'month' => $month,
                    'label' => \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                    'value' => $goodCount - $complaintCount,
                ];
            });

        $analytics = [
            'cases_by_team_member' => [
                'labels' => $teamMemberAnalytics->pluck('label')->all(),
                'values' => $teamMemberAnalytics->pluck('value')->all(),
            ],
            'monthly_trend' => [
                'labels' => $casesByMonth->pluck('label')->all(),
                'values' => $casesByMonth->pluck('value')->all(),
            ],
            'type_distribution' => [
                'labels' => $typeDistribution->pluck('label')->all(),
                'values' => $typeDistribution->pluck('value')->all(),
            ],
            'staff_type_breakdown' => [
                'labels' => $staffTypeBreakdown->pluck('label')->all(),
                'values' => $staffTypeBreakdown->pluck('value')->all(),
            ],
            'net_score_trend' => [
                'labels' => $netScoreTrend->pluck('label')->all(),
                'values' => $netScoreTrend->pluck('value')->all(),
            ],
            'highlights' => [
                'top_team_member' => $teamMemberAnalytics->sortByDesc('value')->first(),
                'peak_month' => $casesByMonth->sortByDesc('value')->first(),
                'net_score' => $netScoreTrend->pluck('value')->last() ?? 0,
            ],
        ];

        $statusOptions = collect([
            $complaints->pluck('resolution_status'),
            $goodFeedback->pluck('status'),
            $vanFeedback->pluck('status'),
            $attendance->pluck('status'),
            $daysOff->pluck('status'),
            $alerts->pluck('status'),
        ])->flatten()->unique()->values();

        $sourceOptions = $goodFeedback->pluck('source')->unique()->values();
        $typeOptions = collect(['Complaint', 'Good Feedback', 'Van Feedback', 'Attendance', 'Days Off', 'Alert'])->values();

        $stats = [
            ['label' => 'Open Complaints', 'value' => $complaints->whereIn('resolution_status', ['Open', 'In Review', 'Escalated'])->count(), 'tone' => 'open', 'note' => 'Requires active follow-up', 'trend' => '+1 this week', 'trend_direction' => 'up', 'spark' => [2, 2, 3, 2, 3, 3]],
            ['label' => 'Good Feedback', 'value' => $goodFeedback->count(), 'tone' => 'positive', 'note' => 'Positive service recognition', 'trend' => '+2 this month', 'trend_direction' => 'up', 'spark' => [1, 1, 2, 2, 3, 3]],
            ['label' => 'Van Issues', 'value' => $vanFeedback->count(), 'tone' => 'review', 'note' => 'Fleet and equipment concerns', 'trend' => 'Flat vs last week', 'trend_direction' => 'flat', 'spark' => [1, 1, 1, 2, 2, 2]],
            ['label' => 'Attendance Incidents', 'value' => $attendance->count(), 'tone' => 'neutral', 'note' => 'Manager-reviewed incidents', 'trend' => '+1 flagged shift', 'trend_direction' => 'up', 'spark' => [0, 1, 1, 1, 2, 3]],
            ['label' => 'Alerts', 'value' => $alerts->count(), 'tone' => 'escalated', 'note' => 'Unauthorized or urgent patterns', 'trend' => '+1 new escalation', 'trend_direction' => 'up', 'spark' => [0, 1, 1, 2, 2, 3]],
            ['label' => 'Resolved Cases', 'value' => $complaints->where('resolution_status', 'Resolved')->count() + $vanFeedback->where('status', 'Resolved')->count(), 'tone' => 'resolved', 'note' => 'Closed with action recorded', 'trend' => '-1 vs last week', 'trend_direction' => 'down', 'spark' => [3, 3, 2, 2, 2, 1]],
        ];

        $preview = ['tag' => 'No item selected', 'status' => '', 'title' => 'No record selected', 'subtitle' => 'Choose a record to inspect operational context.', 'facts' => [], 'sections' => [], 'history' => []];
        $timelineize = function (array $history, ?string $anchorDate = null) {
            return collect($history)->values()->map(function ($item, $index) use ($anchorDate) {
                $baseDate = $item['date'] ?? $anchorDate;
                $dateLabel = null;
                if ($baseDate) {
                    try {
                        $dateLabel = \Carbon\Carbon::parse($baseDate)->addDays($index)->format('M d, Y');
                    } catch (\Throwable $e) {
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

        if ($activeTab === 'complaints') {
            $selected = $complaintsFiltered->firstWhere('complaint_id', $filters['item']) ?: $complaintsFiltered->first();
            if ($selected) {
                $preview = [
                    'tag' => 'Complaint preview',
                    'status' => $selected['resolution_status'],
                    'title' => $selected['category'],
                    'subtitle' => $selected['chef'] . ' • Complaint ' . $selected['complaint_id'],
                    'facts' => ['Event Date' => $selected['event_date'], 'Date Received' => $selected['date_received'], 'Staff Type' => $selected['staff_type'] ?: 'Not classified', 'Assistant' => $selected['assistant'], 'Priority' => $selected['priority']],
                    'sections' => [
                        ['label' => 'Description', 'value' => $selected['description']],
                        ['label' => 'Action Taken', 'value' => $selected['action_taken']],
                    ],
                    'history' => $timelineize($selected['history'], $selected['date_received']),
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
                    'sections' => [['label' => 'Compliment', 'value' => $selected['compliment']]],
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
                    'sections' => [['label' => 'Notes', 'value' => $selected['notes']], ['label' => 'Operational Impact', 'value' => $selected['authorized'] ? 'Documented and authorized by manager.' : 'Requires follow-up due to unexcused attendance impact.']],
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
                    'subtitle' => 'Request spanning ' . $selected['start_date'] . ' to ' . $selected['end_date'],
                    'facts' => ['Days' => $selected['days'], 'Approved By' => $selected['approved_by'], 'Unauthorized Days' => $selected['unauthorized_days']],
                    'sections' => [['label' => 'Notes', 'value' => $selected['notes']], ['label' => 'Operational Guidance', 'value' => $selected['unauthorized_days'] > 0 ? 'Triggers alert review and chef summary exception count.' : 'No unauthorized time attached to this request.']],
                    'history' => $timelineize([['title' => 'Current status', 'note' => $selected['status'] . ' request tracked in days-off log.']], $selected['start_date']),
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
                    'sections' => [['label' => 'Details', 'value' => $selected['details']], ['label' => 'Escalation Path', 'value' => 'Route urgent patterns to operations leadership and retain in chef trend history.']],
                    'history' => $timelineize([['title' => 'Alert generated', 'note' => 'Derived from workbook-style unauthorized and escalation patterns.']], $selected['date']),
                ];
            }
        } elseif ($activeTab === 'chef-summary') {
            $selected = $chefSummaries->firstWhere('chef', $filters['item']) ?: $chefSummaries->first();
            if ($selected) {
                $preview = [
                    'tag' => 'Chef summary',
                    'status' => $selected['net_score'] >= 0 ? 'Healthy' : 'Watch',
                    'title' => $selected['chef'],
                    'subtitle' => 'Workbook-style operational rollup across requests, feedback, and incidents.',
                    'facts' => ['Requests' => $selected['requests'], 'Net Score' => $selected['net_score'], 'Unexcused Units' => $selected['unexcused_units']],
                    'sections' => [['label' => 'Performance Read', 'value' => 'Good feedback: ' . $selected['good_feedback'] . ' | Complaints: ' . $selected['complaints'] . ' | Van issues: ' . $selected['van_issues']], ['label' => 'Risk Flags', 'value' => $selected['unauthorized_days'] . ' unauthorized days and ' . $selected['unexcused_incidents'] . ' unexcused attendance incidents.']],
                    'history' => $timelineize([['title' => 'Summary generated', 'note' => 'Rollup includes days-off, feedback, complaints, van feedback, and attendance logs.']], $filters['date'] ?: now()->toDateString()),
                ];
            }
        } elseif ($activeTab === 'monthly-trends') {
            $selected = $monthlyTrends->firstWhere('month', $filters['item']) ?: $monthlyTrends->first();
            if ($selected) {
                $preview = [
                    'tag' => 'Monthly trend',
                    'status' => 'Trend',
                    'title' => $selected['label'],
                    'subtitle' => 'Monthly workbook rollup for time-off, feedback, and complaint activity.',
                    'facts' => ['Days Off' => $selected['days_off'], 'Unauthorized Days' => $selected['unauthorized_days'], 'Complaints' => $selected['complaints']],
                    'sections' => [['label' => 'Trend Summary', 'value' => 'Good feedback: ' . $selected['good_feedback'] . ' | Attendance incidents: ' . $selected['attendance_incidents']], ['label' => 'Operational Use', 'value' => 'Use this section to spot month-over-month risk and recognition patterns.']],
                    'history' => $timelineize([['title' => 'Trend snapshot', 'note' => 'Built for future charts and leadership reporting.']], $selected['month'] . '-01'),
                ];
            }
        } else {
            $selected = $allCases->firstWhere('id', $filters['item']) ?: $allCases->first();
            if ($selected) {
                $preview = [
                    'tag' => 'Queue preview',
                    'status' => $selected['status'],
                    'title' => $selected['summary'],
                    'subtitle' => $selected['type'] . ' • ' . $selected['chef'] . ' • ' . $selected['id'],
                    'facts' => ['Received' => $selected['date'], 'Event Date' => $selected['event_date'], 'Staff Type' => $selected['staff_type'] ?: 'Not classified', 'Owner' => $selected['owner'], 'Source' => $selected['source']],
                    'sections' => [['label' => 'Action Taken', 'value' => $selected['action_taken']], ['label' => 'Operational Context', 'value' => 'Unified queue combines complaints, positive feedback, van issues, and attendance incidents.']],
                    'history' => $timelineize($selected['history'], $selected['date']),
                ];
            }
        }

        return view('admin.feedback_center', [
            'viewMode' => $viewMode,
            'activeTab' => $activeTab,
            'filters' => $filters,
            'stats' => $stats,
            'statusOptions' => $statusOptions,
            'typeOptions' => $typeOptions,
            'chefOptions' => $allChefs,
            'staffTypeOptions' => $staffTypeOptions,
            'sourceOptions' => $sourceOptions,
            'allCases' => $allCases,
            'complaints' => $complaintsFiltered,
            'goodFeedback' => $goodFeedbackFiltered,
            'vanFeedback' => $vanFeedbackFiltered,
            'attendance' => $attendanceFiltered,
            'daysOff' => $daysOffFiltered,
            'alerts' => $alertsFiltered,
            'chefSummaries' => $chefSummaries,
            'monthlyTrends' => $monthlyTrends,
            'analytics' => $analytics,
            'preview' => $preview,
        ]);
    };

    // Settings & Feedback Center
    Route::get('/admin/settings', fn() => view('admin.placeholder', ['title'=>'Settings']))->middleware('perm:settings.view');
    Route::get('/admin/feedback-center/create', [FeedbackCenterController::class, 'create'])->middleware('perm:complains.view')->name('admin.feedback.create');
    Route::post('/admin/feedback-center/create', [FeedbackCenterController::class, 'store'])->middleware('perm:complains.view')->name('admin.feedback.create.submit');
    Route::get('/admin/feedback-center', $feedbackCenterPage)->middleware('perm:complains.view')->name('admin.feedback');
    Route::get('/admin/complains', $feedbackCenterPage)->middleware('perm:complains.view');

    // Trash
    Route::get('/admin/trash', [TrashController::class, 'index'])->middleware('perm:trash.view')->name('admin.trash');
    Route::post('/admin/trash/{id}/restore', [TrashController::class, 'restore'])->middleware('perm:trash.manage')->name('admin.trash.restore');
    Route::post('/admin/trash/{id}/delete', [TrashController::class, 'forceDelete'])->middleware('perm:trash.manage')->name('admin.trash.force');

    // Menu editor
    Route::get('/admin/menu', [MenuAdminController::class, 'index'])->middleware('perm:menu.view')->name('admin.menu');
    Route::post('/admin/menu', [MenuAdminController::class, 'update'])->middleware('perm:menu.manage')->name('admin.menu.update');
});


// API
Route::get('/api/availability', [ReservationController::class, 'availability'])->name('api.availability');
Route::get('/api/geocode', [ReservationController::class, 'geocode']); // seguimos usando ZIP

// Payments (Stripe Checkout)
Route::post('/payments/checkout', [PaymentController::class, 'checkout'])->name('payments.checkout');
Route::get('/payments/success', [PaymentController::class, 'success'])->name('payments.success');
Route::get('/payments/cancel', [PaymentController::class, 'cancel'])->name('payments.cancel');

// Nota: rutas /admin ahora están protegidas arriba con middleware 'admin'.
