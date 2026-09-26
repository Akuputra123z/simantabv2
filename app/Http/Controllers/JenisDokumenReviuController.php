<?php

namespace App\Http\Controllers;

use App\Models\Lhr\JenisDokumenReviu;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class JenisDokumenReviuController extends Controller
{
    public function index()
    {
        $jenisList = JenisDokumenReviu::withCount('templates')->latest()->paginate(10);

        return view('pages.master.jenis-dokumen-reviu.index', compact('jenisList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:30|unique:jenis_dokumen_reviu,kode',
            'nama' => 'required|string|max:150',
            'gaya_penomoran_bagian' => 'required|in:alfabet,romawi',
            'tata_letak_hasil' => 'required|in:tabel_dua_kolom,naratif',
            'sla_tindak_lanjut_hari' => 'nullable|integer|min:1|max:365',
        ]);

        JenisDokumenReviu::create(array_merge($validated, [
            'uuid' => (string) Str::uuid(),
            'aktif' => true,
        ]));

        return back()->with('success', 'Jenis dokumen reviu berhasil ditambahkan.');
    }

    public function update(Request $request, JenisDokumenReviu $jenisDokumenReviu)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'gaya_penomoran_bagian' => 'required|in:alfabet,romawi',
            'tata_letak_hasil' => 'required|in:tabel_dua_kolom,naratif',
            'sla_tindak_lanjut_hari' => 'nullable|integer|min:1|max:365',
            'aktif' => 'boolean',
        ]);

        $jenisDokumenReviu->update($validated);

        return back()->with('success', 'Jenis dokumen reviu berhasil diperbarui.');
    }

    public function destroy(JenisDokumenReviu $jenisDokumenReviu)
    {
        $jenisDokumenReviu->delete();

        return back()->with('success', 'Jenis dokumen reviu berhasil dihapus.');
    }
}
