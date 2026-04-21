<?php

namespace App\Http\Controllers;

use App\Models\UnitDiperiksa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitDiperiksaController extends Controller
{
    public function index(Request $request)
    {
        $data = UnitDiperiksa::query()
            ->when($request->search, function ($q) use ($request) {
                $q->where('nama_unit', 'like', '%' . $request->search . '%')
                  ->orWhere('nama_kecamatan', 'like', '%' . $request->search . '%')
                  ->orWhere('alamat', 'like', '%' . $request->search . '%');
            })
            ->when($request->kategori, function ($q) use ($request) {
                $q->where('kategori', $request->kategori);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.unit-diperiksa.index', compact('data'));
    }

    public function create()
    {
        $kategoriOptions = ['SKPD', 'Sekolah', 'Puskesmas', 'Desa', 'BLUD'];
        return view('pages.unit-diperiksa.create', compact('kategoriOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_unit'      => 'required|string|max:200',
            'kategori'       => 'required|string|max:50',
            'nama_kecamatan' => 'nullable|string|max:100',
            'alamat'         => 'nullable|string|max:500',
            'telepon'        => 'nullable|string|max:20',
            'keterangan'     => 'nullable|string',
        ]);

        UnitDiperiksa::create($validated);

        return redirect()
            ->route('unit-diperiksa.index')
            ->with('success', 'Unit diperiksa berhasil ditambahkan.');
    }

    public function show(UnitDiperiksa $unitDiperiksa)
    {
        return view('pages.unit-diperiksa.show', ['data' => $unitDiperiksa]);
    }

    public function edit(UnitDiperiksa $unitDiperiksa)
    {
        $kategoriOptions = ['SKPD', 'Sekolah', 'Puskesmas', 'Desa', 'BLUD'];
        return view('pages.unit-diperiksa.edit', [
            'data' => $unitDiperiksa,
            'kategoriOptions' => $kategoriOptions
        ]);
    }

    public function update(Request $request, UnitDiperiksa $unitDiperiksa)
    {
        $validated = $request->validate([
            'nama_unit'      => 'required|string|max:200',
            'kategori'       => 'required|string|max:50',
            'nama_kecamatan' => 'nullable|string|max:100',
            'alamat'         => 'nullable|string|max:500',
            'telepon'        => 'nullable|string|max:20',
            'keterangan'     => 'nullable|string',
        ]);

        $unitDiperiksa->update($validated);

        return redirect()
            ->route('unit-diperiksa.index')
            ->with('success', 'Data unit berhasil diperbarui.');
    }

    public function destroy(UnitDiperiksa $unitDiperiksa)
    {
        $unitDiperiksa->delete();
        return redirect()->back()->with('success', 'Unit berhasil dihapus.');
    }
}