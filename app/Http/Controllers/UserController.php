<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    // ── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $users = User::query()
            ->with('roles')
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where('name', 'like', "%{$request->search}%")
                          ->orWhere('email', 'like', "%{$request->search}%")
                          ->orWhere('nip', 'like', "%{$request->search}%");
                });
            })
            ->when($request->filled('role'), fn ($q) =>
                $q->role($request->role)
            )
            ->when($request->filled('status'), fn ($q) =>
                $q->where('is_active', $request->status === 'aktif')
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $roles = Role::orderBy('name')->pluck('name');

        // 🔥 lebih efisien (1 query)
        $stats = User::selectRaw("
                COUNT(*) as total,
                SUM(is_active = 1) as aktif,
                SUM(is_active = 0) as nonaktif
            ")->first();

        return view('users.index', [
            'users' => $users,
            'roles' => $roles,
            'stats' => [
                'total' => $stats->total ?? 0,
                'aktif' => $stats->aktif ?? 0,
                'nonaktif' => $stats->nonaktif ?? 0,
            ]
        ]);
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function create(): View
    {
        $roles = Role::orderBy('name')->get(['id', 'name']);
        return view('users.create', compact('roles'));
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'nip'      => ['nullable', 'string', 'max:30', 'unique:users,nip'],
            'jabatan'  => ['nullable', 'string', 'max:100'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['required', Password::min(8)->letters()->numbers()],
            'role'     => ['required', 'string', 'exists:roles,name'],
            'is_active' => ['boolean'],
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'nip'       => $validated['nip'] ?? null,
            'jabatan'   => $validated['jabatan'] ?? null,
            'phone'     => $validated['phone'] ?? null,
            'password'  => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->assignRole($validated['role']);

        return redirect()
            ->route('users.index')
            ->with('success', "User {$user->name} berhasil ditambahkan.");
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(User $user): View
    {
        $user->load('roles');
        return view('users.show', compact('user'));
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function edit(User $user): View
    {
        $user->load('roles');
        $roles = Role::orderBy('name')->get(['id', 'name']);
        return view('users.edit', compact('user', 'roles'));
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'nip'      => ['nullable', 'string', 'max:30', Rule::unique('users')->ignore($user->id)],
            'jabatan'  => ['nullable', 'string', 'max:100'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', Password::min(8)->letters()->numbers()],
            'role'     => ['required', 'string', 'exists:roles,name'],
            'is_active' => ['boolean'],
        ]);

        if ($user->id === auth()->id() && ! $request->boolean('is_active')) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'nip'       => $validated['nip'] ?? null,
            'jabatan'   => $validated['jabatan'] ?? null,
            'phone'     => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (! empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password'])
            ]);
        }

        $user->syncRoles([$validated['role']]);

        return redirect()
            ->route('users.index')
            ->with('success', "User {$user->name} berhasil diperbarui.");
    }

    // ── Toggle Active ─────────────────────────────────────────────────────────

    public function toggleActive(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update([
            'is_active' => ! $user->is_active
        ]);

        return back()->with('success', 'Status user berhasil diperbarui.');
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        if ($user->hasRole(User::ROLE_SUPER_ADMIN) &&
            User::role(User::ROLE_SUPER_ADMIN)->count() <= 1) {
            return back()->with('error', 'Tidak dapat menghapus satu-satunya super admin.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', "User {$name} berhasil dihapus.");
    }
}