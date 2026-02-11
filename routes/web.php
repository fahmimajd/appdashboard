<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\PendampingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\KinerjaController;
use App\Http\Controllers\KependudukanController;
use App\Http\Controllers\PelayananController;
use App\Http\Controllers\SarprasController;
use App\Http\Controllers\VpnController;
use App\Http\Controllers\SasaranController;
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
    Route::get('/dashboard/pelayanan-detail', [DashboardController::class, 'pelayananDetail'])->name('dashboard.pelayanan-detail');

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
    
    // Pendamping Management (Admin and Supervisor)
    Route::middleware('role:Admin|Supervisor')->prefix('pendamping')->name('pendamping.')->group(function () {
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

    // User Management (Admin only)
    Route::middleware('role:Admin')->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{id}', [UserController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
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
        Route::get('/pending', [KinerjaController::class, 'pending'])->name('pending');
        Route::get('/input', [KinerjaController::class, 'input'])->name('input');
        Route::get('/create', [KinerjaController::class, 'create'])->name('create');
        Route::post('/store', [KinerjaController::class, 'store'])->name('store');
        Route::get('/report', [KinerjaController::class, 'report'])->name('report');
        Route::get('/export', [KinerjaController::class, 'export'])->name('export');
        Route::get('/{id}/edit', [KinerjaController::class, 'edit'])->name('edit');
        Route::put('/{id}', [KinerjaController::class, 'update'])->name('update');
        Route::get('/{id}', [KinerjaController::class, 'show'])->name('show');
        Route::delete('/{id}', [KinerjaController::class, 'destroy'])->name('destroy');
        // Approval routes
        Route::post('/{id}/approve-field', [KinerjaController::class, 'approveField'])->name('approve-field');
        Route::post('/{id}/reject-field', [KinerjaController::class, 'rejectField'])->name('reject-field');
        Route::post('/{id}/approve-all', [KinerjaController::class, 'approveAll'])->name('approve-all');
        Route::post('/{id}/reject-all', [KinerjaController::class, 'rejectAll'])->name('reject-all');
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
        Route::get('/create', [VpnController::class, 'create'])->name('create');
        Route::post('/', [VpnController::class, 'store'])->name('store');
        Route::get('/{id}', [VpnController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [VpnController::class, 'edit'])->name('edit');
        Route::put('/{id}', [VpnController::class, 'update'])->name('update');
        Route::delete('/{id}', [VpnController::class, 'destroy'])->name('destroy');
    });
    
    // Sasaran
    // Belum Rekam
    Route::get('sasaran/rekapitulasi', [\App\Http\Controllers\BelumRekamController::class, 'rekapitulasi'])->name('sasaran.rekapitulasi');
    Route::get('belum-rekam/export', [\App\Http\Controllers\BelumRekamController::class, 'export'])->name('belum_rekam.export');
    Route::get('belum-rekam/pending', [\App\Http\Controllers\BelumRekamController::class, 'pending'])->name('belum_rekam.pending');
    Route::post('belum-rekam/{nik}/approve-field', [\App\Http\Controllers\BelumRekamController::class, 'approveField'])->name('belum_rekam.approve-field');
    Route::post('belum-rekam/{nik}/reject-field', [\App\Http\Controllers\BelumRekamController::class, 'rejectField'])->name('belum_rekam.reject-field');
    Route::post('belum-rekam/{nik}/approve-all', [\App\Http\Controllers\BelumRekamController::class, 'approveAll'])->name('belum_rekam.approve-all');
    Route::post('belum-rekam/{nik}/reject-all', [\App\Http\Controllers\BelumRekamController::class, 'rejectAll'])->name('belum_rekam.reject-all');
    Route::resource('belum-rekam', \App\Http\Controllers\BelumRekamController::class)
        ->only(['index', 'edit', 'update'])
        ->names('belum_rekam');


    // Belum Akte
    Route::get('belum-akte/export', [\App\Http\Controllers\BelumAkteController::class, 'export'])->name('belum_akte.export');
    Route::get('belum-akte/pending', [\App\Http\Controllers\BelumAkteController::class, 'pending'])->name('belum_akte.pending');
    Route::post('belum-akte/{nik}/approve-field', [\App\Http\Controllers\BelumAkteController::class, 'approveField'])->name('belum_akte.approve-field');
    Route::post('belum-akte/{nik}/reject-field', [\App\Http\Controllers\BelumAkteController::class, 'rejectField'])->name('belum_akte.reject-field');
    Route::post('belum-akte/{nik}/approve-all', [\App\Http\Controllers\BelumAkteController::class, 'approveAll'])->name('belum_akte.approve-all');
    Route::post('belum-akte/{nik}/reject-all', [\App\Http\Controllers\BelumAkteController::class, 'rejectAll'])->name('belum_akte.reject-all');
    Route::post('belum-akte/{nik}/upload-dokumen', [\App\Http\Controllers\BelumAkteController::class, 'uploadDokumen'])->name('belum_akte.upload-dokumen');
    Route::get('belum-akte/{nik}/download-dokumen', [\App\Http\Controllers\BelumAkteController::class, 'downloadDokumen'])->name('belum_akte.download-dokumen');
    Route::delete('belum-akte/{nik}/delete-dokumen', [\App\Http\Controllers\BelumAkteController::class, 'deleteDokumen'])->name('belum_akte.delete-dokumen');
    Route::resource('belum-akte', \App\Http\Controllers\BelumAkteController::class)
        ->only(['index', 'edit', 'update'])
        ->names('belum_akte');

    // API endpoints for AJAX requests
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/kecamatan/{kodeKabupaten}', [WilayahController::class, 'apiKecamatan'])->name('kecamatan');
        Route::get('/desa/{kodeKecamatan}', [WilayahController::class, 'apiDesa'])->name('desa');
        Route::get('/stats/dashboard', [DashboardController::class, 'apiStats'])->name('stats.dashboard');
        Route::get('/kinerja/chart/{year}', [KinerjaController::class, 'apiChartData'])->name('kinerja.chart');
    });
});
