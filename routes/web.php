<?php

use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\FieldController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\PortalAuthController;
use App\Http\Controllers\Backoffice\DashboardController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\PortalAuthenticate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/csrf-token', fn () => response()->json(['token' => csrf_token()]))->name('csrf.token');

Route::get('/', [BookingController::class, 'index'])->name('home');
Route::get('/lapangan', [BookingController::class, 'lapangan'])->name('lapangan.index');
Route::view('/our-story', 'customer.our-story')->name('our-story');
Route::get('/event', [BookingController::class, 'events'])->name('event.index');
Route::get('/events/{banner}', [BookingController::class, 'showBanner'])->name('banners.show');
Route::view('/bantuan', 'Bantuan')->name('bantuan');

Route::get('/booking-jadwal', [BookingController::class, 'create'])->name('booking.create');
Route::get('/api/slots', [BookingController::class, 'getSlots'])->name('api.slots');
Route::get('/api/active-booking', [BookingController::class, 'activeBooking'])->name('api.activeBooking');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::get('/cek-booking', [BookingController::class, 'track'])->name('booking.track');
Route::get('/booking/{booking_code}', [BookingController::class, 'show'])->name('booking.show');
Route::post('/booking/{booking_code}/upload-proof', [BookingController::class, 'uploadProof'])->name('booking.uploadProof');
Route::post('/booking/{booking_code}/cancel', [BookingController::class, 'cancel'])->name('booking.cancel');
Route::get('/booking/{booking_code}/invoice', [BookingController::class, 'invoice'])->name('booking.invoice');
Route::get('/login', [PortalAuthController::class, 'showUserLogin'])->name('login');
Route::post('/login', [PortalAuthController::class, 'loginUser'])->name('login.submit');
Route::post('/logout', [PortalAuthController::class, 'logoutUser'])->name('logout');

Route::middleware([PortalAuthenticate::class . ':web'])->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('home'))->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/login', [PortalAuthController::class, 'showKasirLogin'])->name('login');
    Route::post('/login', [PortalAuthController::class, 'loginKasir'])->name('login.submit');
    Route::post('/logout', [PortalAuthController::class, 'logoutKasir'])->name('logout');

    Route::middleware([PortalAuthenticate::class . ':kasir'])->group(function () {
        Route::get('/', fn () => redirect()->route('kasir.dashboard'))->name('index');
        Route::get('/dashboard', [KasirController::class, 'index'])->name('dashboard');
        Route::get('/laporan-keuangan', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/walk-in', [KasirController::class, 'storeWalkIn'])->name('walkIn.store');
        Route::post('/payments/{payment}/verify', [KasirController::class, 'verifyPayment'])->name('payments.verify');
        Route::post('/payments/{payment}/reject', [KasirController::class, 'rejectPayment'])->name('payments.reject');
        Route::post('/bookings/{booking}/settle', [KasirController::class, 'settleRemaining'])->name('bookings.settle');
        Route::post('/bookings/{booking}/reschedule', [KasirController::class, 'reschedule'])->name('bookings.reschedule');
        Route::post('/bookings/{booking}/cancel', [KasirController::class, 'cancelExpired'])->name('bookings.cancel');
        Route::post('/maintenance-blocks', [KasirController::class, 'storeMaintenanceBlock'])->name('maintenance.store');
        Route::delete('/maintenance-blocks/{block}', [KasirController::class, 'destroyMaintenanceBlock'])->name('maintenance.destroy');
    });
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [PortalAuthController::class, 'showAdminLogin'])->name('login');
    Route::post('/login', [PortalAuthController::class, 'loginAdmin'])->name('login.submit');
    Route::post('/logout', [PortalAuthController::class, 'logoutAdmin'])->name('logout');

    Route::middleware([PortalAuthenticate::class . ':admin'])->group(function () {
        Route::get('/', fn () => redirect()->route('admin.dashboard'))->name('index');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/laporan-keuangan', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports', [ReportController::class, 'index']);

        Route::get('/operasional', [KasirController::class, 'index'])->name('operasional.index');
        Route::post('/operasional/walk-in', [KasirController::class, 'storeWalkIn'])->name('walkIn.store');
        Route::post('/operasional/payments/{payment}/verify', [KasirController::class, 'verifyPayment'])->name('payments.verify');
        Route::post('/operasional/payments/{payment}/reject', [KasirController::class, 'rejectPayment'])->name('payments.reject');
        Route::post('/operasional/bookings/{booking}/settle', [KasirController::class, 'settleRemaining'])->name('bookings.settle');
        Route::post('/operasional/bookings/{booking}/start-match', [KasirController::class, 'startMatch'])->name('bookings.startMatch');
        Route::post('/operasional/bookings/{booking}/reschedule', [KasirController::class, 'reschedule'])->name('bookings.reschedule');
        Route::post('/operasional/bookings/{booking}/cancel', [KasirController::class, 'cancelExpired'])->name('bookings.cancel');
        Route::post('/operasional/maintenance-blocks', [KasirController::class, 'storeMaintenanceBlock'])->name('maintenance.store');
        Route::delete('/operasional/maintenance-blocks/{block}', [KasirController::class, 'destroyMaintenanceBlock'])->name('maintenance.destroy');

        Route::get('/fields', [FieldController::class, 'index'])->name('fields.index');
        Route::post('/fields', [FieldController::class, 'store'])->name('fields.store');
        Route::put('/fields/{field}', [FieldController::class, 'update'])->name('fields.update');
        Route::patch('/fields/{field}/toggle-status', [FieldController::class, 'toggleStatus'])->name('fields.toggleStatus');
        Route::delete('/fields/{field}', [FieldController::class, 'destroy'])->name('fields.destroy');
        Route::post('/qris-config', [FieldController::class, 'updateQris'])->name('qris.update');

        Route::get('/banners', [BannerController::class, 'index'])->name('banners.index');
        Route::post('/banners', [BannerController::class, 'store'])->name('banners.store');
        Route::put('/banners/{banner}', [BannerController::class, 'update'])->name('banners.update');
        Route::patch('/banners/{banner}/toggle', [BannerController::class, 'toggleActive'])->name('banners.toggle');
        Route::delete('/banners/{banner}', [BannerController::class, 'destroy'])->name('banners.destroy');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});

Route::get('/panel/dashboard', function () {
    if (Auth::guard('admin')->check()) {
        return redirect()->route('admin.dashboard');
    }
    if (Auth::guard('kasir')->check()) {
        return redirect()->route('kasir.dashboard');
    }
    return redirect()->route('admin.login');
})->name('backoffice.dashboard');

Route::get('/panel/laporan-keuangan', function () {
    if (Auth::guard('admin')->check()) {
        return redirect()->route('admin.reports.index');
    }
    if (Auth::guard('kasir')->check()) {
        return redirect()->route('kasir.reports.index');
    }
    return redirect()->route('admin.login');
})->name('backoffice.reports.index');

require __DIR__.'/auth.php';
