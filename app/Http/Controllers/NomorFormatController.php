<?php

namespace App\Http\Controllers;

use App\Models\NomorFormat;
use App\Models\NomorSequence;
use Illuminate\Http\Request;

class NomorFormatController extends Controller
{
    public function index()
    {
        $formats = NomorFormat::latest()->get();
        $sequences = NomorSequence::latest()->get();

        return view('pages.master.nomor-format.index', compact('formats', 'sequences'));
    }

    public function update(Request $request, NomorFormat $nomorFormat)
    {
        $validated = $request->validate([
            'pola' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:255',
            'aktif' => 'boolean',
        ]);

        $nomorFormat->update($validated);

        return back()->with('success', "Pola penomoran '{$nomorFormat->jenis}' berhasil diperbarui.");
    }
}
