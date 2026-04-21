<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuditAssignmentController;
use App\Http\Controllers\AuditProgramController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KodeRekomendasiController;
use App\Http\Controllers\KodeTemuanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LhpController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\TemuanController;
use App\Http\Controllers\TindakLanjutCicilanController;
use App\Http\Controllers\TindakLanjutController;
use App\Http\Controllers\UnitDiperiksaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public Route
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/tracking', function () {
    return view('pages.tracking', [
        'search' => null,
        'lhp' => null
    ]);
})->name('tracking.public');

Route::post('/tracking', [LhpController::class, 'tracking'])->name('tracking.public');

// --- GRUP 1: Akses untuk Semua User (Login & Aktif) ---
Route::middleware(['auth', 'active'])->group(function () {
    
    Route::get('/dashboard', function () {
        return view('dashboard', ['title' => 'Dashboard']);
    })->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

    // Fitur Audit & LHP (Akses diatur via Permission di Controller/Policy)
    Route::resource('lhps', LhpController::class);
    Route::post('/lhps/{lhp}/refresh', [LhpController::class, 'refresh'])->name('lhps.refresh');
    Route::delete('lhps/bulk-delete', [LhpController::class, 'bulkDelete'])->name('lhps.bulkDelete');

    Route::resource('temuan', TemuanController::class);
    Route::resource('recommendations', RecommendationController::class);


    Route::resource('tindak-lanjuts', TindakLanjutController::class);
    Route::resource('audit-assignment', AuditAssignmentController::class);
    Route::delete('/audit-assignment/bulk-delete', [AuditAssignmentController::class, 'bulkDelete'])->name('audit-assignment.bulkDelete');
Route::get('/lhp/{lhpId}/temuans', [RecommendationController::class, 'getTemuans']);
    // Cicilan
    Route::prefix('tindak-lanjuts/{tindakLanjut}/cicilans')
        ->name('tindak-lanjuts.cicilans.')
        ->group(function () {
            Route::get('/',           [TindakLanjutCicilanController::class, 'index'])->name('index');
            Route::get('/create',     [TindakLanjutCicilanController::class, 'create'])->name('create');
            Route::post('/',          [TindakLanjutCicilanController::class, 'store'])->name('store');
            Route::get('/{cicilan}',  [TindakLanjutCicilanController::class, 'show'])->name('show');
            Route::get('/{cicilan}/edit', [TindakLanjutCicilanController::class, 'edit'])->name('edit');
            Route::put('/{cicilan}',  [TindakLanjutCicilanController::class, 'update'])->name('update');
            Route::delete('/{cicilan}', [TindakLanjutCicilanController::class, 'destroy'])->name('destroy');
            Route::patch('/{cicilan}/verifikasi', [TindakLanjutCicilanController::class, 'verifikasi'])->name('verifikasi');
        });

    // Helpers
    Route::get('/get-kecamatan/{kategori}', [AuditAssignmentController::class, 'getKecamatan'])->name('get-kecamatan');
    Route::get('/get-unit/{kecamatan}', [AuditAssignmentController::class, 'getUnit'])->name('get-unit');
    Route::delete('/attachments/{id}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

     Route::resource('permissions', PermissionController::class)
         ->parameters(['permissions' => 'role'])
         ->except(['show']);
 
    // Permission CRUD — letakkan SEBELUM resource agar tidak tertimpa
    Route::post('permissions/permission/store',
        [PermissionController::class, 'storePermission']
    )->name('permissions.permission.store');
 
    Route::delete('permissions/permission/{permission}',
        [PermissionController::class, 'destroyPermission']
    )->name('permissions.permission.destroy');
});

// --- GRUP 2: Khusus Super Admin (Manajemen User & Master Data) ---
Route::middleware(['auth', 'active', 'role:super_admin'])->group(function () {
    
    // User Management
    Route::resource('users', UserController::class);
    Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');

    // Master Data
    Route::resource('kode-temuan', KodeTemuanController::class);
    Route::resource('unit-diperiksa', UnitDiperiksaController::class);
    
    Route::resource('audit-program', AuditProgramController::class);
    Route::resource('kode-rekomendasi', KodeRekomendasiController::class);
    Route::patch('kode-rekomendasi/{kodeRekomendasi}/toggle', [KodeRekomendasiController::class, 'toggleStatus'])->name('kode-rekomendasi.toggle');
});

Route::prefix('laporan')->name('laporan.')->group(function () {
 
    // Halaman utama laporan
    Route::get('/', [LaporanController::class, 'index'])->name('index');
 
    // Detail rekap per LHP (view)
    Route::get('/{lhp}/rekap', [LaporanController::class, 'rekapPerLhp'])->name('rekap-per-lhp');
 
    // Download PDF
    Route::get('/download/pdf/semua', [LaporanController::class, 'downloadPdfSemua'])->name('download-pdf-semua');
    Route::get('/download/pdf/{lhp}', [LaporanController::class, 'downloadPdfPerLhp'])->name('download-pdf-per-lhp');
 
    // Download Excel
    Route::get('/download/excel/semua', [LaporanController::class, 'downloadExcelSemua'])->name('download-excel-semua');
    Route::get('/download/excel/{lhp}', [LaporanController::class, 'downloadExcelPerLhp'])->name('download-excel-per-lhp');
});


require __DIR__.'/auth.php';