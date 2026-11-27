<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\RewardController as AdminRewardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Staff\AccountController;
use App\Http\Controllers\Staff\QueueController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Petugas\DashboardController as PetugasDashboardController;
use App\Http\Controllers\Warga\DashboardController;
use App\Http\Controllers\Warga\ProfileController;
use App\Http\Controllers\Warga\ReportController;
use App\Http\Controllers\Warga\RewardController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

/* ------------------------------------------------------------ Auth */
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [LoginController::class, 'create'])->name('login');
    Route::post('/masuk', [LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/daftar', [RegisterController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'store'])->middleware('throttle:10,1')->name('register.store');
});
Route::post('/keluar', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

/* ------------------------------------------------------------ WARGA */
Route::middleware(['auth', 'role:warga'])->group(function () {
    Route::get('/beranda', DashboardController::class)->name('beranda');

    // Laporan (CRUD)
    Route::get('/laporkan', [ReportController::class, 'create'])->name('laporan.create');
    Route::post('/laporkan', [ReportController::class, 'store'])->middleware('throttle:20,1')->name('laporan.store');
    Route::get('/riwayat', [ReportController::class, 'index'])->name('laporan.index');
    Route::get('/riwayat/{report}/ubah', [ReportController::class, 'edit'])->name('laporan.edit');
    Route::put('/riwayat/{report}', [ReportController::class, 'update'])->name('laporan.update');
    Route::delete('/riwayat/{report}', [ReportController::class, 'destroy'])->name('laporan.destroy');

    // Hadiah
    Route::get('/hadiah', [RewardController::class, 'index'])->name('hadiah.index');
    Route::get('/hadiah/riwayat', [RewardController::class, 'history'])->name('hadiah.history');
    Route::post('/hadiah/{reward}/tukar', [RewardController::class, 'redeem'])->name('hadiah.redeem');

    // Profil
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profil.update');
});

/* ------------------------------------------------------------ PETUGAS & ADMIN: antrean, review, riwayat, akun */
Route::middleware(['auth', 'role:petugas,admin'])->group(function () {
    Route::get('/antrean', [QueueController::class, 'index'])->name('antrean.index');
    Route::get('/antrean/tinjau-instan', [QueueController::class, 'next'])->name('antrean.next');
    Route::get('/antrean/{report}', [QueueController::class, 'show'])->name('antrean.show');
    Route::post('/antrean/{report}/setujui', [QueueController::class, 'approve'])->name('antrean.approve');
    Route::post('/antrean/{report}/tolak', [QueueController::class, 'reject'])->name('antrean.reject');
    Route::get('/riwayat-verifikasi', [QueueController::class, 'history'])->name('verifikasi.riwayat');

    Route::get('/akun', [AccountController::class, 'edit'])->name('akun.edit');
    Route::put('/akun', [AccountController::class, 'update'])->name('akun.update');
});

/* ------------------------------------------------------------ PETUGAS */
Route::middleware(['auth', 'role:petugas'])->group(function () {
    Route::get('/petugas', PetugasDashboardController::class)->name('petugas.dashboard');
});

/* ------------------------------------------------------------ ADMIN */
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    // Pengguna
    Route::get('/pengguna', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/pengguna/baru', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/pengguna', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/pengguna/{user}/ubah', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/pengguna/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::patch('/pengguna/{user}/status', [AdminUserController::class, 'toggle'])->name('users.toggle');

    // Reward & pengaturan poin
    Route::get('/reward', [AdminRewardController::class, 'index'])->name('rewards.index');
    Route::get('/reward/baru', [AdminRewardController::class, 'create'])->name('rewards.create');
    Route::post('/reward', [AdminRewardController::class, 'store'])->name('rewards.store');
    Route::get('/reward/{reward}/ubah', [AdminRewardController::class, 'edit'])->name('rewards.edit');
    Route::put('/reward/{reward}', [AdminRewardController::class, 'update'])->name('rewards.update');
    Route::delete('/reward/{reward}', [AdminRewardController::class, 'destroy'])->name('rewards.destroy');
    Route::put('/pengaturan-poin', [AdminRewardController::class, 'updatePoints'])->name('rewards.points');

    // Ekspor
    Route::get('/ekspor', [ExportController::class, 'index'])->name('export.index');
    Route::get('/ekspor/ringkasan', [ExportController::class, 'summary'])->name('export.summary');
    Route::get('/ekspor/unduh', [ExportController::class, 'download'])->name('export.download');
});
