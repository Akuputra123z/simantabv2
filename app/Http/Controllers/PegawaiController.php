<?php

namespace App\Http\Controllers;

use App\Models\UnitDiperiksa;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class PegawaiController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function create(Request $request): View
    {
        $type = str_contains($request->route()->getName(), '.opd.') ? 'opd' : 'inspektorat';

        $roles = Role::orderBy('name')->get(['id', 'name']);
        $unitKerjaOptions = User::UNIT_KERJA_OPTIONS;
        $jabatanOptions = User::JABATAN_OPTIONS;
        $opdUnits = UnitDiperiksa::orderBy('nama_unit')->get(['id', 'nama_unit', 'nama_kecamatan']);
        $defaultRole = $type === 'opd' ? User::ROLE_OPD : null;

        return view('pegawai.create', compact('type', 'roles', 'unitKerjaOptions', 'jabatanOptions', 'opdUnits', 'defaultRole'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(array_merge(
            $this->userService->rules(),
            ['password' => $this->userService->passwordRules(true)],
        ));

        $user = $this->userService->create(
            $data,
            $data['role'],
            $request->has('opd_unit_ids') ? (array) $request->opd_unit_ids : null,
        );

        $redirectRoute = $user->hasRole(User::ROLE_OPD)
            ? 'pegawai.opd.index'
            : 'pegawai.inspektorat.index';

        return redirect()
            ->route($redirectRoute)
            ->with('success', "User {$user->name} berhasil ditambahkan.");
    }

    public function edit(User $user): View
    {
        $user->load('roles', 'opdUnits');

        $type = $user->hasRole(User::ROLE_OPD) ? 'opd' : 'inspektorat';
        $roles = Role::orderBy('name')->get(['id', 'name']);
        $unitKerjaOptions = User::UNIT_KERJA_OPTIONS;
        $jabatanOptions = User::JABATAN_OPTIONS;
        $opdUnits = UnitDiperiksa::orderBy('nama_unit')->get(['id', 'nama_unit', 'nama_kecamatan']);

        $defaultRole = $type === 'opd' ? User::ROLE_OPD : null;

        return view('pegawai.edit', compact('user', 'type', 'roles', 'unitKerjaOptions', 'jabatanOptions', 'opdUnits', 'defaultRole'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(array_merge(
            $this->userService->rules($user),
            ['password' => $this->userService->passwordRules(false)],
        ));

        if ($user->id === auth()->id() && ! $request->boolean('is_active')) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $this->userService->update(
            $user,
            $data,
            $data['role'],
            $request->has('opd_unit_ids') ? (array) $request->opd_unit_ids : null,
        );

        $redirectRoute = $user->hasRole(User::ROLE_OPD)
            ? 'pegawai.opd.index'
            : 'pegawai.inspektorat.index';

        return redirect()
            ->route($redirectRoute)
            ->with('success', "User {$user->name} berhasil diperbarui.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        if ($user->hasRole(User::ROLE_SUPER_ADMIN) &&
            User::role(User::ROLE_SUPER_ADMIN)->count() <= 1) {
            return back()->with('error', 'Tidak dapat menghapus satu-satunya super admin.');
        }

        $isOpd = $user->hasRole(User::ROLE_OPD);
        $name = $user->name;
        $user->delete();

        $redirectRoute = $isOpd ? 'pegawai.opd.index' : 'pegawai.inspektorat.index';

        return redirect()
            ->route($redirectRoute)
            ->with('success', "User {$name} berhasil dihapus.");
    }

    public function toggleActive(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'Status user berhasil diperbarui.');
    }
}
