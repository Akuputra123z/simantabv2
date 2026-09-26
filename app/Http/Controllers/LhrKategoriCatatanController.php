<?php

namespace App\Http\Controllers;

use App\Models\Lhr\LhrKategoriCatatan;
use Illuminate\Http\Request;

class LhrKategoriCatatanController extends Controller
{
    public function index()
    {
        $kategoris = LhrKategoriCatatan::withCount('catatans')->latest()->paginate(15);

        return view('pages.master.lhr-kategori-catatan.index', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:30|unique:lhr_kategori_catatans,kode',
            'nama' => 'required|string|max:150',
        ]);

        LhrKategoriCatatan::create(array_merge($validated, ['aktif' => true]));

        return back()->with('success', 'Kategori catatan reviu berhasil ditambahkan.');
    }

    public function update(Request $request, LhrKategoriCatatan $lhrKategoriCatatan)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'aktif' => 'boolean',
        ]);

        $lhrKategoriCatatan->update($validated);

        return back()->with('success', 'Kategori catatan reviu berhasil diperbarui.');
    }

    public function destroy(LhrKategoriCatatan $lhrKategoriCatatan)
    {
        $lhrKategoriCatatan->delete();

        return back()->with('success', 'Kategori catatan reviu berhasil dihapus.');
    }
}
