<?php

namespace App\Imports;

use App\Models\UnitDiperiksa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UnitDiperiksaImport implements ToCollection, WithHeadingRow
{
    public int $importedCount = 0;
    public int $updatedCount = 0;
    public int $skippedCount = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Variation handling for column headers
            $namaUnit = $row['nama_unit'] 
                ?? $row['nama'] 
                ?? $row['unit'] 
                ?? $row['nama_opd'] 
                ?? $row['nama_unit_diperiksa'] 
                ?? $row['objek_pengawasan'] 
                ?? null;

            if (!$namaUnit || trim((string)$namaUnit) === '') {
                $this->skippedCount++;
                continue;
            }

            $namaUnit = trim((string)$namaUnit);

            $rawKategori = $row['kategori'] ?? $row['kat'] ?? $row['jenis'] ?? null;
            $kategori    = $this->normalizeKategori($rawKategori);

            $rawKecamatan = $row['nama_kecamatan'] ?? $row['kecamatan'] ?? $row['kec'] ?? null;
            $kecamatan    = $this->normalizeKecamatan($rawKecamatan);

            if (!$kategori) {
                if ($kecamatan) {
                    $kategori = 'Desa';
                } else {
                    $kategori = 'OPD';
                }
            }

            $alamat = $row['alamat'] ?? $row['lokasi'] ?? null;
            $alamat = $alamat ? trim((string)$alamat) : null;

            $telepon = $row['telepon'] ?? $row['telp'] ?? $row['hp'] ?? $row['no_telepon'] ?? null;
            $telepon = $telepon ? trim((string)$telepon) : null;

            $keterangan = $row['keterangan'] ?? $row['ket'] ?? $row['catatan'] ?? null;
            $keterangan = $keterangan ? trim((string)$keterangan) : null;

            // Search for existing unit by nama_unit (case-insensitive & trimmed), including trashed
            $cleanUnitLower = mb_strtolower(trim($namaUnit));
            $cleanKecLower  = $kecamatan ? mb_strtolower(trim($kecamatan)) : null;

            $existing = UnitDiperiksa::withTrashed()
                ->whereRaw('LOWER(TRIM(nama_unit)) = ?', [$cleanUnitLower])
                ->when($cleanKecLower, function ($q) use ($cleanKecLower) {
                    $q->where(function ($sub) use ($cleanKecLower) {
                        $sub->whereNull('nama_kecamatan')
                            ->orWhereRaw('LOWER(TRIM(nama_kecamatan)) = ?', [$cleanKecLower]);
                    });
                })
                ->first();

            if ($existing) {
                // Jika data pernah terhapus, pulihkan kembali (restore)
                if ($existing->trashed()) {
                    $existing->restore();
                }

                $updateData = [];
                if ($kategori && $existing->kategori !== $kategori) {
                    $updateData['kategori'] = $kategori;
                }
                if ($kecamatan && $existing->nama_kecamatan !== $kecamatan) {
                    $updateData['nama_kecamatan'] = $kecamatan;
                }
                if ($alamat && $existing->alamat !== $alamat) {
                    $updateData['alamat'] = $alamat;
                }
                if ($telepon && $existing->telepon !== $telepon) {
                    $updateData['telepon'] = $telepon;
                }
                if ($keterangan && $existing->keterangan !== $keterangan) {
                    $updateData['keterangan'] = $keterangan;
                }

                if (!empty($updateData)) {
                    $existing->update($updateData);
                    $this->updatedCount++;
                } else {
                    $this->skippedCount++;
                }
            } else {
                UnitDiperiksa::create([
                    'nama_unit'      => $namaUnit,
                    'kategori'       => $kategori,
                    'nama_kecamatan' => $kecamatan,
                    'alamat'         => $alamat,
                    'telepon'        => $telepon,
                    'keterangan'     => $keterangan,
                ]);
                $this->importedCount++;
            }
        }
    }

    /**
     * Normalisasi nama kecamatan (menghapus awalan 'Kecamatan' / 'Kec.', trim spasi, Titlecase).
     */
    private function normalizeKecamatan(?string $input): ?string
    {
        if (!$input) return null;

        $clean = trim((string) $input);
        if ($clean === '') return null;

        // Hapus awalan 'Kecamatan ', 'Kec. ', 'Kec ' (case-insensitive)
        $clean = preg_replace('/^(kecamatan|kec\.|kec)\s+/i', '', $clean);
        $clean = trim($clean);

        if ($clean === '') return null;

        // Ubah menjadi Title Case (misal: "SULANG" / "sulang" -> "Sulang")
        return ucwords(strtolower($clean));
    }

    /**
     * Normalisasi nama kategori ke format baku.
     */
    private function normalizeKategori(?string $input): ?string
    {
        if (!$input) return null;

        $clean = trim((string) $input);
        if ($clean === '') return null;

        $lower = strtolower($clean);
        return match (true) {
            str_contains($lower, 'desa')    => 'Desa',
            str_contains($lower, 'opd')     => 'OPD',
            str_contains($lower, 'sekolah') => 'Sekolah',
            str_contains($lower, 'bumd')    => 'BUMD',
            str_contains($lower, 'blud')    => 'BLUD',
            default                          => ucwords($lower),
        };
    }
}
