<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * PermissionController
 *
 * Mengelola:
 * - Daftar role & permission (index)
 * - Buat role baru (create/store)
 * - Edit permission yang dimiliki sebuah role (edit/update)
 * - Hapus role kustom (destroy)
 * - Buat permission baru (storePermission)
 * - Hapus permission kustom yang belum terikat (destroyPermission)
 */
class PermissionController extends Controller
{
    /**
     * Daftar role bawaan sistem yang tidak boleh dihapus demi integritas aplikasi.
     */
    public const PROTECTED_ROLES = [
        User::ROLE_SUPER_ADMIN,
        User::ROLE_KEPALA_INSPEKTORAT,
        User::ROLE_IRBAN,
        User::ROLE_PENGENDALI_TEKNIS,
        User::ROLE_KETUA_TIM,
        User::ROLE_ANGGOTA,
        User::ROLE_STAFF_INSPEKTORAT,
        User::ROLE_OPD,
    ];

    /**
     * Deskripsi fungsional peran kerja dalam pengawasan Inspektorat.
     */
    public const ROLE_DESCRIPTIONS = [
        User::ROLE_SUPER_ADMIN => 'Akses administrator penuh ke seluruh modul sistem, audit log, dan kendali hak otorisasi.',
        User::ROLE_KEPALA_INSPEKTORAT => 'Pimpinan tertinggi Inspektorat, approval akhir laporan pengawasan dan pemantauan tindak lanjut daerah.',
        User::ROLE_IRBAN => 'Inspektur Pembantu wilayah, supervisi penugasan, telaah dan persetujuan LHP/LHR/LHTL serta verifikasi tindak lanjut.',
        User::ROLE_PENGENDALI_TEKNIS => 'Pengendali Teknis pemeriksaan, supervisi metodologis audit dan penjaminan mutu kertas kerja.',
        User::ROLE_KETUA_TIM => 'Koordinator tim pengawasan, penyusunan naskah LHP, entri temuan dan rekomendasi, serta verifikasi bukti tindak lanjut.',
        User::ROLE_ANGGOTA => 'Pelaksana audit lapangan, entri kertas kerja pemeriksaan, dan pencatatan fakta temuan.',
        User::ROLE_STAFF_INSPEKTORAT => 'Dukungan administrasi kesekretariatan pengawasan, pengelolaan template, dan penomoran naskah dinas.',
        User::ROLE_OPD => 'Perangkat Daerah auditee, pelaporan tindak lanjut rekomendasi, dan pengunggahan dokumen bukti pemenuhan.',
    ];

    /**
     * Pengelompokan permission berdasarkan modul bisnis aplikasi.
     */
    public const PERMISSION_GROUPS = [
        'LHP (Laporan Pengawasan)' => [
            'lhp.view',
            'lhp.create',
            'lhp.edit',
            'lhp.delete',
        ],
        'Temuan Pemeriksaan' => [
            'temuan.view',
            'temuan.create',
            'temuan.edit',
            'temuan.delete',
        ],
        'Rekomendasi' => [
            'rekomendasi.view',
            'rekomendasi.create',
            'rekomendasi.edit',
            'rekomendasi.delete',
        ],
        'Tindak Lanjut' => [
            'tindak-lanjut.view',
            'tindak-lanjut.create',
            'tindak-lanjut.edit',
            'tindak-lanjut.delete',
            'tl.approve-tidak-dapat',
        ],
        'Cicilan Kerugian Kas' => [
            'cicilan.view',
            'cicilan.create',
            'cicilan.edit',
            'cicilan.delete',
            'cicilan.verifikasi',
        ],
        'Surat Tugas & Penugasan' => [
            'audit-assignment.view',
            'audit-assignment.create',
            'audit-assignment.edit',
            'audit-assignment.delete',
        ],
        'LHR (Laporan Hasil Reviu)' => [
            'lhr.view',
            'lhr.create',
            'lhr.update',
            'lhr.submit',
            'lhr.review',
            'lhr.approve',
            'lhr.revise',
            'lhr.cancel',
            'lhr.download-final',
        ],
        'LHTL & Berita Acara' => [
            'lhtl.view',
            'lhtl.create',
            'lhtl.update',
            'lhtl.review',
            'lhtl.approve',
            'ba.confirm',
        ],
        'Template & Penomoran' => [
            'template.manage',
            'numbering.manage',
            'permohonan.create',
            'permohonan.register',
        ],
        'Dataset & Analisis' => [
            'dataset.import',
            'comparison.run',
        ],
        'Portal Satuan Kerja OPD' => [
            'tindak-lanjut.opd-view',
            'tindak-lanjut.opd-upload',
            'tindak-lanjut.buka-kunci-opd',
            'tindak-lanjut.tolak-opd',
        ],
        'Master Data Referensi' => [
            'kode-temuan.manage',
            'kode-rekomendasi.manage',
            'unit-diperiksa.manage',
        ],
        'Manajemen Pengguna' => [
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',
            'user.assign-role',
        ],
        'Laporan & Rekapitulasi' => [
            'laporan.view',
            'laporan.export',
        ],
    ];

