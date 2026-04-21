<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Jalankan dengan: php artisan db:seed --class=RoleAndPermissionSeeder
 * Atau lewat DatabaseSeeder: $this->call(RoleAndPermissionSeeder::class)
 */
class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache Spatie agar perubahan langsung efektif
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── 1. Definisi Permission ─────────────────────────────────────────────
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
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ── 2. Definisi Role + Permission yang diberikan ───────────────────────

        // SUPER ADMIN — akses penuh, tidak perlu assign permission satu-satu
        $superAdmin = Role::firstOrCreate(['name' => User::ROLE_SUPER_ADMIN]);
        $superAdmin->syncPermissions(Permission::all());

        // KEPALA INSPEKTORAT — baca semua + verifikasi, tidak bisa hapus/manage master
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
        ]);

        // KETUA TIM — kelola semua data audit, bisa verifikasi cicilan tim-nya
        $ketuaTim = Role::firstOrCreate(['name' => User::ROLE_KETUA_TIM]);
        $ketuaTim->syncPermissions([
            'lhp.view', 'lhp.create', 'lhp.edit',
            'temuan.view', 'temuan.create', 'temuan.edit',
            'rekomendasi.view', 'rekomendasi.create', 'rekomendasi.edit',
            'tindak-lanjut.view', 'tindak-lanjut.create', 'tindak-lanjut.edit',
            'cicilan.view', 'cicilan.create', 'cicilan.edit', 'cicilan.verifikasi',
            'audit-assignment.view', 'audit-assignment.create', 'audit-assignment.edit',
            'laporan.view',
        ]);

        // ANGGOTA — input data, tidak bisa hapus atau verifikasi
        $anggota = Role::firstOrCreate(['name' => User::ROLE_ANGGOTA]);
        $anggota->syncPermissions([
            'lhp.view', 'lhp.create', 'lhp.edit',
            'temuan.view', 'temuan.create', 'temuan.edit',
            'rekomendasi.view', 'rekomendasi.create',
            'tindak-lanjut.view', 'tindak-lanjut.create',
            'cicilan.view', 'cicilan.create',
            'audit-assignment.view',
        ]);

        // STAFF INSPEKTORAT — hanya baca + input tindak lanjut & cicilan
        $staff = Role::firstOrCreate(['name' => User::ROLE_STAFF_INSPEKTORAT]);
        $staff->syncPermissions([
            'lhp.view',
            'temuan.view',
            'rekomendasi.view',
            'tindak-lanjut.view', 'tindak-lanjut.create', 'tindak-lanjut.edit',
            'cicilan.view', 'cicilan.create', 'cicilan.edit',
            'laporan.view',
        ]);

        // ── 3. Buat akun Super Admin default (hanya jika belum ada) ───────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@inspektorat.go.id'],
            [
                'name'     => 'Super Administrator',
                'password' => Hash::make('password'), // GANTI setelah deploy!
                'nip'      => '000000000000000000',
                'jabatan'  => 'Administrator Sistem',
                'is_active' => true,
            ]
        );

        $admin->syncRoles([User::ROLE_SUPER_ADMIN]);

        $this->command->info('Roles & permissions berhasil di-seed.');
        $this->command->warn('Jangan lupa ganti password admin default!');
    }
}