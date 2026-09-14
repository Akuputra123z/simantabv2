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
        $isOpd = str_contains($request->route()->getName(), '.opd.') || $request->input('role') === User::ROLE_OPD;

        try {
            $validationRules = array_merge(
                $this->userService->rules(),
                [
                    'password'     => $this->userService->passwordRules(true),
                    'opd_unit_ids' => $isOpd ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
                ]
            );

            $validationMessages = [
                'name.required'         => 'Nama lengkap wajib diisi.',
                'email.required'        => 'Alamat email wajib diisi.',
                'email.email'           => 'Format email tidak valid (contoh: user@domain.com).',
                'email.unique'          => 'Alamat email ini sudah terdaftar pada sistem.',
                'nip.unique'            => 'NIP ini sudah terdaftar pada sistem.',
                'role.required'         => 'Role akses wajib dipilih.',
                'role.exists'           => 'Role akses yang dipilih tidak terdaftar.',
                'password.required'     => 'Password wajib diisi.',
                'password.min'          => 'Password minimal harus 8 karakter.',
                'jenis_kelamin.required'=> 'Jenis kelamin wajib dipilih (Laki-laki atau Perempuan).',
                'jenis_kelamin.in'      => 'Pilihan jenis kelamin tidak valid (pilih Laki-laki atau Perempuan).',
                'opd_unit_ids.required' => 'Pilih minimal 1 Unit OPD yang dapat diakses oleh user ini.',
                'opd_unit_ids.min'      => 'Pilih minimal 1 Unit OPD yang dapat diakses oleh user ini.',
            ];

            $data = $request->validate($validationRules, $validationMessages);

            $role = $data['role'] ?? User::ROLE_OPD;

            $user = $this->userService->create(
                $data,
                $role,
                $request->has('opd_unit_ids') ? (array) $request->opd_unit_ids : null,
            );

            $redirectRoute = $user->hasRole(User::ROLE_OPD)
                ? 'pegawai.opd.index'
                : 'pegawai.inspektorat.index';

            return redirect()
                ->route($redirectRoute)
                ->with('success', "User {$user->name} berhasil ditambahkan.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menambahkan user: ' . $e->getMessage());
        }
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
        $isOpd = $user->hasRole(User::ROLE_OPD) || $request->input('role') === User::ROLE_OPD;

        try {
            $validationRules = array_merge(
                $this->userService->rules($user),
                [
                    'password'     => $this->userService->passwordRules(false),
                    'opd_unit_ids' => $isOpd ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
                ]
            );

            $validationMessages = [
                'name.required'         => 'Nama lengkap wajib diisi.',
                'email.required'        => 'Alamat email wajib diisi.',
                'email.email'           => 'Format email tidak valid.',
                'email.unique'          => 'Alamat email ini sudah terdaftar pada sistem.',
                'nip.unique'            => 'NIP ini sudah terdaftar pada sistem.',
                'role.required'         => 'Role akses wajib dipilih.',
                'opd_unit_ids.required' => 'Pilih minimal 1 Unit OPD yang dapat diakses oleh user ini.',
                'opd_unit_ids.min'      => 'Pilih minimal 1 Unit OPD yang dapat diakses oleh user ini.',
            ];

            $data = $request->validate($validationRules, $validationMessages);

            if ($user->id === auth()->id() && ! $request->boolean('is_active')) {
                return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
            }

            $role = $data['role'] ?? User::ROLE_OPD;

            $this->userService->update(
                $user,
                $data,
                $role,
                $request->has('opd_unit_ids') ? (array) $request->opd_unit_ids : null,
            );

            $redirectRoute = $user->hasRole(User::ROLE_OPD)
                ? 'pegawai.opd.index'
                : 'pegawai.inspektorat.index';

            return redirect()
                ->route($redirectRoute)
                ->with('success', "User {$user->name} berhasil diperbarui.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui user: ' . $e->getMessage());
        }
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