    // ── Index — semua role + ringkasan permission ─────────────────────────────

    public function index(): View
    {
        $roleOrder = [
            'super_admin' => 1,
            'kepala_inspektorat' => 2,
            'irban' => 3,
            'pengendali_teknis' => 4,
            'ketua_tim' => 5,
            'anggota' => 6,
            'staff_inspektorat' => 7,
            'opd' => 8,
        ];

        $roles = Role::withCount(['permissions', 'users'])
            ->get()
            ->sortBy(fn ($role) => [$roleOrder[$role->name] ?? 99, $role->name])
            ->values();

        $totalPermissions = Permission::count();

        // Eager load seluruh permission beserta relasi roles-nya untuk performa load optimal
        $allPermissions = Permission::with('roles')
            ->orderBy('name')
            ->get();

        $groupedPermissions = $allPermissions->groupBy(function ($perm) {
            if (str_contains($perm->name, '.')) {
                $prefix = explode('.', $perm->name)[0];
                return match ($prefix) {
                    'lhp' => 'LHP (Laporan Pengawasan)',
                    'temuan' => 'Temuan Pemeriksaan',
                    'rekomendasi' => 'Rekomendasi',
                    'tindak-lanjut', 'tl' => 'Tindak Lanjut & Verifikasi',
                    'cicilan' => 'Cicilan Kas',
                    'audit-assignment' => 'Surat Tugas & Penugasan',
                    'lhr' => 'LHR (Hasil Reviu)',
                    'lhtl', 'ba' => 'LHTL & Berita Acara',
                    'template', 'numbering', 'permohonan' => 'Template & Penomoran',
                    'dataset', 'comparison' => 'Dataset & Analisis',
                    'kode-temuan', 'kode-rekomendasi', 'unit-diperiksa' => 'Master Data',
                    'user' => 'Manajemen Pengguna',
                    'laporan' => 'Laporan & Ekspor',
                    default => ucfirst(str_replace('-', ' ', $prefix)),
                };
            }
            return 'Lainnya';
        });

        $protectedRoles = self::PROTECTED_ROLES;
        $roleDescriptions = self::ROLE_DESCRIPTIONS;

        return view('pages.permissions.index', compact(
            'roles',
            'totalPermissions',
            'groupedPermissions',
            'protectedRoles',
            'roleDescriptions'
        ));
    }

    // ── Create role baru ──────────────────────────────────────────────────────

    public function create(): View
    {
        $permissions = Permission::orderBy('name')->get();
        $permissionGroups = $this->buildPermissionGroups($permissions);

        return view('pages.permissions.create', compact('permissionGroups'));
    }

