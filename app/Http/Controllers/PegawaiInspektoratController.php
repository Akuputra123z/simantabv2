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
            ->when($request->filled('unit_kerja'), fn ($q) => $q->where('unit_kerja', $request->unit_kerja)
            )
            ->when($request->filled('jabatan'), function ($q) use ($request) {
                $jabatan = $request->jabatan;
                if ($jabatan === 'Auditor') {
                    $q->where('jabatan', 'like', '%Auditor%');
                } elseif ($jabatan === 'Pengawas') {
                    $q->where(fn ($sq) => $sq->where('jabatan', 'like', '%P2UPD%')->orWhere('jabatan', 'like', '%Pengawas%'));
                } elseif ($jabatan === 'Staf') {
                    $q->where(fn ($sq) => $sq->where('jabatan', 'like', '%Kasubbag%')
                        ->orWhere('jabatan', 'like', '%PPPK%')
                        ->orWhere('jabatan', 'like', '%Staf%')
                        ->orWhere('jabatan', 'like', '%Operator%')
                        ->orWhere('jabatan', 'like', '%Pengadministrasi%')
                        ->orWhere('jabatan', 'like', '%Pengelola%')
                        ->orWhere('jabatan', 'like', '%Penata Layanan%')
                        ->orWhere('jabatan', 'like', '%Pranata%')
                        ->orWhere('jabatan', 'like', '%Arsiparis%')
                    );
                } elseif ($jabatan === 'Inspektur') {
                    $q->where('jabatan', 'like', '%Inspektur%')->where('jabatan', 'not like', '%Irban%');
                } elseif ($jabatan === 'Sekdin') {
                    $q->where(fn ($sq) => $sq->where('jabatan', 'like', '%Sekretaris%')->orWhere('jabatan', 'like', '%Sekdin%'));
                } elseif ($jabatan === 'Irbansus') {
                    $q->where(fn ($sq) => $sq->where('jabatan', 'like', '%IRBANSUS%')->orWhere('jabatan', 'like', '%Irbansus%'));
                } else {
                    $q->where('jabatan', 'like', "%{$jabatan}%");
                }
            })
            ->latest()
            ->paginate(min((int) $request->per_page, 100) ?: 15)
            ->withQueryString();

        $unitKerjaOptions = User::UNIT_KERJA_OPTIONS;
        $jabatanOptions = User::JABATAN_OPTIONS;

        $stats = [
            'total' => User::inspektorat()->count(),
            'aktif' => User::inspektorat()->where('is_active', true)->count(),
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
