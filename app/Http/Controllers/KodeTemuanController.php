<?php

namespace App\Http\Controllers;

use App\Models\KodeTemuan;
use Illuminate\Http\Request;

class KodeTemuanController extends Controller
{
    public function index(Request $request)
    {
        $query = KodeTemuan::query();

        if ($request->search) {
            $query->where('kode', 'like', "%{$request->search}%")
                  ->orWhere('deskripsi', 'like', "%{$request->search}%")
                  ->orWhere('kelompok', 'like', "%{$request->search}%");
        }

        $data = $query->latest()->paginate(10);
        return view('pages.kode-temuan.index', compact('data'));
    }

    public function create()
    {
        return view('pages.kode-temuan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|unique:kode_temuans,kode',
            'kode_numerik' => 'required',
            'kel' => 'required|integer',
            'sub_kel' => 'required|integer',
            'jenis' => 'required|integer',
            'kelompok' => 'required|string|max:150',
            'sub_kelompok' => 'required|string|max:150',
            'deskripsi' => 'required',
            'alternatif_rekom' => 'nullable|array',
            'alternatif_rekom.*' => 'integer',
        ]);

        KodeTemuan::create($validated);
        return redirect()->route('kode-temuan.index')->with('success', 'Data berhasil disimpan');
    }

    public function edit(KodeTemuan $kodeTemuan)
    {
        return view('pages.kode-temuan.edit', ['data' => $kodeTemuan]);
    }

    public function update(Request $request, KodeTemuan $kodeTemuan)
    {
        $validated = $request->validate([
            'kode' => 'required|unique:kode_temuans,kode,' . $kodeTemuan->id,
            'kode_numerik' => 'required',
            'kel' => 'required|integer',
            'sub_kel' => 'required|integer', // Tambahkan ini
            'jenis' => 'required|integer',   // Tambahkan ini
            'kelompok' => 'required|string|max:150',
            'sub_kelompok' => 'required|string|max:150', // Tambahkan ini
            'deskripsi' => 'required',
        ]);

        $kodeTemuan->update($validated);
        return redirect()->route('kode-temuan.index')->with('success', 'Data berhasil diperbarui');
    }
    public function show(KodeTemuan $kodeTemuan)
{
   // Ambil detail rekomendasi berdasarkan kode_numerik yang tersimpan di array alternatif_rekom
    // Kita asumsikan alternatif_rekom berisi [1, 2, 5] yang merujuk ke kode_numerik di tabel rekomendasi
    $rekomendasiTerkait = \App\Models\KodeRekomendasi::whereIn('kode_numerik', $kodeTemuan->alternatif_rekom ?? [])
        ->where('is_active', true)
        ->get();

    return view('pages.kode-temuan.show', [
        'data' => $kodeTemuan,
        'rekomendasiTerkait' => $rekomendasiTerkait
    ]);
}

    public function destroy(KodeTemuan $kodeTemuan)
    {
        $kodeTemuan->delete();
        return back()->with('success', 'Data berhasil dihapus');
    }
}
