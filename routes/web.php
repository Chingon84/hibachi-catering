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
    Route::post('/admin/clients', [ClientController::class, 'store'])->middleware('perm:clients.manage')->name('admin.clients.store');
    Route::get('/admin/clients/{id}/edit', [ClientController::class, 'edit'])->middleware('perm:clients.manage')->name('admin.clients.edit');
    Route::post('/admin/clients/{id}', [ClientController::class, 'update'])->middleware('perm:clients.manage')->name('admin.clients.update');
    Route::post('/admin/clients/{id}/delete', [ClientController::class, 'destroy'])->middleware('perm:clients.manage')->name('admin.clients.delete');
    Route::post('/admin/clients/{id}/status', [ClientController::class, 'updateStatus'])->middleware('perm:clients.manage')->name('admin.clients.status');
    Route::get('/admin/clients-export', [ClientController::class, 'exportCsv'])->middleware('perm:clients.manage')->name('admin.clients.export');
    Route::post('/admin/clients-import', [ClientController::class, 'importCsv'])->middleware('perm:clients.manage')->name('admin.clients.import');
    Route::get('/admin/clients-template', [ClientController::class, 'templateCsv'])->middleware('perm:clients.manage')->name('admin.clients.template');

    // Reports
    Route::get('/admin/reports', [ReportsController::class, 'index'])->middleware('perm:reports.view')->name('admin.reports');
    Route::get('/admin/orders-breakdown', fn() => view('admin.orders_breakdown'))
        ->middleware('perm:orders.view')
        ->name('admin.orders.breakdown');
    Route::get('/admin/orders-breakdown/search', [ReservationAdminController::class, 'searchOrdersBreakdown'])
        ->middleware('perm:orders.view')
        ->name('admin.orders.breakdown.search');
    Route::get('/admin/orders-breakdown/details', [ReservationAdminController::class, 'ordersBreakdownDetails'])
        ->middleware('perm:orders.view')
        ->name('admin.orders.breakdown.details');
    // Event JSON (for popover)
    Route::get('/events/{id}', [CalendarController::class, 'eventJson'])->middleware('perm:calendar.view')->name('events.show.json');

    // Team Management
    Route::get('/admin/team', [TeamController::class, 'index'])->middleware('perm:team.view')->name('admin.team.index');
    Route::get('/admin/team/create', [TeamController::class, 'create'])->middleware('perm:team.manage')->name('admin.team.create');
    Route::post('/admin/team', [TeamController::class, 'store'])->middleware('perm:team.manage')->name('admin.team.store');
    Route::get('/admin/team/{id}/edit', [TeamController::class, 'edit'])->middleware('perm:team.manage')->name('admin.team.edit');
    Route::post('/admin/team/{id}', [TeamController::class, 'update'])->middleware('perm:team.manage')->name('admin.team.update');
    Route::post('/admin/team/{id}/toggle', [TeamController::class, 'toggleAccess'])->middleware('perm:team.manage')->name('admin.team.toggle');
    Route::post('/admin/team/{id}/delete', [TeamController::class, 'destroy'])->middleware('perm:team.manage')->name('admin.team.delete');

    // Permissions Matrix
    Route::get('/admin/team/permissions', [PermissionsController::class, 'index'])->middleware('perm:team.manage')->name('admin.team.permissions');
    Route::post('/admin/team/permissions', [PermissionsController::class, 'update'])->middleware('perm:team.manage')->name('admin.team.permissions.update');

    // Settings & Complains placeholders
    Route::get('/admin/settings', fn() => view('admin.placeholder', ['title'=>'Settings']))->middleware('perm:settings.view');
    Route::get('/admin/complains', fn() => view('admin.placeholder', ['title'=>'Complains']))->middleware('perm:complains.view');

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
// Public signed link to pay invoice balance
Route::get('/invoice/{code}/pay', [PaymentController::class, 'payBalance'])
    ->name('invoice.pay')
    ->middleware('signed');


// Nota: rutas /admin ahora están protegidas arriba con middleware 'admin'.
