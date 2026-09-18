<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─────────────────────────────────────────────────────────────────────────────
// Petani Maju — Content Pipeline Schedule
//
// Jadwal otomatis:
//   07:00 WIB (setiap hari) → Command jalan, tapi di DALAM command ada
//                              interval gate 2 hari. Jadi efektifnya
//                              artikel baru hanya diambil setiap 2 hari.
//                              Kalau belum waktunya, command langsung skip.
//
//   Kenapa cron tetap setiap hari, bukan tiap 2 hari?
//   Karena cron "every 2 days" tidak bisa dikontrol tanggal mulainya.
//   Lebih aman biarkan cron jalan tiap hari, gatenya di dalam command.
//
//   23:00 WIB (Minggu) → Cleanup konten spam/tidak relevan.
//
// CARA AKTIFKAN DI SERVER — jalankan sekali saja:
//   crontab -e
//   Tambah baris:
//   * * * * * cd /path/ke/project && php artisan schedule:run >> /dev/null 2>&1
//
// CEK JADWAL:
//   php artisan schedule:list
//
// TEST MANUAL (paksa jalan sekarang):
//   php artisan agri:scrape-sync --force
//
// SCRAPE 1 URL SPESIFIK:
//   php artisan agri:scrape-sync https://hortiindonesia.com/berita/nama-artikel
//
// LIHAT LOG REAL-TIME:
//   tail -f storage/logs/laravel.log
// ─────────────────────────────────────────────────────────────────────────────

Schedule::command('agri:scrape-sync')
    ->dailyAt('07:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping(15)    // Lock maksimal 15 menit, cegah proses duplikat
    ->runInBackground()
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Schedule: agri:scrape-sync selesai sukses.');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Schedule: agri:scrape-sync GAGAL — cek storage/logs/laravel.log');
    });

Schedule::command('agri:cleanup')
    ->weeklyOn(0, '23:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();