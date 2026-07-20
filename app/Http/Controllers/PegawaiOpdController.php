<?php

namespace App\Http\Controllers;

use App\Models\UnitDiperiksa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PegawaiOpdController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::opd()
            ->with('roles', 'opdUnits:id,nama_unit')
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where('name', 'like', "%{$request->search}%")
                          ->orWhere('email', 'like', "%{$request->search}%")
                          ->orWhere('nip', 'like', "%{$request->search}%");
                });
            })
            ->when($request->filled('unit_opd'), fn ($q) =>
                $q->whereHas('opdUnits', fn ($q2) =>
                    $q2->where('unit_diperiksa_id', $request->unit_opd)
                )
            )
            ->latest()
            ->paginate(min((int) $request->per_page ?: 15, 100))
            ->withQueryString();

        $stats = [
            'total'    => User::opd()->count(),
            'aktif'    => User::opd()->where('is_active', true)->count(),
            'nonaktif' => User::opd()->where('is_active', false)->count(),
        ];

        $opdUnitOptions = UnitDiperiksa::orderBy('nama_unit')->get(['id', 'nama_unit']);

        return view('pegawai.opd.index', compact('users', 'stats', 'opdUnitOptions'));
    }

    public function show(User $user): View
    {
        $user->load('roles', 'opdUnits');
        return view('pegawai.opd.show', compact('user'));
    }
}
