<?php

namespace App\Http\Controllers;

use App\Models\KodeTemuan;
use App\Models\KodeRekomendasi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        if ($request->filled('kode')) {
            KodeTemuan::onlyTrashed()->where('kode', trim($request->kode))->forceDelete();
        }

        $validated = $request->validate([
            'kode'               => ['required', 'string', 'max:50', Rule::unique('kode_temuans', 'kode')->whereNull('deleted_at')],
            'kode_numerik'       => 'required',
            'kel'                => 'required|integer',
            'sub_kel'            => 'required|integer',
            'jenis'              => 'required|integer',
            'kelompok'           => 'required|string|max:150',
            'sub_kelompok'       => 'required|string|max:150',
            'deskripsi'          => 'required',
            'alternatif_rekom'   => 'nullable|array',
            'alternatif_rekom.*' => 'string',
        ], [
            'kode.unique' => 'Kode temuan ini sudah digunakan.',
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
        if ($request->filled('kode')) {
            KodeTemuan::onlyTrashed()->where('kode', trim($request->kode))->where('id', '!=', $kodeTemuan->id)->forceDelete();
        }

        $validated = $request->validate([
            'kode'               => ['required', 'string', 'max:50', Rule::unique('kode_temuans', 'kode')->ignore($kodeTemuan->id)->whereNull('deleted_at')],
            'kode_numerik'       => 'required',
            'kel'                => 'required|integer',
            'sub_kel'            => 'required|integer',
            'jenis'              => 'required|integer',
            'kelompok'           => 'required|string|max:150',
            'sub_kelompok'       => 'required|string|max:150',
            'deskripsi'          => 'required',
            'alternatif_rekom'   => 'nullable|array',
            'alternatif_rekom.*' => 'string',
        ], [
            'kode.unique' => 'Kode temuan ini sudah digunakan oleh kode lain.',
        ]);

        $kodeTemuan->update($validated);
        return redirect()->route('kode-temuan.index')->with('success', 'Data berhasil diperbarui');
    }

    public function show(KodeTemuan $kodeTemuan)
    {
        $rekomendasiTerkait = KodeRekomendasi::whereIn('kode', $kodeTemuan->alternatif_rekom ?? [])
            ->active()
            ->get();

        return view('pages.kode-temuan.show', [
            'data' => $kodeTemuan,
            'rekomendasiTerkait' => $rekomendasiTerkait
        ]);
    }

    public function destroy(KodeTemuan $kodeTemuan)
    {
        if (\App\Models\Temuan::where('kode_temuan_id', $kodeTemuan->id)->exists()) {
            return back()->with('error', 'Kode temuan ini tidak dapat dihapus karena sudah digunakan dalam data temuan LHP.');
        }

        $kodeTemuan->forceDelete();
        return back()->with('success', 'Kode temuan berhasil dihapus permanen.');
    }
}