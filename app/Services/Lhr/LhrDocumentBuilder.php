<?php

namespace App\Services\Lhr;

use App\Models\Lhr\Lhr;

class LhrDocumentBuilder
{
    /**
     * Susun array data representasi dokumen lengkap untuk rendering Blade & PDF.
     *
     * @return array<string, mixed>
     */
    public function build(Lhr $lhr): array
    {
        $lhr->load([
            'unitDiperiksa',
            'jenisDokumen',
            'auditAssignment.ketuaTim',
            'auditAssignment.pengendaliTeknis',
            'auditAssignment.members',
            'sections',
            'dasarHukums',
            'pengujians.parent',
            'pengujians.children.catatans.rekomendasis.kodeRekomendasi',
            'pengujians.catatans.rekomendasis.kodeRekomendasi',
            'pengujians.catatans.kategori',
            'signer',
            'attachments',
        ]);

        $sectionsKeyed = [];
        foreach ($lhr->sections as $sec) {
            $sectionsKeyed[$sec->kunci] = $sec;
        }

        // Ambil data tim dari snapshot jika sudah terbit, atau dari penugasan berjalan
        $team = $lhr->snapshot_terbit['team'] ?? [
            'pengendali_teknis' => [
                'nama' => $lhr->auditAssignment?->pengendaliTeknis?->name ?? '-',
                'nip' => $lhr->auditAssignment?->pengendaliTeknis?->nip ?? '-',
                'jabatan' => $lhr->auditAssignment?->pengendaliTeknis?->jabatan ?? 'Pengendali Teknis',
            ],
            'ketua_tim' => [
                'nama' => $lhr->auditAssignment?->ketuaTim?->name ?? '-',
                'nip' => $lhr->auditAssignment?->ketuaTim?->nip ?? '-',
                'jabatan' => $lhr->auditAssignment?->ketuaTim?->jabatan ?? 'Ketua Tim',
            ],
            'anggota' => $lhr->auditAssignment?->members->map(fn ($m) => [
                'nama' => $m->name,
                'nip' => $m->nip,
                'jabatan' => $m->pivot->jabatan_tim ?? $m->jabatan,
            ])->toArray() ?? [],
        ];

        return [
            'lhr' => $lhr,
            'unit' => $lhr->unitDiperiksa,
            'jenis' => $lhr->jenisDokumen,
            'assignment' => $lhr->auditAssignment,
            'sections' => $sectionsKeyed,
            'dasarHukums' => $lhr->dasarHukums,
            'pengujians' => $lhr->pengujians,
            'team' => $team,
            'signer' => $lhr->signer,
            'isDraft' => ! $lhr->isTerbit(),
            'verifyUrl' => url("/verifikasi/{$lhr->uuid}"),
            'attachments' => $lhr->attachments,
        ];
    }
}
