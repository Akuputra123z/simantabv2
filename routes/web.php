<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuditAssignmentController;
use App\Http\Controllers\AuditProgramController;
use App\Http\Controllers\AuditProgramDetailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KodeRekomendasiController;
use App\Http\Controllers\KodeTemuanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LhpController;
use App\Http\Controllers\OpdDashboardController;
use App\Http\Controllers\OpdProfileController;
use App\Http\Controllers\OpdTindakLanjutController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PegawaiInspektoratController;
use App\Http\Controllers\PegawaiOpdController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\TemuanController;
use App\Http\Controllers\TindakLanjutCicilanController;
use App\Http\Controllers\TindakLanjutController;
use App\Http\Controllers\UnitDiperiksaController;
use Illuminate\Support\Facades\Route;

// Public Route
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/tracking', function () {
    return view('pages.tracking', ['search' => null, 'lhp' => null]);
})->name('tracking.public');

Route::post('/tracking', [LhpController::class, 'tracking'])->name('tracking.public.post');

// --- GRUP 1: Akses untuk Semua User (Login & Aktif) ---
Route::middleware(['auth', 'active'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Fitur Audit & LHP
    Route::delete('lhps/bulk-delete', [LhpController::class, 'bulkDelete'])->name('lhps.bulkDelete');
    Route::resource('lhps', LhpController::class);
    Route::post('/lhps/{lhp}/refresh', [LhpController::class, 'refresh'])->name('lhps.refresh');

    Route::resource('temuan', TemuanController::class);
    Route::resource('recommendations', RecommendationController::class);
    Route::resource('tindak-lanjuts', TindakLanjutController::class);
    Route::get('/recommendations-by-program/{programId}', [TindakLanjutController::class, 'getRekomendasisByProgram']);
    Route::patch('/tindak-lanjuts/{tindakLanjut}/buka-kunci-opd', [TindakLanjutController::class, 'bukaKunciOpd'])
        ->name('tindak-lanjuts.buka-kunci-opd');
    Route::post('/tindak-lanjuts/{tindakLanjut}/tolak-opd', [TindakLanjutController::class, 'tolakOpd'])
        ->name('tindak-lanjuts.tolak-opd');
    
    // Audit Assignment
    Route::resource('audit-assignment', AuditAssignmentController::class);
    Route::delete('/audit-assignment/bulk-delete', [AuditAssignmentController::class, 'bulkDelete'])->name('audit-assignment.bulkDelete');
    Route::get('/lhp/{lhpId}/temuans', [RecommendationController::class, 'getTemuans']);
    Route::get('/lhps-by-program/{programId}', [RecommendationController::class, 'getLhpsByProgram']);

    // --- Audit Program Detail (Fleksibel) ---
    // --- Audit Program Detail (Fleksibel) ---
// --- Audit Program Detail (Fleksibel) ---
Route::prefix('audit-program-detail')->name('audit-program-detail.')->group(function () {
    
    // 1. PINDAHKAN KE SINI (Rute statis/pasti harus di atas rute wildcard {id})
    Route::get('/download-template', [AuditProgramDetailController::class, 'downloadTemplate'])
        ->name('download-template');

    Route::post('/import', [AuditProgramDetailController::class, 'import'])
        ->name('import');
    Route::post('/bulk-delete', [AuditProgramDetailController::class, 'bulkDelete'])
        ->name('bulk-delete');

    // 2. Rute dengan parameter/wildcard diletakkan di bawah
    Route::get('/create/{audit_program_id}', [AuditProgramDetailController::class, 'create'])->name('create');
    Route::post('/', [AuditProgramDetailController::class, 'store'])->name('store');
    
    // Rute ini yang sebelumnya "memakan" rute download-template
    Route::get('/{id}', [AuditProgramDetailController::class, 'show'])->name('show');
    
    Route::get('/{auditProgramDetail}/edit', [AuditProgramDetailController::class, 'edit'])->name('edit');
    Route::put('/{auditProgramDetail}', [AuditProgramDetailController::class, 'update'])->name('update');
    Route::delete('/{auditProgramDetail}', [AuditProgramDetailController::class, 'destroy'])->name('destroy');
});

    // Cicilan Tindak Lanjut
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

    // Helpers & Attachments
   Route::get('/get-program-details/{programId}', [AuditAssignmentController::class, 'getProgramDetails'])->name('get-program-details'); // TAMBAHKAN INI
Route::get('/get-kecamatan/{kategori}', [AuditAssignmentController::class, 'getKecamatan'])->name('get-kecamatan');
Route::get('/get-unit/{kecamatan}', [AuditAssignmentController::class, 'getUnit'])->name('get-unit');
Route::get('/audit-assignment/{id}/preview', [AuditAssignmentController::class, 'print'])->name('audit-assignment.preview');
Route::get('/audit-assignment/{id}/print', [AuditAssignmentController::class, 'printPdf'])->name('audit-assignment.print');
Route::post('/audit-assignment/{auditAssignment}/sign', [AuditAssignmentController::class, 'sign'])->name('audit-assignment.sign');

    // Master Data & Audit Program Utama
    Route::resource('kode-temuan', KodeTemuanController::class);
    Route::resource('unit-diperiksa', UnitDiperiksaController::class);
    Route::resource('audit-program', AuditProgramController::class);
    Route::get('/audit-program/{auditProgram}/preview', [AuditProgramController::class, 'preview'])->name('audit-program.preview');
    Route::get('/audit-program/{auditProgram}/preview-pdf', [AuditProgramController::class, 'previewPdf'])->name('audit-program.preview-pdf');
    Route::match(['GET', 'POST'], '/audit-program/{auditProgram}/export-pdf', [AuditProgramController::class, 'exportPdf'])->name('audit-program.export-pdf');
    Route::match(['GET', 'POST'], '/audit-program/{auditProgram}/export-excel', [AuditProgramController::class, 'exportExcel'])->name('audit-program.export-excel');
    Route::post('/audit-program/{auditProgram}/approve', [AuditProgramController::class, 'approve'])->name('audit-program.approve');
    Route::post('/audit-program/{auditProgram}/reject', [AuditProgramController::class, 'reject'])->name('audit-program.reject');
    Route::post('/audit-program/{auditProgram}/batal-setujui', [AuditProgramController::class, 'batalSetujui'])->name('audit-program.batal-setujui');
    Route::resource('kode-rekomendasi', KodeRekomendasiController::class);
    Route::patch('kode-rekomendasi/{kodeRekomendasi}/toggle', [KodeRekomendasiController::class, 'toggleStatus'])->name('kode-rekomendasi.toggle');
});

