<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ObatController;
use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\RiwayatScanController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes — AgriNova Admin Dashboard
|--------------------------------------------------------------------------
*/

/* ── Root redirect ── */
Route::get('/', fn () => redirect()->route('admin.dashboard'));

/* ── Auth routes (guests only) ── */
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.post');
});

/* ── Logout ── */
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

/* ── Protected admin routes ── */
Route::middleware(['web', 'admin.auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('obat', ObatController::class);
    Route::resource('artikel', ArtikelController::class);

    // Hama & Rekomendasi Obat
    Route::get('/hama', [\App\Http\Controllers\HamaController::class, 'index'])->name('hama.index');
    Route::get('/hama/{id}', [\App\Http\Controllers\HamaController::class, 'show'])->name('hama.show');
    Route::post('/hama/{id}/rekomendasi', [\App\Http\Controllers\HamaController::class, 'storeRekomendasi'])->name('hama.rekomendasi.store');
    Route::delete('/hama/{hamaId}/rekomendasi/{id}', [\App\Http\Controllers\HamaController::class, 'destroyRekomendasi'])->name('hama.rekomendasi.destroy');

    // Riwayat Scan AI
    Route::get('/riwayat-scan', [RiwayatScanController::class, 'index'])->name('riwayat-scan.index');

    // Kelola Pengguna & Langganan
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::post('/users/subscriptions/{id}/status', [UserController::class, 'updateStatus'])->name('users.subscription.status');

    // Pengaturan Akun
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');

    // Notifikasi
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
});

