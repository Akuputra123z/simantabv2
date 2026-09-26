<?php

namespace App\Actions\Lhr;

use App\Enums\LhrStatus;
use App\Enums\SimpulanStatus;
use App\Models\AuditAssignment;
use App\Models\Lhr\JenisDokumenReviu;
use App\Models\Lhr\Lhr;
use App\Models\Lhr\LhrDasarHukum;
use App\Models\Lhr\LhrPengujian;
use App\Models\Lhr\LhrSection;
use App\Models\Lhr\LhrStatistik;
use App\Models\Lhr\LhrTemplate;
use App\Models\UnitDiperiksa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreateLhrFromAssignment
{
    /**
     * Buat dokumen LHR baru dari Surat Perintah Tugas (SPT) Reviu.
     */
    public function execute(
        AuditAssignment $assignment,
        UnitDiperiksa $unit,
        JenisDokumenReviu $jenisDokumen,
        int $tahunAnggaran,
        ?string $permohonanNomor = null,
        ?Carbon $permohonanTanggal = null,
        ?Carbon $tanggalMulai = null,
        ?Carbon $tanggalSelesai = null,
        ?string $nomorLhr = null,
        ?Carbon $tanggalLhr = null,
        ?int $ditandatanganiOleh = null,
        ?User $creator = null
    ): Lhr {
        // Ambil template aktif untuk jenis dokumen reviu ini
        $template = LhrTemplate::where('jenis_dokumen_reviu_id', $jenisDokumen->id)
            ->where('status', 'terbit')
            ->latest('versi')
            ->first();

        if (! $template) {
            throw new InvalidArgumentException("Template terbit untuk jenis dokumen '{$jenisDokumen->nama}' belum tersedia.");
        }

        $tanggalMulai = $tanggalMulai ?? $assignment->tanggal_mulai;
        $tanggalSelesai = $tanggalSelesai ?? $assignment->tanggal_selesai;

        return DB::transaction(function () use (
            $assignment,
            $unit,
            $jenisDokumen,
            $template,
            $tahunAnggaran,
            $permohonanNomor,
            $permohonanTanggal,
            $tanggalMulai,
            $tanggalSelesai,
            $nomorLhr,
            $tanggalLhr,
            $ditandatanganiOleh,
            $creator
        ) {
            // Pastikan nomor_lhr tidak bentrok dengan dokumen yang sudah ada
            if ($nomorLhr && Lhr::where('nomor_lhr', $nomorLhr)->exists()) {
                $nomorLhr = app(\App\Services\Lhr\LhrNumberingService::class)->getNextSuggestedNomor('lhr', $tahunAnggaran);
            }

            // 1. Buat Header Agregat LHR
            $lhr = Lhr::create([
                'audit_assignment_id' => $assignment->id,
                'unit_diperiksa_id' => $unit->id,
                'jenis_dokumen_reviu_id' => $jenisDokumen->id,
                'lhr_template_id' => $template->id,
                'tahun_anggaran' => $tahunAnggaran,
                'nomor_lhr' => $nomorLhr,
                'tanggal_lhr' => $tanggalLhr,
                'ditandatangani_oleh' => $ditandatanganiOleh,
                'permohonan_nomor' => $permohonanNomor,
                'permohonan_tanggal' => $permohonanTanggal,
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
                'status' => LhrStatus::Draft,
                'versi' => 1,
                'created_by' => $creator?->id,
            ]);

            // 2. Salin Bagian Naratif dengan Placeholder Replacement Aman
            $replacements = [
                '{{opd}}' => $unit->nama_unit,
                '{{tahun}}' => (string) $tahunAnggaran,
                '{{nomor_spt}}' => $assignment->nomor_surat ?? '-',
                '{{tanggal_spt}}' => $assignment->tanggal_mulai ? Carbon::parse($assignment->tanggal_mulai)->translatedFormat('d F Y') : '-',
                '{{tanggal_mulai}}' => $tanggalMulai ? $tanggalMulai->translatedFormat('d F Y') : '-',
                '{{tanggal_selesai}}' => $tanggalSelesai ? $tanggalSelesai->translatedFormat('d F Y') : '-',
                '{{jenis_dokumen}}' => $jenisDokumen->nama,
            ];

            foreach ($template->sections as $sec) {
                $isi = $sec->isi_default;
                if ($isi) {
                    $isi = str_replace(array_keys($replacements), array_values($replacements), $isi);
                }

                LhrSection::create([
                    'lhr_id' => $lhr->id,
                    'kunci' => $sec->kunci,
                    'judul' => $sec->judul,
                    'isi' => $isi,
                    'urutan' => $sec->urutan,
                ]);
            }

            // 3. Salin Dasar Hukum
            foreach ($template->dasarHukums as $dh) {
                LhrDasarHukum::create([
                    'lhr_id' => $lhr->id,
                    'urutan' => $dh->urutan,
                    'teks' => $dh->teks,
                ]);
            }

            // 4. Salin Butir Pengujian (Hierarkis / Parent-Child)
            $pengujianIdMap = [];
            $sortedPengujians = $template->pengujians->sortBy(fn ($p) => ($p->parent_id ? 1000 : 0) + $p->urutan);

            foreach ($sortedPengujians as $peng) {
                $created = LhrPengujian::create([
                    'lhr_id' => $lhr->id,
                    'template_pengujian_id' => $peng->id,
                    'parent_id' => $peng->parent_id ? ($pengujianIdMap[$peng->parent_id] ?? null) : null,
                    'kode' => $peng->kode,
                    'judul' => $peng->judul,
                    'grup' => $peng->grup,
                    'urutan' => $peng->urutan,
                    'status_simpulan' => SimpulanStatus::BelumDinilai,
                    'teks_simpulan' => $peng->teks_simpulan_default,
                ]);
                $pengujianIdMap[$peng->id] = $created->id;
            }

            // 5. Inisialisasi Cache Statistik
            LhrStatistik::create([
                'lhr_id' => $lhr->id,
                'total_rekomendasi' => 0,
                'selesai' => 0,
                'persen' => 0.00,
                'dihitung_pada' => now(),
            ]);

            // 6. Otomatis terapkan draf temuan & rekomendasi standar resmi Rembang jika dokumen RKPD
            if ($jenisDokumen->kode === 'RKPD') {
                (new ApplyStandardRkpdFindings())->execute($lhr);
            }

            return $lhr->fresh(['sections', 'dasarHukums', 'pengujians.catatans.rekomendasis', 'statistik']);
        });
    }
}
