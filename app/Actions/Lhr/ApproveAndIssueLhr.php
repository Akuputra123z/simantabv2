<?php

namespace App\Actions\Lhr;

use App\Enums\LhrStatus;
use App\Models\Lhr\Lhr;
use App\Models\User;
use App\Services\Lhr\LhrNumberingService;
use Illuminate\Support\Facades\DB;

class ApproveAndIssueLhr
{
    public function __construct(
        protected LhrNumberingService $numberingService
    ) {}

    /**
     * Setujui dan terbitkan LHR oleh Kepala Inspektorat dengan alokasi nomor atomik.
     */
    public function execute(Lhr $lhr, User $approver, string $sufiks = ''): Lhr
    {
        if ($lhr->status !== LhrStatus::MenungguPersetujuan) {
            throw new \DomainException('Dokumen LHR belum dalam status menunggu persetujuan akhir.');
        }

        return DB::transaction(function () use ($lhr, $approver, $sufiks) {
            $tanggalLhr = $lhr->tanggal_lhr ?? now()->toDateString();
            $tahunSurat = (int) date('Y', strtotime((string) $tanggalLhr));

            // 1. Alokasikan nomor surat resmi secara atomik berdasarkan tahun penerbitan
            $nomorData = $this->numberingService->allocate('lhr', $tahunSurat, $sufiks);

            // 2. Kumpulkan snapshot tim & objek pengawasan saat terbit
            $assignment = $lhr->auditAssignment()->with(['ketuaTim', 'pengendaliTeknis', 'members'])->first();

            $teamSnapshot = [
                'pengendali_teknis' => [
                    'nama' => $assignment?->pengendaliTeknis?->name ?? '-',
                    'nip' => $assignment?->pengendaliTeknis?->nip ?? '-',
                    'jabatan' => $assignment?->pengendaliTeknis?->jabatan ?? 'Pengendali Teknis',
                ],
                'ketua_tim' => [
                    'nama' => $assignment?->ketuaTim?->name ?? '-',
                    'nip' => $assignment?->ketuaTim?->nip ?? '-',
                    'jabatan' => $assignment?->ketuaTim?->jabatan ?? 'Ketua Tim',
                ],
                'anggota' => $assignment?->members->map(fn ($m) => [
                    'nama' => $m->name,
                    'nip' => $m->nip,
                    'jabatan' => $m->pivot->jabatan_tim ?? $m->jabatan,
                ])->toArray() ?? [],
                'penandatangan' => [
                    'nama' => $approver->name,
                    'nip' => $approver->nip,
                    'jabatan' => $approver->jabatan ?? 'Inspektur Kabupaten',
                ],
            ];

            // 3. Hitung jumlah rekomendasi LHR
            $totalRekom = 0;
            $catatans = $lhr->catatans()->withCount('rekomendasis')->get();
            foreach ($catatans as $cat) {
                $totalRekom += $cat->rekomendasis_count;
            }

            // LHR tanpa rekomendasi langsung berstatus Selesai (PRD TL-06)
            $finalStatus = ($totalRekom === 0) ? LhrStatus::Selesai : LhrStatus::Terbit;

            // 4. Update LHR
            $lhr->update([
                'nomor_lhr' => $nomorData['nomor'],
                'nomor_urut' => $nomorData['nomor_urut'],
                'tahun_nomor' => $nomorData['tahun'],
                'sufiks' => $nomorData['sufiks'],
                'status' => $finalStatus,
                'tanggal_lhr' => $lhr->tanggal_lhr ?? now()->toDateString(),
                'ditandatangani_oleh' => $approver->id,
                'ditandatangani_pada' => now(),
                'snapshot_terbit' => $teamSnapshot,
            ]);

            // 5. Update Statistik
            $persen = ($totalRekom === 0) ? 100.00 : 0.00;
            $lhr->statistik()->updateOrCreate(
                ['lhr_id' => $lhr->id],
                [
                    'total_rekomendasi' => $totalRekom,
                    'selesai' => ($totalRekom === 0 ? 0 : 0),
                    'persen' => $persen,
                    'dihitung_pada' => now(),
                ]
            );

            return $lhr->fresh();
        });
    }
}
