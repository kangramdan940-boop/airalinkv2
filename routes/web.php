<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TransferFormController;
use App\Http\Controllers\TunaiFormController;
use App\Http\Controllers\KalkulatorController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\InvestorController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\UangCashController;
use App\Http\Controllers\UangCashMonitoringController;
use App\Http\Controllers\RekeningValidController;
use App\Http\Controllers\Master\PegawaiController;
use App\Http\Controllers\Transaksi\TransferController;
use App\Http\Controllers\Transaksi\TunaiController;
use App\Http\Controllers\Konfigurasi\UsersController;
use App\Http\Controllers\Konfigurasi\RolesController;
use App\Http\Controllers\ClosingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to home
Route::get('/', fn() => redirect('/home'));

// Authentication
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// Dashboard (permission check inside controller)
Route::get('/home', [HomeController::class, 'index'])->name('home');

// Authenticated Routes
Route::middleware('auth')->group(function () {

    // Transfer form (user) - permission: transfer-view
    Route::middleware('can:transfer-view')->group(function () {
        Route::get('/transfer', [TransferFormController::class, 'index']);
        Route::post('/transfer', [TransferFormController::class, 'store']);
    });

    // Tunai form (user) - permission: tunai-view
    Route::middleware('can:tunai-view')->group(function () {
        Route::get('/tunai', [TunaiFormController::class, 'index']);
        Route::post('/tunai', [TunaiFormController::class, 'store']);
    });

    // Kalkulator - permission: kalkulator-view
    Route::get('/kalkulator', [KalkulatorController::class, 'index'])->middleware('can:kalkulator-view');
    Route::get('/kalkulator/chart-data', [KalkulatorController::class, 'chartData'])->middleware('can:kalkulator-view');

    // Pengeluaran Bulanan - permission: pengeluaran-view
    Route::middleware('can:pengeluaran-view')->group(function () {
        Route::get('/pengeluaran', [PengeluaranController::class, 'index']);
        Route::post('/pengeluaran', [PengeluaranController::class, 'store']);
        Route::put('/pengeluaran/{id}', [PengeluaranController::class, 'update']);
        Route::delete('/pengeluaran/{id}', [PengeluaranController::class, 'destroy']);
        Route::post('/pengeluaran/hitung-admin', [PengeluaranController::class, 'hitungAdmin']);
        Route::post('/pengeluaran/get-banks', [PengeluaranController::class, 'getBanks']);
    });

    // Investor - permission: investor-view
    Route::middleware('can:investor-view')->group(function () {
        Route::get('/investor', [InvestorController::class, 'index']);
        Route::post('/investor', [InvestorController::class, 'store']);
        Route::put('/investor/{id}', [InvestorController::class, 'update']);
        Route::delete('/investor/{id}', [InvestorController::class, 'destroy']);
    });

    // Laporan PDF - permission: investor-view (admin only)
    Route::middleware('can:investor-view')->group(function () {
        Route::get('/laporan', [LaporanController::class, 'index']);
        Route::post('/laporan/generate', [LaporanController::class, 'generate']);
    });

    // Uang Cash - permission: uang-cash-view
    Route::middleware('can:uang-cash-view')->group(function () {
        Route::post('uang-cash/grid', [UangCashController::class, 'grid']);
        Route::get('/uang-cash', [UangCashController::class, 'index']);
        Route::post('/uang-cash', [UangCashController::class, 'store']);
        Route::delete('/uang-cash/{id}', [UangCashController::class, 'destroy']);
    });

    // Uang Cash Monitoring - permission: uang-cash-monitoring-view
    Route::middleware('can:uang-cash-monitoring-view')->group(function () {
        Route::post('uang-cash-monitoring/grid', [UangCashMonitoringController::class, 'grid']);
        Route::get('/uang-cash-monitoring', [UangCashMonitoringController::class, 'index']);
        Route::post('/uang-cash-monitoring', [UangCashMonitoringController::class, 'store']);
        Route::put('/uang-cash-monitoring/{id}', [UangCashMonitoringController::class, 'update']);
        Route::delete('/uang-cash-monitoring/{id}', [UangCashMonitoringController::class, 'destroy']);
    });

    // Closing Brilink
    Route::get('/closing', [ClosingController::class, 'index']);
    Route::get('/closing/api/accounts', [ClosingController::class, 'apiAccounts']);
    Route::get('/closing/api/shops', [ClosingController::class, 'apiShops']);
    Route::get('/closing/api/by-date/{date}', [ClosingController::class, 'apiByDate']);
    Route::post('/closing/api/closings', [ClosingController::class, 'apiStore']);
    Route::get('/closing/api/closings', [ClosingController::class, 'apiList']);
    Route::get('/closing/api/closings/{id}', [ClosingController::class, 'apiShow']);

    // Transaksi Bank - permission per module
    Route::prefix('transaksi')->group(function () {
        Route::middleware('can:transaksi-transfer-view')->group(function () {
            Route::post('transfer/grid', [TransferController::class, 'grid']);
            Route::resource('transfer', TransferController::class);
        });

        Route::middleware('can:transaksi-tarik-tunai-view')->group(function () {
            Route::post('tarik-tunai/grid', [TunaiController::class, 'grid']);
            Route::resource('tarik-tunai', TunaiController::class);
        });
    });

    // Rekening Valid (admin CRUD + lookup API)
    Route::get('/rekening-valid/lookup', [RekeningValidController::class, 'lookup']);
    Route::get('/rekening-valid/lookup-search', [RekeningValidController::class, 'lookupSearch']);
    Route::middleware('can:master-pegawai-view')->group(function () {
        Route::post('rekening-valid/grid', [RekeningValidController::class, 'grid']);
        Route::resource('rekening-valid', RekeningValidController::class)->except(['show', 'create']);
    });

    // Master - permission: master-pegawai-view
    Route::prefix('master')->middleware('can:master-pegawai-view')->group(function () {
        Route::post('pegawai/grid', [PegawaiController::class, 'grid']);
        Route::resource('pegawai', PegawaiController::class);
    });

    // Konfigurasi
    Route::prefix('konfigurasi')->group(function () {
        Route::middleware('can:konfigurasi-users-view')->group(function () {
            Route::post('users/grid', [UsersController::class, 'grid']);
            Route::put('users/{id}/reset-password', [UsersController::class, 'resetPassword']);
            Route::resource('users', UsersController::class);
        });

        Route::middleware('can:konfigurasi-roles-view')->group(function () {
            Route::put('roles/{id}/grant', [RolesController::class, 'grant']);
            Route::post('roles/grid', [RolesController::class, 'grid']);
            Route::resource('roles', RolesController::class);
        });
    });
});
