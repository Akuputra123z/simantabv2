<?php

namespace App\Http\Controllers;

use App\Models\KodeTemuan;
use App\Models\KodeRekomendasi;
use Illuminate\Http\Request;

class KodeTemuanController extends Controller
{
    public function index(Request $request)
    {
        $query = KodeTemuan::query();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('kode', 'like', "%{$request->search}%")
                  ->orWhere('deskripsi', 'like', "%{$request->search}%")
                  ->orWhere('kelompok', 'like', "%{$request->search}%");
            });
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
            'kode'               => 'required|unique:kode_temuans,kode',
            'kode_numerik'       => 'required',
            'kel'                => 'required|integer',
            'sub_kel'            => 'required|integer',
            'jenis'              => 'required|integer',
            'kelompok'           => 'required|string|max:150',
            'sub_kelompok'       => 'required|string|max:150',
            'deskripsi'          => 'required',
            'alternatif_rekom'   => 'nullable|array',
            'alternatif_rekom.*' => 'string', // Ubah ke string jika kode berupa "01", "02"
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
            'kode'               => 'required|unique:kode_temuans,kode,' . $kodeTemuan->id,
            'kode_numerik'       => 'required',
            'kel'                => 'required|integer',
            'sub_kel'            => 'required|integer',
            'jenis'              => 'required|integer',
            'kelompok'           => 'required|string|max:150',
            'sub_kelompok'       => 'required|string|max:150',
            'deskripsi'          => 'required',
            'alternatif_rekom'   => 'nullable|array',
            'alternatif_rekom.*' => 'string',
        ]);

        $kodeTemuan->update($validated);
        return redirect()->route('kode-temuan.index')->with('success', 'Data berhasil diperbarui');
    }

    public function show(KodeTemuan $kodeTemuan)
    {
        // Ambil detail rekomendasi berdasarkan kode yang tersimpan di array alternatif_rekom
        // Pastikan model KodeRekomendasi sudah benar
        $rekomendasiTerkait = KodeRekomendasi::whereIn('kode', $kodeTemuan->alternatif_rekom ?? [])
            ->active() // Menggunakan scope active() jika tersedia di model
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