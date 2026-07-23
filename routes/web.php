<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MonitoringDokumenController;
use App\Http\Controllers\SdmBezettingController;
use App\Http\Controllers\ComingSoonController;
use App\Http\Controllers\MonitoringCutiController;
use App\Http\Controllers\MonitoringEvkinController;


// Halaman login hanya boleh diakses kalau BELUM login.
// Kalau user yang udah login coba buka /login lagi, otomatis di-redirect (middleware 'guest').
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');

    // throttle:5,1 = maksimal 5 percobaan login per menit per kombinasi email+IP.
    // Ini proteksi bawaan Laravel buat brute-force attack, tanpa perlu setup tambahan.
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:5,1');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->name('logout')
    ->middleware('auth');

// Semua halaman di bawah ini WAJIB login dulu. Kalau belum, otomatis
// di-redirect ke /login, dan setelah berhasil login akan balik lagi
// ke halaman yang tadinya mau diakses (redirect()->intended() di controller).
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/components-preview', function () {
        return view('components-preview');
    })->name('components.preview');

    //monitoring dokumen controller
    Route::get('/monitoring-str-sip', [MonitoringDokumenController::class, 'index'])
        ->name('monitoring-str-sip.index');

    //bezetting sdm 
    Route::get('/sdm-bezetting', [SdmBezettingController::class, 'index'])
        ->name('sdm-bezetting.index');

    // Sementara: buat ngecek error API SI KAWAN tanpa perlu akses SSH server.
    // Hapus kalau udah gak dibutuhin.
    Route::get('/sdm-bezetting/diagnostic', [SdmBezettingController::class, 'diagnostic'])
        ->name('sdm-bezetting.diagnostic');

    //Monitoring cuti
    Route::get('/monitoring-cuti', [MonitoringCutiController::class, 'index'])
        ->name('monitoring-cuti.index');

    //Monitoring Capaian Kinerja 
    Route::get('/monitoring-evkin', [MonitoringEvkinController::class, 'index'])
        ->name('monitoring-evkin.index');

    //detail per card
    Route::get('/segera-hadir/{module}', [ComingSoonController::class, 'show'])->name('coming-soon');
});