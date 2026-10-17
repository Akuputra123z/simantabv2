<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // LHP
            'lhp.view', 'lhp.create', 'lhp.edit', 'lhp.delete',

            // Temuan
            'temuan.view', 'temuan.create', 'temuan.edit', 'temuan.delete',

            // Rekomendasi
            'rekomendasi.view', 'rekomendasi.create', 'rekomendasi.edit', 'rekomendasi.delete',

            // Tindak Lanjut
            'tindak-lanjut.view', 'tindak-lanjut.create', 'tindak-lanjut.edit', 'tindak-lanjut.delete',

            // Cicilan
            'cicilan.view', 'cicilan.create', 'cicilan.edit', 'cicilan.delete',
            'cicilan.verifikasi',

            // Audit Assignment
            'audit-assignment.view', 'audit-assignment.create', 'audit-assignment.edit', 'audit-assignment.delete',

            // Master Data
            'kode-temuan.manage',
            'kode-rekomendasi.manage',
            'unit-diperiksa.manage',

            // User Management
            'user.view', 'user.create', 'user.edit', 'user.delete',
            'user.assign-role',

            // Laporan
            'laporan.view', 'laporan.export',

            // OPD
            'tindak-lanjut.opd-view',
            'tindak-lanjut.opd-upload',

            // Admin/IRBAN
            'tindak-lanjut.buka-kunci-opd',
            'tindak-lanjut.tolak-opd',

            // LHR & Template Management
            'template.manage',
            'numbering.manage',
            'permohonan.create',
            'permohonan.register',
            'lhr.view',
            'lhr.create',
            'lhr.update',
            'lhr.submit',
            'lhr.review',
            'lhr.approve',
            'lhr.revise',
            'lhr.cancel',
            'lhr.download-final',
            'dataset.import',
            'comparison.run',

            // LHTL & Berita Acara
            'lhtl.view',
            'lhtl.create',
            'lhtl.update',
            'lhtl.review',
            'lhtl.approve',
            'tl.approve-tidak-dapat',
            'ba.confirm',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ── 2. Definisi Role + Permission yang diberikan ───────────────────────

        // SUPER ADMIN — akses penuh, tidak perlu assign permission satu-satu
        $superAdmin = Role::firstOrCreate(['name' => User::ROLE_SUPER_ADMIN]);
        $superAdmin->syncPermissions(Permission::all());

        // KEPALA INSPEKTORAT — baca semua + verifikasi + approval LHR & LHTL
        $kepala = Role::firstOrCreate(['name' => User::ROLE_KEPALA_INSPEKTORAT]);
        $kepala->syncPermissions([
            'lhp.view',
            'temuan.view',
            'rekomendasi.view',
            'tindak-lanjut.view',
            'cicilan.view', 'cicilan.verifikasi',
            'audit-assignment.view',
            'laporan.view', 'laporan.export',
            'user.view',
            'lhr.view',
            'lhr.approve',
            'lhr.download-final',
            'lhtl.view',
            'lhtl.approve',
            'tl.approve-tidak-dapat',
        ]);

        // PENGENDALI TEKNIS (IRBAN) — kendali mutu, telaah LHR, verifikasi tindak lanjut
        $pengendaliTeknis = Role::firstOrCreate(['name' => User::ROLE_PENGENDALI_TEKNIS]);
        $irbanPermissions = [
            'lhp.view',
            'temuan.view',
            'rekomendasi.view',
            'tindak-lanjut.view',
            'tindak-lanjut.edit',
            'cicilan.view', 'cicilan.verifikasi',
            'audit-assignment.view',
            'laporan.view',
            'lhr.view',
            'lhr.review',
            'lhr.download-final',
            'dataset.import',
            'comparison.run',
            'lhtl.view',
            'lhtl.review',
            'tl.approve-tidak-dapat',
            'ba.confirm',
        ];
        $pengendaliTeknis->syncPermissions($irbanPermissions);

        // IRBAN (INSPEKTUR PEMBANTU) — setara Pengendali Teknis dalam pengawasan
        $irbanRole = Role::firstOrCreate(['name' => User::ROLE_IRBAN]);
        $irbanRole->syncPermissions($irbanPermissions);

        // KETUA TIM — kelola semua data audit, susun LHR & LHTL, verifikasi cicilan
        $ketuaTim = Role::firstOrCreate(['name' => User::ROLE_KETUA_TIM]);
        $ketuaTim->syncPermissions([
            'lhp.view', 'lhp.create', 'lhp.edit',
            'temuan.view', 'temuan.create', 'temuan.edit',
            'rekomendasi.view', 'rekomendasi.create', 'rekomendasi.edit',
            'tindak-lanjut.view', 'tindak-lanjut.create', 'tindak-lanjut.edit',
            'cicilan.view', 'cicilan.create', 'cicilan.edit', 'cicilan.verifikasi',
            'audit-assignment.view', 'audit-assignment.create', 'audit-assignment.edit',
            'laporan.view',
            'lhr.view', 'lhr.create', 'lhr.update', 'lhr.submit', 'lhr.revise', 'lhr.cancel',
            'lhr.download-final',
            'dataset.import', 'comparison.run',
            'lhtl.view', 'lhtl.create', 'lhtl.update',
            'ba.confirm',
        ]);

        // ANGGOTA — input data pengujian/catatan yang ditugaskan
        $anggota = Role::firstOrCreate(['name' => User::ROLE_ANGGOTA]);
        $anggota->syncPermissions([
            'lhp.view', 'lhp.create', 'lhp.edit',
            'temuan.view', 'temuan.create', 'temuan.edit',
            'rekomendasi.view', 'rekomendasi.create',
            'tindak-lanjut.view', 'tindak-lanjut.create',
            'cicilan.view', 'cicilan.create',
            'audit-assignment.view',
            'lhr.view', 'lhr.update', 'lhr.download-final',
            'dataset.import', 'comparison.run',
            'lhtl.view', 'lhtl.update',
            'ba.confirm',
        ]);

        // STAFF INSPEKTORAT — register permohonan, buat kerangka LHR/LHTL
        $staff = Role::firstOrCreate(['name' => User::ROLE_STAFF_INSPEKTORAT]);
        $staff->syncPermissions([
            'lhp.view',
            'temuan.view',
            'rekomendasi.view',
            'tindak-lanjut.view', 'tindak-lanjut.create', 'tindak-lanjut.edit',
            'cicilan.view', 'cicilan.create', 'cicilan.edit',
            'laporan.view',
            'permohonan.register',
            'lhr.view', 'lhr.create', 'lhr.download-final',
            'lhtl.view', 'lhtl.create',
        ]);

        // OPD — ajukan permohonan, kirim dokumen revisi TL, TTD Berita Acara
        $opd = Role::firstOrCreate(['name' => User::ROLE_OPD]);
        $opd->syncPermissions([
            'tindak-lanjut.opd-view',
            'tindak-lanjut.opd-upload',
            'permohonan.create',
            'lhr.view',
            'lhr.download-final',
            'lhtl.view',
            'ba.confirm',
        ]);

        // ── 3. Buat akun Super Admin default (hanya jika belum ada) ───────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@inspektorat.go.id'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Super Administrator',
                'password' => Hash::make('password'), // GANTI setelah deploy!
                'nip' => '000000000000000000',
                'jabatan' => 'Administrator Sistem',
                'jenis_kelamin' => 'L',
                'is_active' => true,
            ]
        );

        $admin->syncRoles([User::ROLE_SUPER_ADMIN]);

        $this->command->info('Roles & permissions berhasil di-seed.');
        $this->command->warn('Jangan lupa ganti password admin default!');
    }
}
