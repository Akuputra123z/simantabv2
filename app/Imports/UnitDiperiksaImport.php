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

            $kategori = $row['kategori'] ?? $row['kat'] ?? $row['jenis'] ?? null;
            $kategori = $kategori ? trim((string)$kategori) : null;

            $kecamatan = $row['nama_kecamatan'] ?? $row['kecamatan'] ?? $row['kec'] ?? null;
            $kecamatan = $kecamatan ? trim((string)$kecamatan) : null;

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

            // Search for existing unit by nama_unit and optional nama_kecamatan
            $existingQuery = UnitDiperiksa::where('nama_unit', $namaUnit);
            if ($kecamatan) {
                $existingQuery->where('nama_kecamatan', $kecamatan);
            }

            $existing = $existingQuery->first();

            if ($existing) {
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
}
