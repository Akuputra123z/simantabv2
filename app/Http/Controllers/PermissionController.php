<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * PermissionController
 *
 * Mengelola:
 * - Daftar role & permission (index)
 * - Buat role baru (create/store)
 * - Edit permission yang dimiliki sebuah role (edit/update)
 * - Hapus role (destroy)
 * - Buat permission baru (storePermission)
 * - Hapus permission (destroyPermission)
 */
class PermissionController extends Controller
{
    /**
     * Kelompok permission berdasarkan modul — untuk tampilan grid di blade.
     * Tambahkan modul baru di sini tanpa perlu ubah blade.
     */
    private const PERMISSION_GROUPS = [
        'LHP'               => ['lhp.view', 'lhp.create', 'lhp.edit', 'lhp.delete'],
        'Temuan'            => ['temuan.view', 'temuan.create', 'temuan.edit', 'temuan.delete'],
        'Rekomendasi'       => ['rekomendasi.view', 'rekomendasi.create', 'rekomendasi.edit', 'rekomendasi.delete'],
        'Tindak Lanjut'     => ['tindak-lanjut.view', 'tindak-lanjut.create', 'tindak-lanjut.edit', 'tindak-lanjut.delete'],
        'Cicilan'           => ['cicilan.view', 'cicilan.create', 'cicilan.edit', 'cicilan.delete', 'cicilan.verifikasi'],
        'Audit Assignment'  => ['audit-assignment.view', 'audit-assignment.create', 'audit-assignment.edit', 'audit-assignment.delete'],
        'Master Data'       => ['kode-temuan.manage', 'kode-rekomendasi.manage', 'unit-diperiksa.manage'],
        'User'              => ['user.view', 'user.create', 'user.edit', 'user.delete', 'user.assign-role'],
        'Laporan'           => ['laporan.view', 'laporan.export'],
    ];

    // ── Index — semua role + ringkasan permission ─────────────────────────────

    public function index(): View
    {
        $roles = Role::withCount('permissions', 'users')
            ->orderBy('name')
            ->get();

        $totalPermissions = Permission::count();

        return view('pages.permissions.index', compact('roles', 'totalPermissions'));
    }

    // ── Create role baru ──────────────────────────────────────────────────────

    public function create(): View
    {
        $permissions     = Permission::orderBy('name')->get();
        $permissionGroups = $this->buildPermissionGroups($permissions);

        return view('pages.permissions.create', compact('permissionGroups'));
    }

    // ── Store role baru ───────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:roles,name', 'regex:/^[a-z0-9_]+$/'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ], [
            'name.regex' => 'Nama role hanya boleh huruf kecil, angka, dan underscore.',
        ]);

        $role = Role::create(['name' => $validated['name']]);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()
            ->route('permissions.index')
            ->with('success', "Role \"{$role->name}\" berhasil dibuat.");
    }

    // ── Edit permission sebuah role ───────────────────────────────────────────

    public function edit(Role $role): View
    {
        // Super admin tidak perlu di-edit — dia selalu dapat semua permission
        abort_if(
            $role->name === User::ROLE_SUPER_ADMIN,
            403,
            'Permission super admin tidak dapat diubah.'
        );

        $permissions      = Permission::orderBy('name')->get();
        $rolePermissions  = $role->permissions->pluck('name')->toArray();
        $permissionGroups = $this->buildPermissionGroups($permissions);
        $userCount        = $role->users()->count();

        return view('pages.permissions.edit', compact(
            'role', 'permissionGroups', 'rolePermissions', 'userCount'
        ));
    }

    // ── Update permission sebuah role ─────────────────────────────────────────

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_if($role->name === User::ROLE_SUPER_ADMIN, 403, 'Permission super admin tidak dapat diubah.');

        $validated = $request->validate([
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        // syncPermissions: hapus semua permission lama, assign yang baru
        $role->syncPermissions($validated['permissions'] ?? []);

        // Bersihkan cache Spatie agar perubahan langsung efektif
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()
            ->route('permissions.index')
            ->with('success', "Permission role \"{$role->name}\" berhasil diperbarui.");
    }

    // ── Hapus role ────────────────────────────────────────────────────────────

    public function destroy(Role $role): RedirectResponse
    {
        // Proteksi: role built-in tidak bisa dihapus
        $protected = [
            User::ROLE_SUPER_ADMIN,
            User::ROLE_KEPALA_INSPEKTORAT,
            User::ROLE_KETUA_TIM,
            User::ROLE_ANGGOTA,
            User::ROLE_STAFF_INSPEKTORAT,
        ];

        if (in_array($role->name, $protected)) {
            return back()->with('error', "Role \"{$role->name}\" adalah role bawaan sistem dan tidak dapat dihapus.");
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', "Role \"{$role->name}\" masih digunakan oleh {$role->users()->count()} user. Pindahkan user terlebih dahulu.");
        }

        $name = $role->name;
        $role->delete();

        return redirect()
            ->route('permissions.index')
            ->with('success', "Role \"{$name}\" berhasil dihapus.");
    }

    // ── Buat permission baru ──────────────────────────────────────────────────

    public function storePermission(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:permissions,name', 'regex:/^[a-z0-9\-\.]+$/'],
        ], [
            'name.regex' => 'Nama permission hanya boleh huruf kecil, angka, titik, dan strip.',
        ]);

        Permission::create(['name' => $validated['name']]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return back()->with('success', "Permission \"{$validated['name']}\" berhasil dibuat.");
    }

    // ── Hapus permission ──────────────────────────────────────────────────────

    public function destroyPermission(Permission $permission): RedirectResponse
    {
        // Cek apakah permission masih dipakai oleh role manapun
        $usedByRoles = $permission->roles()->pluck('name')->join(', ');

        if ($usedByRoles) {
            return back()->with('error', "Permission \"{$permission->name}\" masih digunakan oleh role: {$usedByRoles}. Cabut dari role terlebih dahulu.");
        }

        $name = $permission->name;
        $permission->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return back()->with('success', "Permission \"{$name}\" berhasil dihapus.");
    }

    // ── Helper — build grouped permissions ────────────────────────────────────

    /**
     * Kelompokkan permission berdasarkan PERMISSION_GROUPS.
     * Permission yang tidak masuk grup manapun dimasukkan ke grup "Lainnya".
     */
    private function buildPermissionGroups($permissions): array
    {
        $grouped   = [];
        $allNamed  = $permissions->pluck('name')->toArray();
        $covered   = [];

        foreach (self::PERMISSION_GROUPS as $group => $names) {
            $exists = array_filter($names, fn ($n) => in_array($n, $allNamed));
            if (! empty($exists)) {
                $grouped[$group] = array_values($exists);
                $covered         = array_merge($covered, $exists);
            }
        }

        // Permission di luar grup yang sudah didefinisikan
        $others = array_diff($allNamed, $covered);
        if (! empty($others)) {
            $grouped['Lainnya'] = array_values($others);
        }

        return $grouped;
    }
}