<?php

namespace Database\Seeders;

use App\Models\Lhp;
use App\Models\Temuan;
use App\Models\Recommendation;
use App\Models\UnitDiperiksa;
use App\Models\KodeTemuan;
use App\Models\KodeRekomendasi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AgentsDataSeeder extends Seeder
{
    public function run(): void
    {
        // Temuan Data Map from AGENTS.md
        $dataset = [
            // 1.01.00 Kerugian Negara/Daerah
            [
                'sub_kel' => '1.01.00',
                'kode_default' => '1.01.01',
                'items' => [
                    ['nama' => 'DESA TIREMAN', 'jumlah' => 1, 'nilai' => 23001000],
                    ['nama' => 'DESA PAKIS', 'jumlah' => 1, 'nilai' => 4855000],
                    ['nama' => 'DESA MOJOSARI', 'jumlah' => 3, 'nilai' => 66542066],
                    ['nama' => 'DESA TLOGOTUNGGAL', 'jumlah' => 1, 'nilai' => 19905000],
                    ['nama' => 'DESA PANTIHARJO', 'jumlah' => 3, 'nilai' => 34071589],
                    ['nama' => 'DESA TASIKHARJO', 'jumlah' => 1, 'nilai' => 3577922],
                    ['nama' => 'DESA SUMBERGIRANG', 'jumlah' => 3, 'nilai' => 47805094],
                    ['nama' => 'DESA WARUGUNUNG', 'jumlah' => 1, 'nilai' => 11292444],
                    ['nama' => 'DESA PACING', 'jumlah' => 2, 'nilai' => 435935602],
                    ['nama' => 'DESA KARANGHARJO', 'jumlah' => 1, 'nilai' => 9750600],
                    ['nama' => 'DESA KEMADU', 'jumlah' => 2, 'nilai' => 41681000],
                    ['nama' => 'DESA JURANGJERO', 'jumlah' => 3, 'nilai' => 14090775],
                    ['nama' => 'DESA DOROPAYUNG', 'jumlah' => 3, 'nilai' => 121504828],
                    ['nama' => 'DESA NGULAHAN', 'jumlah' => 1, 'nilai' => 39102623],
                    ['nama' => 'DESA SANETAN', 'jumlah' => 1, 'nilai' => 26889260],
                    ['nama' => 'DESA SEREN', 'jumlah' => 1, 'nilai' => 46981172],
                    ['nama' => 'DESA JADI', 'jumlah' => 2, 'nilai' => 17619500],
                    ['nama' => 'DESA BULU', 'jumlah' => 1, 'nilai' => 9611000],
                    ['nama' => 'DESA NGULAAN', 'jumlah' => 1, 'nilai' => 5890000],
                    ['nama' => 'DESA KETANGGI', 'jumlah' => 1, 'nilai' => 12195600],
                    ['nama' => 'DESA JOHOGUNUNG', 'jumlah' => 1, 'nilai' => 9884946],
                ]
            ],
            // 1.02.00 Potensi Kerugian
            [
                'sub_kel' => '1.02.00',
                'kode_default' => '1.02.01',
                'items' => [
                    ['nama' => 'DESA PACING', 'jumlah' => 1, 'nilai' => 16950000],
                    ['nama' => 'DESA KEMADU', 'jumlah' => 1, 'nilai' => 37044500],
                ]
            ],
            // 1.03.00 Kekurangan Penerimaan
            [
                'sub_kel' => '1.03.00',
                'kode_default' => '1.03.01',
                'items' => [
                    ['nama' => 'DESA PACING', 'jumlah' => 1, 'nilai' => 3902353],
                    ['nama' => 'DESA KARANGHARJO', 'jumlah' => 2, 'nilai' => 48272617],
                    ['nama' => 'DESA KEMADU', 'jumlah' => 1, 'nilai' => 47315363],
                    ['nama' => 'DESA JURANGJERO', 'jumlah' => 1, 'nilai' => 4102774],
                    ['nama' => 'DESA JADI', 'jumlah' => 1, 'nilai' => 3753201],
                    ['nama' => 'DESA NGULAAN', 'jumlah' => 1, 'nilai' => 1434000],
                ]
            ],
            // 1.04.00 Administrasi
            [
                'sub_kel' => '1.04.00',
                'kode_default' => '1.04.01',
                'items' => [
                    ['nama' => 'DESA TIREMAN', 'jumlah' => 2, 'nilai' => 1423000],
                    ['nama' => 'DESA PAKIS', 'jumlah' => 1, 'nilai' => 0],
                    ['nama' => 'DESA MOJOSARI', 'jumlah' => 3, 'nilai' => 5780500],
                    ['nama' => 'DESA TLOGOTUNGGAL', 'jumlah' => 1, 'nilai' => 1726500],
                    ['nama' => 'DESA PANTIHARJO', 'jumlah' => 2, 'nilai' => 0],
                    ['nama' => 'DESA TASIKHARJO', 'jumlah' => 2, 'nilai' => 1813500],
                    ['nama' => 'DESA SUMBERGIRANG', 'jumlah' => 3, 'nilai' => 50000],
                    ['nama' => 'DESA WARUGUNUNG', 'jumlah' => 1, 'nilai' => 2405000],
                    ['nama' => 'DESA PACING', 'jumlah' => 2, 'nilai' => 8791000],
                    ['nama' => 'DESA KEMADU', 'jumlah' => 1, 'nilai' => 5468200],
                    ['nama' => 'DESA JURANGJERO', 'jumlah' => 2, 'nilai' => 580000],
                    ['nama' => 'DESA DOROPAYUNG', 'jumlah' => 3, 'nilai' => 14837000],
                    ['nama' => 'DESA NGULAHAN', 'jumlah' => 1, 'nilai' => 4224500],
                    ['nama' => 'DESA SEREN', 'jumlah' => 1, 'nilai' => 776000],
                    ['nama' => 'DESA JADI', 'jumlah' => 2, 'nilai' => 167284],
                    ['nama' => 'DESA KETANGGI', 'jumlah' => 1, 'nilai' => 8500000],
                    ['nama' => 'DESA JOHOGUNUNG', 'jumlah' => 1, 'nilai' => 1485400],
                ]
            ],
            // 2.01.00 Kelemahan Akuntansi & Pelaporan
            [
                'sub_kel' => '2.01.00',
                'kode_default' => '2.01.01',
                'items' => [
                    ['nama' => 'DESA KEMADU', 'jumlah' => 1, 'nilai' => 0],
                    ['nama' => 'DESA JADI', 'jumlah' => 1, 'nilai' => 0],
                ]
            ],
            // 2.02.00 Kelemahan Pelaksanaan Anggaran
            [
                'sub_kel' => '2.02.00',
                'kode_default' => '2.02.01',
                'items' => [
                    ['nama' => 'DESA TLOGOTUNGGAL', 'jumlah' => 1, 'nilai' => 0],
                    ['nama' => 'DESA PACING', 'jumlah' => 2, 'nilai' => 0],
                    ['nama' => 'DESA KARANGHARJO', 'jumlah' => 1, 'nilai' => 0],
                    ['nama' => 'DESA KEMADU', 'jumlah' => 1, 'nilai' => 0],
                    ['nama' => 'DESA SANETAN', 'jumlah' => 1, 'nilai' => 0],
                    ['nama' => 'DESA SEREN', 'jumlah' => 1, 'nilai' => 0],
                    ['nama' => 'DESA BULU', 'jumlah' => 1, 'nilai' => 0],
                    ['nama' => 'DESA NGULAAN', 'jumlah' => 1, 'nilai' => 0],
                    ['nama' => 'DESA KETANGGI', 'jumlah' => 1, 'nilai' => 0],
                    ['nama' => 'DESA JOHOGUNUNG', 'jumlah' => 1, 'nilai' => 0],
                ]
            ],
            // 2.03.00 Kelemahan Struktur SPI
            [
                'sub_kel' => '2.03.00',
                'kode_default' => '2.03.01',
                'items' => [
                    ['nama' => 'DESA JADI', 'jumlah' => 1, 'nilai' => 0],
                ]
            ],
        ];

        $defaultKodeRekomendasi = KodeRekomendasi::first()?->id;

        foreach ($dataset as $group) {
            $kodeTemuan = KodeTemuan::where('kode', $group['kode_default'])->first() 
                        ?? KodeTemuan::where('kode', 'LIKE', substr($group['sub_kel'], 0, 4) . '%')->first();

            if (!$kodeTemuan) {
                continue;
            }

            foreach ($group['items'] as $item) {
                $unit = UnitDiperiksa::where('nama_unit', 'LIKE', '%' . $item['nama'] . '%')->first();
                if (!$unit) {
                    $unit = UnitDiperiksa::create(['nama_unit' => $item['nama'], 'jenis' => 'desa']);
                }

                // Get or create LHP for this unit
                $lhp = Lhp::where('unit_diperiksa_id', $unit->id)
                    ->whereYear('tanggal_lhp', 2026)
                    ->first();

                if (!$lhp) {
                    $cleanUnit = str_replace(' ', '', strtoupper($item['nama']));
                    $lhp = Lhp::create([
                        'audit_assignment_id' => \App\Models\AuditAssignment::first()?->id ?? 1,
                        'nomor_lhp' => '700/LHP/' . $cleanUnit . '/2026',
                        'judul' => 'Laporan Hasil Pemeriksaan Pengawasan Keuangan ' . ucwords(strtolower($item['nama'])),
                        'tanggal_lhp' => '2026-03-15',
                        'unit_diperiksa_id' => $unit->id,
                        'status' => 'selesai',
                    ]);
                }

                $qty = max(1, (int)$item['jumlah']);
                $totalNilai = (float)$item['nilai'];
                $unitNilai = round($totalNilai / $qty, 2);

                for ($i = 0; $i < $qty; $i++) {
                    $nilaiThis = ($i == $qty - 1) ? ($totalNilai - ($unitNilai * ($qty - 1))) : $unitNilai;

                    // Check if already seeded to avoid duplicates
                    $exists = Temuan::where('lhp_id', $lhp->id)
                        ->where('kode_temuan_id', $kodeTemuan->id)
                        ->where('nilai_temuan', $nilaiThis)
                        ->exists();

                    if (!$exists) {
                        $temuan = Temuan::create([
                            'lhp_id' => $lhp->id,
                            'kode_temuan_id' => $kodeTemuan->id,
                            'kondisi' => 'Hasil temuan pengawasan pada ' . $item['nama'] . ' (' . $group['sub_kel'] . ')',
                            'sebab' => 'Kurang kecermatan dalam pengelolaan keuangan dan administrasi desa',
                            'akibat' => 'Berpotensi menimbulkan ketidakpatuhan atau risiko finansial',
                            'nilai_temuan' => $nilaiThis,
                            'status_tl' => 'belum_ditindaklanjuti',
                        ]);

                        // Seed a matching recommendation
                        Recommendation::create([
                            'temuan_id' => $temuan->id,
                            'kode_rekomendasi_id' => $defaultKodeRekomendasi,
                            'uraian_rekom' => 'Agar Kepala ' . ucwords(strtolower($item['nama'])) . ' melakukan penyetoran/penatausahaan administrasi sesuai petunjuk audit.',
                            'status' => 'proses',
                        ]);
                    }
                }
            }
        }
    }
}
