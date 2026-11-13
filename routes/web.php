<?php

use App\Http\Controllers\Admin\DashboardAdminController;
use App\Http\Controllers\Admin\DataAtsController as AdminDataAtsController;
use App\Http\Controllers\Admin\DataAtsInputController;
use App\Http\Controllers\Admin\LaporanBulananController;
use App\Http\Controllers\Admin\MutasiController as AdminMutasiController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Controllers\Admin\PetaSebaranController;
use App\Http\Controllers\Admin\SkBantuanController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\LampiranController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\Pengawas\DashboardPengawasController;
use App\Http\Controllers\Pengawas\DataAtsController;
use App\Http\Controllers\Pengawas\EwsController;
use App\Http\Controllers\Pengawas\MutasiController as PengawasMutasiController;
use App\Http\Controllers\Pengawas\ValidasiLapanganController;
use App\Http\Controllers\Verifikator\VerifikasiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        return redirect()->route($user->beranda());
    }

    return redirect()->route('login');
});

// ================= AUTH =================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
});
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::post('/notifikasi/{id}/tandai-dibaca', [NotifikasiController::class, 'tandaiDibaca'])
    ->middleware('auth')
    ->name('notifikasi.baca');

Route::get('/lampiran/{riwayat}', [LampiranController::class, 'show'])
    ->middleware(['auth', 'role:admin_dinas,pengawas_sekolah'])
    ->name('lampiran.show');

// ================= ADMIN DINAS =================
Route::middleware(['auth', 'role:admin_dinas'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', DashboardAdminController::class)->name('dashboard');

        Route::get('/data-ats', [AdminDataAtsController::class, 'index'])->name('data-ats.index');

        // Input & import data (hanya admin). Data masuk antrean verifikasi dulu.
        Route::get('/data-ats/create', [DataAtsInputController::class, 'create'])->name('data-ats.create');
        Route::post('/data-ats', [DataAtsInputController::class, 'store'])->name('data-ats.store');
        Route::get('/data-ats/import', [DataAtsInputController::class, 'importForm'])->name('data-ats.import');
        Route::get('/data-ats/import/template', [DataAtsInputController::class, 'template'])->name('data-ats.import.template');
        Route::post('/data-ats/import', [DataAtsInputController::class, 'importStore'])->middleware('throttle:unggah')->name('data-ats.import.store');

        Route::get('/data-ats/{dataAtsItem}', [AdminDataAtsController::class, 'show'])->name('data-ats.show');
        Route::put('/data-ats/{dataAtsItem}/ajukan-ulang', [AdminDataAtsController::class, 'ajukanUlang'])->name('data-ats.ajukan-ulang');

        Route::get('/mutasi', [AdminMutasiController::class, 'index'])->name('mutasi.index');

        Route::get('/peta-sebaran', PetaSebaranController::class)->name('peta-sebaran');

        Route::get('/laporan-bulanan', [LaporanBulananController::class, 'index'])->name('laporan.index');
        Route::get('/laporan-bulanan/export-pdf', [LaporanBulananController::class, 'exportPdf'])->name('laporan.export-pdf');

        Route::get('/pengaturan/akun', [PengaturanController::class, 'akunIndex'])->name('pengaturan.akun');
        Route::patch('/pengaturan/akun/{user}/toggle-aktif', [PengaturanController::class, 'akunToggleActive'])->name('pengaturan.akun.toggle');
        Route::post('/pengaturan/akun/{user}/reset-password', [PengaturanController::class, 'akunResetPassword'])->name('pengaturan.akun.reset');
        Route::get('/pengaturan/sekolah', [PengaturanController::class, 'sekolahIndex'])->name('pengaturan.sekolah');
        Route::get('/pengaturan/sekolah/{sekolah}/edit', [PengaturanController::class, 'sekolahEdit'])->name('pengaturan.sekolah.edit');
        Route::put('/pengaturan/sekolah/{sekolah}', [PengaturanController::class, 'sekolahUpdate'])->name('pengaturan.sekolah.update');

        Route::post('/sk-bantuan/{dataAtsItem}', [SkBantuanController::class, 'store'])->name('sk-bantuan.store');
        Route::delete('/sk-bantuan/{skBantuan}', [SkBantuanController::class, 'destroy'])->name('sk-bantuan.destroy');
    });

// ================= VERIFIKATOR =================
Route::middleware(['auth', 'role:verifikator'])
    ->prefix('verifikator')
    ->name('verifikator.')
    ->group(function () {
        Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
        Route::post('/verifikasi/aksi-massal', [VerifikasiController::class, 'aksiMassal'])->name('verifikasi.aksi-massal');
        Route::get('/verifikasi/{dataAtsItem}', [VerifikasiController::class, 'show'])->name('verifikasi.show');
        Route::put('/verifikasi/{dataAtsItem}/setujui', [VerifikasiController::class, 'approve'])->name('verifikasi.approve');
        Route::put('/verifikasi/{dataAtsItem}/tolak', [VerifikasiController::class, 'reject'])->name('verifikasi.reject');
    });

// ================= PENGAWAS SEKOLAH =================
Route::middleware(['auth', 'role:pengawas_sekolah'])
    ->prefix('pengawas')
    ->name('pengawas.')
    ->group(function () {
        Route::get('/dashboard', DashboardPengawasController::class)->name('dashboard');

        // Validasi lapangan: gerbang terakhir sebelum data masuk master Data ATS.
        Route::get('/validasi', [ValidasiLapanganController::class, 'index'])->name('validasi.index');
        Route::get('/validasi/{dataAtsItem}', [ValidasiLapanganController::class, 'show'])->name('validasi.show');
        Route::put('/validasi/{dataAtsItem}', [ValidasiLapanganController::class, 'validasi'])->name('validasi.update');

        // Data ATS (master): lihat seluruh data (cari + filter). Edit/hapus hanya untuk data sekolah sendiri.
        Route::get('/data-ats', [DataAtsController::class, 'index'])->name('data-ats.index');
        Route::get('/data-ats/{dataAtsItem}/edit', [DataAtsController::class, 'edit'])->name('data-ats.edit');
        Route::put('/data-ats/{dataAtsItem}', [DataAtsController::class, 'update'])->name('data-ats.update');
        Route::delete('/data-ats/{dataAtsItem}', [DataAtsController::class, 'destroy'])->name('data-ats.destroy');

        // Early Warning: jembatan dari Data ATS lewat tombol "Tindakan"
        Route::get('/ews', [EwsController::class, 'index'])->name('ews.index');
        Route::get('/ews/{dataAtsItem}', [EwsController::class, 'show'])->name('ews.show');
        Route::put('/ews/{dataAtsItem}', [EwsController::class, 'updateStatus'])->name('ews.update-status');

        Route::get('/mutasi', [PengawasMutasiController::class, 'index'])->name('mutasi.index');
        Route::get('/mutasi/create', [PengawasMutasiController::class, 'create'])->name('mutasi.create');
        Route::post('/mutasi', [PengawasMutasiController::class, 'store'])->name('mutasi.store');
    });
