<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PegawaiInspektoratController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::inspektorat()
            ->with('roles')
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where('name', 'like', "%{$request->search}%")
                          ->orWhere('email', 'like', "%{$request->search}%")
                          ->orWhere('nip', 'like', "%{$request->search}%");
                });
            })
            ->when($request->filled('unit_kerja'), fn ($q) =>
                $q->where('unit_kerja', $request->unit_kerja)
            )
            ->when($request->filled('jabatan'), fn ($q) =>
                $q->where('jabatan', $request->jabatan)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $unitKerjaOptions = User::UNIT_KERJA_OPTIONS;
        $jabatanOptions = User::JABATAN_OPTIONS;

        $stats = [
            'total'    => User::inspektorat()->count(),
            'aktif'    => User::inspektorat()->where('is_active', true)->count(),
            'nonaktif' => User::inspektorat()->where('is_active', false)->count(),
        ];

        return view('pegawai.inspektorat.index', compact('users', 'unitKerjaOptions', 'jabatanOptions', 'stats'));
    }

    public function show(User $user): View
    {
        $user->load('roles');
        return view('pegawai.inspektorat.show', compact('user'));
    }
}
