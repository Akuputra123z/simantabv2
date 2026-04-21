<?php

namespace App\Http\Controllers;

use App\Models\KodeRekomendasi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KodeRekomendasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = KodeRekomendasi::query()
            // Filter aktif
            ->when($request->filled('is_active'), function ($q) use ($request) {
                $q->where('is_active', $request->is_active);
            })
            // Search
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('kode', 'like', '%' . $request->search . '%')
                        ->orWhere('deskripsi', 'like', '%' . $request->search . '%')
                        ->orWhere('kategori', 'like', '%' . $request->search . '%');
                });
            })
            ->orderBy('kode_numerik')
            ->paginate(10)
            ->withQueryString();

        return view('pages.kode-rekomendasi.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.kode-rekomendasi.create');
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode'          => 'required|string|max:10|unique:kode_rekomendasis,kode',
            'kode_numerik'  => 'required|integer|min:1|max:999|unique:kode_rekomendasis,kode_numerik',
            'kategori'      => 'nullable|string|max:100',
            'deskripsi'     => 'nullable|string',
            'is_active'     => 'nullable|boolean',
        ]);

        // Fix checkbox: jika tidak dicentang, set false
        $validated['is_active'] = $request->has('is_active');

        KodeRekomendasi::create($validated);

        return redirect()
            ->route('kode-rekomendasi.index')
            ->with('success', 'Data berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     * INI YANG BARU DITAMBAHKAN
     */
    public function show(KodeRekomendasi $kodeRekomendasi)
    {
        return view('pages.kode-rekomendasi.show', [
            'data' => $kodeRekomendasi
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KodeRekomendasi $kodeRekomendasi)
    {
        return view('pages.kode-rekomendasi.edit', [
            'data' => $kodeRekomendasi
        ]);
    }

    /**
     * Update the specified resource.
     */
    public function update(Request $request, KodeRekomendasi $kodeRekomendasi)
    {
        $validated = $request->validate([
            'kode' => [
                'required',
                'string',
                'max:10',
                Rule::unique('kode_rekomendasis')->ignore($kodeRekomendasi->id),
            ],
            'kode_numerik' => [
                'required',
                'integer',
                Rule::unique('kode_rekomendasis')->ignore($kodeRekomendasi->id),
            ],
            'kategori'  => 'nullable|string|max:100',
            'deskripsi' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $kodeRekomendasi->update($validated);

        return redirect()
            ->route('kode-rekomendasi.index')
            ->with('success', 'Data berhasil diperbarui');
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(KodeRekomendasi $kodeRekomendasi)
    {
        $kodeRekomendasi->delete();

        return redirect()
            ->route('kode-rekomendasi.index')
            ->with('success', 'Data berhasil dihapus');
    }

    /**
     * Toggle status aktif/nonaktif
     */
    public function toggleStatus(KodeRekomendasi $kodeRekomendasi)
    {
        $kodeRekomendasi->update([
            'is_active' => !$kodeRekomendasi->is_active
        ]);

        return redirect()->back()->with('success', 'Status berhasil diubah');
    }
}