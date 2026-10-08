<?php

namespace App\Http\Controllers;

use App\Models\DasarPeraturan;
use Illuminate\Http\Request;

class DasarPeraturanController extends Controller
{
    public function index(Request $request)
    {
        $query = DasarPeraturan::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nomor', 'like', "%{$search}%")
                    ->orWhere('tahun', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'aktif') {
                $query->where('is_active', true);
            } elseif ($request->status === 'nonaktif') {
                $query->where('is_active', false);
            }
        }

        $dasarPeraturans = $query->orderBy('urutan', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(15)
            ->withQueryString();

        $totalAktif = DasarPeraturan::where('is_active', true)->count();
        $totalNonaktif = DasarPeraturan::where('is_active', false)->count();

        return view('pages.master.dasar-peraturan.index', compact(
            'dasarPeraturans',
            'totalAktif',
            'totalNonaktif'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string',
            'nomor' => 'nullable|string|max:150',
            'tahun' => 'nullable|string|max:10',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ], [
            'nama.required' => 'Isi/nama dasar peraturan wajib diisi.',
        ]);

        if (! isset($validated['urutan']) || $validated['urutan'] === null) {
            $validated['urutan'] = ((int) DasarPeraturan::max('urutan')) + 1;
        }

        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : true;

        DasarPeraturan::create($validated);

        return redirect()->route('dasar-peraturan.index')->with('success', 'Dasar peraturan berhasil ditambahkan.');
    }

    public function update(Request $request, DasarPeraturan $dasarPeraturan)
    {
        $validated = $request->validate([
            'nama' => 'required|string',
            'nomor' => 'nullable|string|max:150',
            'tahun' => 'nullable|string|max:10',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ], [
            'nama.required' => 'Isi/nama dasar peraturan wajib diisi.',
        ]);

        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : false;

        $dasarPeraturan->update($validated);

        return redirect()->route('dasar-peraturan.index')->with('success', 'Dasar peraturan berhasil diperbarui.');
    }

    public function destroy(DasarPeraturan $dasarPeraturan)
    {
        $dasarPeraturan->delete();

        return redirect()->route('dasar-peraturan.index')->with('success', 'Dasar peraturan berhasil dihapus.');
    }

    public function toggle(DasarPeraturan $dasarPeraturan)
    {
        $dasarPeraturan->update([
            'is_active' => ! $dasarPeraturan->is_active,
        ]);

        $status = $dasarPeraturan->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('dasar-peraturan.index')->with('success', "Dasar peraturan berhasil {$status}.");
    }
}
