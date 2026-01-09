<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\PendampingController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\KinerjaController;
use App\Http\Controllers\KependudukanController;
use App\Http\Controllers\PelayananController;
use App\Http\Controllers\SarprasController;
use App\Http\Controllers\VpnController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Force Password Change
    Route::get('/change-password', [\App\Http\Controllers\Auth\ChangePasswordController::class, 'show'])->name('password.change');
    Route::post('/change-password', [\App\Http\Controllers\Auth\ChangePasswordController::class, 'update'])->name('password.update');
    
    // Wilayah Management
    Route::prefix('wilayah')->name('wilayah.')->group(function () {
        Route::get('/', [WilayahController::class, 'index'])->name('index');
        Route::get('/kabupaten', [WilayahController::class, 'kabupaten'])->name('kabupaten');
        Route::get('/kecamatan/{kodeKabupaten?}', [WilayahController::class, 'kecamatan'])->name('kecamatan');
        Route::get('/desa/{kodeKecamatan?}', [WilayahController::class, 'desa'])->name('desa');
        Route::get('/desa/{kodeDesa}/detail', [WilayahController::class, 'show'])->name('desa.detail');
    });
    
    // Pendamping Management (Admin only)
    Route::middleware('role:Admin')->prefix('pendamping')->name('pendamping.')->group(function () {
        Route::get('/', [PendampingController::class, 'index'])->name('index');
        Route::get('/create', [PendampingController::class, 'create'])->name('create');
        Route::post('/', [PendampingController::class, 'store'])->name('store');
        Route::get('/{nik}', [PendampingController::class, 'show'])->name('show');
        Route::get('/{nik}/edit', [PendampingController::class, 'edit'])->name('edit');
        Route::put('/{nik}', [PendampingController::class, 'update'])->name('update');
        Route::delete('/{nik}', [PendampingController::class, 'destroy'])->name('destroy');
        Route::post('/{nik}/toggle-status', [PendampingController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{nik}/reset-password', [PendampingController::class, 'resetPassword'])->name('reset-password');
    });
    
    // Petugas Management
    Route::prefix('petugas')->name('petugas.')->group(function () {
        Route::get('/', [PetugasController::class, 'index'])->name('index');
        Route::get('/create', [PetugasController::class, 'create'])->name('create');
        Route::post('/', [PetugasController::class, 'store'])->name('store');
        Route::get('/{nik}', [PetugasController::class, 'show'])->name('show');
        Route::get('/{nik}/edit', [PetugasController::class, 'edit'])->name('edit');
        Route::put('/{nik}', [PetugasController::class, 'update'])->name('update');
        Route::delete('/{nik}', [PetugasController::class, 'destroy'])->name('destroy');
        Route::post('/{nik}/toggle-status', [PetugasController::class, 'toggleStatus'])->name('toggle-status');
    });
    
    // Kinerja Petugas
    Route::prefix('kinerja')->name('kinerja.')->group(function () {
        Route::get('/', [KinerjaController::class, 'index'])->name('index');
        Route::get('/input', [KinerjaController::class, 'input'])->name('input');
        Route::get('/create', [KinerjaController::class, 'create'])->name('create');
        Route::post('/store', [KinerjaController::class, 'store'])->name('store');
        Route::get('/report', [KinerjaController::class, 'report'])->name('report');
        Route::get('/export', [KinerjaController::class, 'export'])->name('export');
        Route::get('/{id}/edit', [KinerjaController::class, 'edit'])->name('edit');
        Route::put('/{id}', [KinerjaController::class, 'update'])->name('update');
        Route::get('/{id}', [KinerjaController::class, 'show'])->name('show');
        Route::delete('/{id}', [KinerjaController::class, 'destroy'])->name('destroy');
    });
    
    // Kependudukan
    // Kependudukan
    Route::prefix('kependudukan')->name('kependudukan.')->group(function () {
        Route::get('/', [KependudukanController::class, 'index'])->name('index');
        Route::get('/create', [KependudukanController::class, 'create'])->name('create');
        Route::post('/store', [KependudukanController::class, 'store'])->name('store');
        Route::get('/statistics', [KependudukanController::class, 'statistics'])->name('statistics');
        Route::get('/export', [KependudukanController::class, 'export'])->name('export');
        Route::get('/{id}', [KependudukanController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [KependudukanController::class, 'edit'])->name('edit');
        Route::put('/{id}', [KependudukanController::class, 'update'])->name('update');
        Route::delete('/{id}', [KependudukanController::class, 'destroy'])->name('destroy');
    });
    
    // Pelayanan
    Route::prefix('pelayanan')->name('pelayanan.')->group(function () {
        Route::get('/', [PelayananController::class, 'index'])->name('index');
        Route::get('/create', [PelayananController::class, 'create'])->name('create');
        Route::post('/', [PelayananController::class, 'store'])->name('store');
        Route::get('/{id}', [PelayananController::class, 'show'])->name('show');
    });
    
    // Sarpras Desa
    Route::prefix('sarpras')->name('sarpras.')->group(function () {
        Route::get('/', [SarprasController::class, 'index'])->name('index');
        Route::get('/{kodeDesa}/edit', [SarprasController::class, 'edit'])->name('edit');
        Route::put('/{kodeDesa}', [SarprasController::class, 'update'])->name('update');
    });
    
    // VPN Desa
    Route::prefix('vpn')->name('vpn.')->group(function () {
        Route::get('/', [VpnController::class, 'index'])->name('index');
        Route::get('/{kodeDesa}/edit', [VpnController::class, 'edit'])->name('edit');
        Route::put('/{kodeDesa}', [VpnController::class, 'update'])->name('update');
    });
    
    // API endpoints for AJAX requests
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/kecamatan/{kodeKabupaten}', [WilayahController::class, 'apiKecamatan'])->name('kecamatan');
        Route::get('/desa/{kodeKecamatan}', [WilayahController::class, 'apiDesa'])->name('desa');
        Route::get('/stats/dashboard', [DashboardController::class, 'apiStats'])->name('stats.dashboard');
        Route::get('/kinerja/chart/{year}', [KinerjaController::class, 'apiChartData'])->name('kinerja.chart');
    });
});
