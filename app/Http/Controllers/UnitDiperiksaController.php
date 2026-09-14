<?php

namespace App\Http\Controllers;

use App\Exports\TemplateUnitDiperiksaExport;
use App\Imports\UnitDiperiksaImport;
use App\Models\UnitDiperiksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class UnitDiperiksaController extends Controller
{
    public function index(Request $request)
    {
        $query = UnitDiperiksa::query();

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [5, 8, 10, 20, 25, 50])) {
            $perPage = 10;
        }

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
            ->paginate($perPage)
            ->withQueryString();

        // ✅ ambil list kecamatan unik dari database via model helper
        $kecamatanList = UnitDiperiksa::getKecamatanList();

        return view('pages.unit-diperiksa.index', compact('data', 'kecamatanList'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ], [
            'file.required' => 'Pilih file Excel atau CSV untuk diimpor.',
            'file.mimes'    => 'Format file harus berupa .xlsx, .xls, atau .csv.',
            'file.max'      => 'Ukuran file tidak boleh melebihi 10MB.',
        ]);

        try {
            $import = new UnitDiperiksaImport();
            Excel::import($import, $request->file('file'));

            $msg = "Proses impor selesai! {$import->importedCount} unit baru ditambahkan";
            if ($import->updatedCount > 0) {
                $msg .= ", {$import->updatedCount} unit diperbarui";
            }
            if ($import->skippedCount > 0) {
                $msg .= ", {$import->skippedCount} baris dilewati";
            }
            $msg .= ".";

            return redirect()
                ->route('unit-diperiksa.index')
                ->with('success', $msg);
        } catch (\Exception $e) {
            Log::error('Import UnitDiperiksa Error: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Gagal mengimpor file: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new TemplateUnitDiperiksaExport, 'template_import_unit_diperiksa.xlsx');
    }

    public function create()
    {
        $kategoriOptions = ['BUMD', 'Sekolah', 'OPD', 'Desa', 'BLUD'];
        $kecamatanList = UnitDiperiksa::getKecamatanList();
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
        $kecamatanList = UnitDiperiksa::getKecamatanList();
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

    public function bulkDelete(Request $request)
    {
        if (!$request->ids || !is_array($request->ids)) {
            return back()->with('error', 'Pilih minimal satu data unit yang akan dihapus.');
        }

        $count = UnitDiperiksa::whereIn('id', $request->ids)->delete();

        return redirect()->route('unit-diperiksa.index')
            ->with('success', "{$count} data unit diperiksa berhasil dihapus.");
    }
}