    // ── Store role baru ───────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name', 'regex:/^[a-z0-9_]+$/'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ], [
            'name.regex' => 'Nama role hanya boleh huruf kecil, angka, dan underscore (contoh: koordinator_khusus).',
            'name.unique' => 'Nama role tersebut sudah terdaftar di sistem.',
        ]);

        $role = Role::create(['name' => $validated['name']]);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()
            ->route('permissions.index')
            ->with('success', "Role \"{$role->name}\" berhasil dibuat.");
    }

    // ── Edit permission sebuah role ───────────────────────────────────────────

    public function edit(Role $role): View
    {
        // Super admin selalu memiliki akses bypass penuh
        abort_if(
            $role->name === User::ROLE_SUPER_ADMIN,
            403,
            'Hak akses Super Administrator bersifat penuh (full access) dan tidak dapat dibatasi.'
        );

        $permissions = Permission::orderBy('name')->get();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $permissionGroups = $this->buildPermissionGroups($permissions);
        $userCount = $role->users()->count();
        $isProtected = in_array($role->name, self::PROTECTED_ROLES);

        return view('pages.permissions.edit', compact(
            'role',
            'permissionGroups',
            'rolePermissions',
            'userCount',
            'isProtected'
        ));
    }

    // ── Update permission sebuah role ─────────────────────────────────────────

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_if($role->name === User::ROLE_SUPER_ADMIN, 403, 'Permission Super Admin tidak dapat diubah.');

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        // Sync permissions: hapus semua yang lama, assign yang baru dipilih
        $role->syncPermissions($validated['permissions'] ?? []);

        // Bersihkan cache Spatie agar perubahan langsung aktif seketika
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()
            ->route('permissions.index')
            ->with('success', "Hak akses (permission) untuk role \"{$role->name}\" berhasil diperbarui.");
    }

    // ── Hapus role ────────────────────────────────────────────────────────────

    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, self::PROTECTED_ROLES)) {
            return back()->with('error', "Role \"{$role->name}\" adalah role standar bawaan sistem dan dilindungi dari penghapusan.");
        }

        $userCount = $role->users()->count();
        if ($userCount > 0) {
            return back()->with('error', "Role \"{$role->name}\" masih digunakan oleh {$userCount} pengguna aktif. Silakan alihkan role pengguna terlebih dahulu.");
        }

        $name = $role->name;
        $role->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()
            ->route('permissions.index')
            ->with('success', "Role \"{$name}\" berhasil dihapus dari sistem.");
    }

    // ── Buat permission baru ──────────────────────────────────────────────────

    public function storePermission(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:permissions,name', 'regex:/^[a-z0-9\-\.]+$/'],
        ], [
            'name.regex' => 'Nama permission hanya boleh huruf kecil, angka, titik, dan strip (contoh: modul.aksi).',
            'name.unique' => 'Permission dengan nama tersebut sudah ada.',
        ]);

        Permission::create(['name' => $validated['name']]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return back()->with('success', "Permission baru \"{$validated['name']}\" berhasil ditambahkan.");
    }

    // ── Hapus permission ──────────────────────────────────────────────────────

    public function destroyPermission(Permission $permission): RedirectResponse
    {
        $usedByRoles = $permission->roles()->pluck('name')->join(', ');

        if ($usedByRoles) {
            return back()->with('error', "Permission \"{$permission->name}\" tidak dapat dihapus karena sedang digunakan oleh role: {$usedByRoles}. Cabut permission dari role tersebut terlebih dahulu.");
        }

        $name = $permission->name;
        $permission->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return back()->with('success', "Permission \"{$name}\" berhasil dihapus.");
    }

    // ── Helper — build grouped permissions ────────────────────────────────────

    /**
     * Mengelompokkan permission berdasarkan PERMISSION_GROUPS secara rapi.
     */
    private function buildPermissionGroups($permissions): array
    {
        $grouped = [];
        $allNamed = $permissions->pluck('name')->toArray();
        $covered = [];

        foreach (self::PERMISSION_GROUPS as $group => $names) {
            $exists = array_filter($names, fn ($n) => in_array($n, $allNamed));
            if (! empty($exists)) {
                $grouped[$group] = array_values($exists);
                $covered = array_merge($covered, $exists);
            }
        }

        // Permission tambahan di luar daftar yang sudah didefinisikan
        $others = array_diff($allNamed, $covered);
        if (! empty($others)) {
            $grouped['Lainnya'] = array_values($others);
        }

        return $grouped;
    }
}
