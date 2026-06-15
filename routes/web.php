<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\InvoiceStatusController;
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
use App\Http\Controllers\Admin\InvoiceAdminController;
use App\Http\Controllers\Admin\InventoryAlertController;
use App\Http\Controllers\Admin\InventoryDashboardController;
use App\Http\Controllers\Admin\InventoryItemController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\VanChecklistController;
use App\Http\Controllers\Admin\InventoryMovementController;
use App\Http\Controllers\Admin\VanInventoryController;
use App\Http\Controllers\Admin\OnlineUsersController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\StaffDashboardController;

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');

Route::get('/presence/ping', fn () => response()->noContent())
    ->middleware(['auth', 'presence'])
    ->name('presence.ping');

Route::get('/invoice/status/{token}', [InvoiceStatusController::class, 'show'])
    ->middleware('throttle:30,1')
    ->name('invoice.status.public');

Route::middleware(['auth', 'presence'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
    Route::get('/events/{reservation}', [StaffDashboardController::class, 'show'])->name('events.show');
    Route::post('/events/{reservation}/confirm', [StaffDashboardController::class, 'confirm'])->name('events.confirm');
});

Route::middleware(['admin', 'presence'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

// Público (wizard)
Route::get('/', function () {
    if (auth()->check() && auth()->user()?->isStaffPortalUser()) {
        return redirect()->route('staff.dashboard');
    }

    return redirect()->route('admin.dashboard');
});

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
Route::middleware(['admin', 'presence'])->group(function () {
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/online-users', [OnlineUsersController::class, 'index'])->name('admin.online-users.index');

    // Timeslots
    Route::get('/admin/timeslots', [TimeslotController::class, 'index'])->middleware('perm:timeslots.view')->name('admin.timeslots');
    Route::post('/admin/timeslots', [TimeslotController::class, 'store'])->middleware('perm:timeslots.manage');
    Route::get('/admin/timeslots/json', [TimeslotController::class, 'json'])->middleware('perm:timeslots.view')->name('admin.timeslots.json');
    Route::get('/admin/timeslots/bookings', [TimeslotController::class, 'bookingsJson'])->middleware('perm:timeslots.view')->name('admin.timeslots.bookings');
    Route::get('/admin/timeslots/month-status', [TimeslotController::class, 'monthStatusJson'])->middleware('perm:timeslots.view')->name('admin.timeslots.month_status');
    Route::post('/admin/timeslots/auto-fill-month', [TimeslotController::class, 'autoFillMonth'])->middleware('perm:timeslots.manage')->name('admin.timeslots.auto_fill_month');
    Route::post('/admin/timeslots/clear-month', [TimeslotController::class, 'clearMonth'])->middleware('perm:timeslots.manage')->name('admin.timeslots.clear_month');
    Route::post('/admin/timeslots/{id}/delete', [TimeslotController::class, 'delete'])->middleware('perm:timeslots.manage')->name('admin.timeslots.delete');
    Route::post('/admin/timeslots/{id}/status', [TimeslotController::class, 'updateStatus'])->middleware('perm:timeslots.manage')->name('admin.timeslots.status');
    Route::post('/admin/timeslots/{id}/update', [TimeslotController::class, 'updateCapacity'])->middleware('perm:timeslots.manage')->name('admin.timeslots.update');
    Route::post('/admin/timeslots/bulk-update', [TimeslotController::class, 'bulkUpdate'])->middleware('perm:timeslots.manage')->name('admin.timeslots.bulk_update');

    // Reservations
    Route::get('/admin/reservations', [ReservationAdminController::class, 'index'])->middleware('perm:reservations.view')->name('admin.reservations');
    Route::get('/admin/invoices', [InvoiceAdminController::class, 'index'])->middleware('perm:reservations.view')->name('admin.invoices');
    Route::get('/admin/invoices/create', [InvoiceAdminController::class, 'create'])->middleware('perm:reservations.manage')->name('admin.invoices.create');
    Route::post('/admin/invoices', [InvoiceAdminController::class, 'store'])->middleware('perm:reservations.manage')->name('admin.invoices.store');
    Route::get('/admin/invoices/{invoice}', [InvoiceAdminController::class, 'show'])->middleware('perm:reservations.view')->name('admin.invoices.show');
    Route::get('/admin/invoices/{invoice}/download', [InvoiceAdminController::class, 'download'])->middleware('perm:reservations.view')->name('admin.invoices.download');
    Route::get('/admin/invoices/{invoice}/edit', [InvoiceAdminController::class, 'edit'])->middleware('perm:reservations.manage')->name('admin.invoices.edit');
    Route::post('/admin/invoices/{invoice}', [InvoiceAdminController::class, 'update'])->middleware('perm:reservations.manage')->name('admin.invoices.update');
    Route::get('/admin/invoices/{invoice}/review', [InvoiceAdminController::class, 'review'])->middleware('perm:reservations.view')->name('admin.invoices.review');
    Route::post('/admin/invoices/{invoice}/finalize', [InvoiceAdminController::class, 'finalize'])->middleware('perm:reservations.manage')->name('admin.invoices.finalize');
    Route::post('/admin/invoices/{invoice}/void', [InvoiceAdminController::class, 'void'])->middleware('perm:reservations.manage')->name('admin.invoices.void');
    Route::post('/admin/invoices/{invoice}/delete', [InvoiceAdminController::class, 'destroy'])->middleware('perm:reservations.manage')->name('admin.invoices.destroy');
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
    Route::get('/admin/reservations/{id}/prep-print', [ReservationAdminController::class, 'prepPrint'])->middleware('perm:reservations.view')->name('admin.reservations.prep_print');

    // Calendar & Staff bookings
    Route::get('/admin/calendar', [CalendarController::class, 'index'])->middleware('perm:calendar.view')->name('admin.calendar');
    Route::get('/admin/schedule', [ScheduleController::class, 'index'])->middleware('perm:schedule.view')->name('admin.schedule.index');
    Route::get('/admin/schedule/assign', [ScheduleController::class, 'assign'])->middleware('perm:schedule.view')->name('admin.schedule.assign');
    Route::post('/admin/schedule/assign', [ScheduleController::class, 'storeAssignment'])->middleware('perm:schedule.manage')->name('admin.schedule.assign.store');
    Route::get('/admin/schedule/staff-counts', [ScheduleController::class, 'staffCounts'])->middleware('perm:schedule.view')->name('admin.schedule.staff_counts');
    Route::post('/admin/schedule/{reservation}/assignment', [ScheduleController::class, 'updateAssignment'])->middleware('perm:schedule.manage')->name('admin.schedule.assignment.update');
    Route::get('/admin/schedule/rules', [ScheduleController::class, 'rules'])->middleware('perm:schedule.view')->name('admin.schedule.rules');
    Route::get('/admin/schedule/chefs/{user}/score', [ScheduleController::class, 'editScore'])->middleware('perm:schedule.manage')->name('admin.schedule.score.edit');
    Route::post('/admin/schedule/chefs/{user}/score', [ScheduleController::class, 'updateScore'])->middleware('perm:schedule.manage')->name('admin.schedule.score.update');
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
    Route::get('/admin/reports/financial-overview', [FinancialOverviewController::class, 'index'])->middleware('perm:financial.view')->name('admin.reports.financial');
    Route::get('/admin/reports/financial-overview/expenses/create', [FinancialOverviewController::class, 'create'])->middleware('perm:financial.manage')->name('admin.expenses.create');
    Route::post('/admin/reports/financial-overview/expenses', [FinancialOverviewController::class, 'store'])->middleware('perm:financial.manage')->name('admin.expenses.store');
    Route::get('/admin/reports/financial-overview/expenses/{id}/edit', [FinancialOverviewController::class, 'edit'])->middleware('perm:financial.manage')->name('admin.expenses.edit');
    Route::post('/admin/reports/financial-overview/expenses/{id}', [FinancialOverviewController::class, 'update'])->middleware('perm:financial.manage')->name('admin.expenses.update');
    Route::post('/admin/reports/financial-overview/expenses/{id}/delete', [FinancialOverviewController::class, 'destroy'])->middleware('perm:financial.manage')->name('admin.expenses.delete');
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
    Route::get('/admin/inventory/checklists', [VanChecklistController::class, 'index'])->middleware('perm:inventory.view')->name('admin.inventory.checklists.index');
    Route::get('/admin/inventory/checklists/export/{format}', [VanChecklistController::class, 'export'])->middleware('perm:inventory.view')->name('admin.inventory.checklists.export');
    Route::get('/admin/inventory/checklists/create', [VanChecklistController::class, 'create'])->middleware('perm:inventory.manage')->name('admin.inventory.checklists.create');
    Route::post('/admin/inventory/checklists', [VanChecklistController::class, 'store'])->middleware('perm:inventory.manage')->name('admin.inventory.checklists.store');
    Route::get('/admin/inventory/checklists/{id}', [VanChecklistController::class, 'show'])->middleware('perm:inventory.view')->name('admin.inventory.checklists.show');
    Route::get('/admin/inventory/checklists/{id}/edit', [VanChecklistController::class, 'edit'])->middleware('perm:inventory.manage')->name('admin.inventory.checklists.edit');
    Route::post('/admin/inventory/checklists/{id}', [VanChecklistController::class, 'update'])->middleware('perm:inventory.manage')->name('admin.inventory.checklists.update');
    Route::post('/admin/inventory/checklists/{id}/delete', [VanChecklistController::class, 'destroy'])->middleware('perm:inventory.manage')->name('admin.inventory.checklists.delete');

    // Event JSON (for popover)
    Route::get('/events/{id}', [CalendarController::class, 'eventJson'])->middleware('perm:calendar.view')->name('events.show.json');

    // Team Management
    Route::get('/admin/team', [TeamController::class, 'index'])->middleware('perm:team.view')->name('admin.team.index');
    Route::get('/admin/team/create', [TeamController::class, 'create'])->middleware('perm:team.manage')->name('admin.team.create');
    Route::post('/admin/team', [TeamController::class, 'store'])->middleware('perm:team.manage')->name('admin.team.store');
    // Access Control
    Route::get('/admin/team/permissions', [PermissionsController::class, 'index'])->middleware('perm:team.manage')->name('admin.team.permissions');
    Route::post('/admin/team/permissions', [PermissionsController::class, 'update'])->middleware('perm:team.manage')->name('admin.team.permissions.update');
    Route::get('/team/{id}', [TeamController::class, 'show'])->middleware('perm:team.view')->name('admin.team.show');
    Route::post('/team/{id}/profile-photo', [TeamController::class, 'updateProfilePhoto'])->middleware('perm:team.manage')->name('admin.team.profile-photo.update');
    Route::post('/team/{id}/profile-photo/delete', [TeamController::class, 'destroyProfilePhoto'])->middleware('perm:team.manage')->name('admin.team.profile-photo.delete');
    Route::post('/team/{id}/documents', [TeamController::class, 'storeDocument'])->middleware('perm:team.manage')->name('admin.team.documents.store');
    Route::get('/team/{id}/documents/{documentId}/view', [TeamController::class, 'viewDocument'])->middleware('perm:team.view')->name('admin.team.documents.view');
    Route::get('/team/{id}/documents/{documentId}/download', [TeamController::class, 'downloadDocument'])->middleware('perm:team.view')->name('admin.team.documents.download');
    Route::post('/team/{id}/documents/{documentId}/delete', [TeamController::class, 'destroyDocument'])->middleware('perm:team.manage')->name('admin.team.documents.delete');
    Route::get('/admin/team/{id}/edit', [TeamController::class, 'edit'])->middleware('perm:team.manage')->name('admin.team.edit');
    Route::post('/admin/team/{id}', [TeamController::class, 'update'])->middleware('perm:team.manage')->name('admin.team.update');
    Route::post('/admin/team/{id}/toggle', [TeamController::class, 'toggleAccess'])->middleware('perm:team.manage')->name('admin.team.toggle');
    Route::post('/admin/team/{id}/delete', [TeamController::class, 'destroy'])->middleware('perm:team.manage')->name('admin.team.delete');

    // Settings & Feedback Center
    Route::get('/admin/settings', [SettingsController::class, 'index'])->middleware('perm:settings.view')->name('admin.settings');
    Route::get('/admin/settings/business-profile', [SettingsController::class, 'businessProfile'])->middleware('perm:settings.view')->name('admin.settings.business-profile');
    Route::post('/admin/settings/business-profile', [SettingsController::class, 'updateBusinessProfile'])->middleware('perm:settings.view')->name('admin.settings.business-profile.update');
    Route::get('/admin/settings/reservation-rules', [SettingsController::class, 'reservationRules'])->middleware('perm:settings.view')->name('admin.settings.reservation-rules');
    Route::post('/admin/settings/reservation-rules', [SettingsController::class, 'updateReservationRules'])->middleware('perm:settings.view')->name('admin.settings.reservation-rules.update');
    Route::post('/admin/settings/reservation-rules/reset', [SettingsController::class, 'resetReservationRules'])->middleware('perm:settings.view')->name('admin.settings.reservation-rules.reset');
    Route::get('/admin/settings/menu-pricing-rules', [SettingsController::class, 'menuPricingRules'])->middleware('perm:settings.view')->name('admin.settings.menu-pricing-rules');
    Route::post('/admin/settings/menu-pricing-rules', [SettingsController::class, 'updateMenuPricingRules'])->middleware('perm:menu.manage')->name('admin.settings.menu-pricing-rules.update');
    Route::post('/admin/settings/menu-pricing-rules/reset', [SettingsController::class, 'resetMenuPricingRules'])->middleware('perm:menu.manage')->name('admin.settings.menu-pricing-rules.reset');
    Route::post('/admin/settings/menu-pricing-rules/preview', [SettingsController::class, 'previewMenuPricingBulkUpdate'])->middleware('perm:menu.manage')->name('admin.settings.menu-pricing-rules.preview');
    Route::post('/admin/settings/menu-pricing-rules/apply', [SettingsController::class, 'applyMenuPricingBulkUpdate'])->middleware('perm:menu.manage')->name('admin.settings.menu-pricing-rules.apply');
    Route::get('/admin/settings/custom-tax-rates', [SettingsController::class, 'customTaxRates'])->middleware('perm:settings.view')->name('admin.settings.custom-tax-rates.index');
    Route::post('/admin/settings/custom-tax-rates', [SettingsController::class, 'storeCustomTaxRates'])->middleware('perm:settings.view')->name('admin.settings.custom-tax-rates.store');
    Route::put('/admin/settings/custom-tax-rates/{customTaxRate}', [SettingsController::class, 'updateCustomTaxRate'])->middleware('perm:settings.view')->name('admin.settings.custom-tax-rates.update');
    Route::delete('/admin/settings/custom-tax-rates/{customTaxRate}', [SettingsController::class, 'destroyCustomTaxRate'])->middleware('perm:settings.view')->name('admin.settings.custom-tax-rates.destroy');
    Route::get('/admin/settings/{section}', [SettingsController::class, 'show'])->middleware('perm:settings.view')->name('admin.settings.section');
    Route::get('/admin/feedback-center/create', [FeedbackCenterController::class, 'create'])->middleware('perm:feedback.manage')->name('admin.feedback.create');
    Route::post('/admin/feedback-center/create', [FeedbackCenterController::class, 'store'])->middleware('perm:feedback.manage')->name('admin.feedback.create.submit');
    Route::get('/admin/feedback-center', [FeedbackCenterController::class, 'index'])->middleware('perm:feedback.view')->name('admin.feedback');
    Route::get('/admin/complains', [FeedbackCenterController::class, 'index'])->middleware('perm:feedback.view');
    Route::post('/admin/feedback-center/workflow', [FeedbackCenterController::class, 'updateWorkflow'])->middleware('perm:feedback.manage')->name('admin.feedback.workflow.update');

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
