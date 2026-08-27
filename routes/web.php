<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ObatController;
use App\Http\Controllers\ArtikelController;

/*
|--------------------------------------------------------------------------
| Web Routes — Petani Maju Admin Dashboard
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
});