// --- GRUP 2: Khusus Super Admin (Manajemen User) ---
Route::middleware(['auth', 'active', 'role:super_admin'])->group(function () {

    // Permissions Management
    Route::resource('permissions', PermissionController::class)
         ->parameters(['permissions' => 'role'])
         ->except(['show']);
    Route::post('permissions/permission/store', [PermissionController::class, 'storePermission'])->name('permissions.permission.store');
    Route::delete('permissions/permission/{permission}', [PermissionController::class, 'destroyPermission'])->name('permissions.permission.destroy');

    // ── Pegawai Inspektorat ──
    Route::prefix('pegawai/inspektorat')->name('pegawai.inspektorat.')->group(function () {
        Route::get('/', [PegawaiInspektoratController::class, 'index'])->name('index');
        Route::get('/create', [PegawaiController::class, 'create'])->name('create');
        Route::post('/', [PegawaiController::class, 'store'])->name('store');
        Route::get('{user}', [PegawaiInspektoratController::class, 'show'])->name('show');
        Route::get('{user}/edit', [PegawaiController::class, 'edit'])->name('edit');
        Route::put('{user}', [PegawaiController::class, 'update'])->name('update');
        Route::delete('{user}', [PegawaiController::class, 'destroy'])->name('destroy');
        Route::patch('{user}/toggle-active', [PegawaiController::class, 'toggleActive'])->name('toggle-active');
    });

    // ── Pegawai OPD ──
    Route::prefix('pegawai/opd')->name('pegawai.opd.')->group(function () {
        Route::get('/', [PegawaiOpdController::class, 'index'])->name('index');
        Route::get('/create', [PegawaiController::class, 'create'])->name('create');
        Route::post('/', [PegawaiController::class, 'store'])->name('store');
        Route::get('{user}', [PegawaiOpdController::class, 'show'])->name('show');
        Route::get('{user}/edit', [PegawaiController::class, 'edit'])->name('edit');
        Route::put('{user}', [PegawaiController::class, 'update'])->name('update');
        Route::delete('{user}', [PegawaiController::class, 'destroy'])->name('destroy');
        Route::patch('{user}/toggle-active', [PegawaiController::class, 'toggleActive'])->name('toggle-active');
    });
});

// --- GRUP 3: Laporan ---
Route::middleware(['auth', 'active'])->prefix('laporan')->name('laporan.')->group(function () {
    Route::get('/', [LaporanController::class, 'index'])->name('index');
    Route::get('/{lhp}/rekap', [LaporanController::class, 'rekapPerLhp'])->name('rekap-per-lhp');
    Route::get('/download/pdf/semua', [LaporanController::class, 'downloadPdfSemua'])->name('download-pdf-semua');
    Route::get('/download/pdf/{lhp}', [LaporanController::class, 'downloadPdfPerLhp'])->name('download-pdf-per-lhp');
    Route::get('/preview/pdf/{lhp}', [LaporanController::class, 'previewPdfPerLhp'])->name('preview-pdf-per-lhp');
    Route::get('/download/excel/semua', [LaporanController::class, 'downloadExcelSemua'])->name('download-excel-semua');
    Route::get('/download/excel/{lhp}', [LaporanController::class, 'downloadExcelPerLhp'])->name('download-excel-per-lhp');
});

// --- GRUP 4: OPD (User Eksternal) ---
Route::middleware(['auth', 'active', 'role:opd'])
    ->prefix('opd')
    ->name('opd.')
    ->group(function () {
        Route::get('/tindak-lanjut', [OpdTindakLanjutController::class, 'index'])
            ->name('tindak-lanjut.index');
        Route::get('/tindak-lanjut/{tindakLanjut}', [OpdTindakLanjutController::class, 'show'])
            ->name('tindak-lanjut.show');
        Route::post('/tindak-lanjut/{tindakLanjut}/upload', [OpdTindakLanjutController::class, 'upload'])
            ->name('tindak-lanjut.upload');
        Route::post('/tindak-lanjut/{tindakLanjut}/kirim', [OpdTindakLanjutController::class, 'kirim'])
            ->name('tindak-lanjut.kirim');
        Route::delete('/tindak-lanjut/{tindakLanjut}/lampiran/{attachment}', [OpdTindakLanjutController::class, 'hapusLampiran'])
            ->name('tindak-lanjut.hapus-lampiran');

        Route::get('/dashboard', [OpdDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [OpdProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [OpdProfileController::class, 'update'])->name('profile.update');
    });

require __DIR__.'/auth.php';