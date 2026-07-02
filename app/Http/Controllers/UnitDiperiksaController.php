<?php

namespace App\Http\Controllers;

use App\Models\UnitDiperiksa;
use Illuminate\Http\Request;

class UnitDiperiksaController extends Controller
{
   public function index(Request $request)
{
    $query = UnitDiperiksa::query();

    $data = $query
        ->when($request->search, function ($q) use ($request) {
            $q->where(function ($sub) use ($request) {
                $sub->where('nama_unit', 'like', '%' . $request->search . '%')
                    ->orWhere('nama_kecamatan', 'like', '%' . $request->search . '%')
                    ->orWhere('alamat', 'like', '%' . $request->search . '%');
            });
        })
        ->when($request->kategori, function ($q) use ($request) {
            $q->where('kategori', $request->kategori);
        })
        ->when($request->kecamatan, function ($q) use ($request) {
            $q->where('nama_kecamatan', $request->kecamatan);
        })
        ->latest()
        ->paginate(10)
        ->withQueryString();

    // ✅ ambil list kecamatan unik dari database
    $kecamatanList = UnitDiperiksa::select('nama_kecamatan')
        ->whereNotNull('nama_kecamatan')
        ->distinct()
        ->orderBy('nama_kecamatan')
        ->pluck('nama_kecamatan');

    return view('pages.unit-diperiksa.index', compact('data', 'kecamatanList'));
}
    public function create()
    {
        $kategoriOptions = ['BUMD', 'Sekolah', 'OPD', 'Desa', 'BLUD'];
        $kecamatanList = UnitDiperiksa::select('nama_kecamatan')
            ->whereNotNull('nama_kecamatan')
            ->distinct()
            ->orderBy('nama_kecamatan')
            ->pluck('nama_kecamatan');
        return view('pages.unit-diperiksa.create', compact('kategoriOptions', 'kecamatanList'));
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
        $kategoriOptions = ['BUMD', 'Sekolah', 'OPD', 'Desa', 'BLUD'];
        $kecamatanList = UnitDiperiksa::select('nama_kecamatan')
            ->whereNotNull('nama_kecamatan')
            ->distinct()
            ->orderBy('nama_kecamatan')
            ->pluck('nama_kecamatan');
        return view('pages.unit-diperiksa.edit', [
            'data' => $unitDiperiksa,
            'kategoriOptions' => $kategoriOptions,
            'kecamatanList' => $kecamatanList,
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