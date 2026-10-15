@extends('layouts.app')

@section('content')
@php
    $stat   = $lhp->statistik;
    $persen = (float) ($stat?->persen_selesai_gabungan ?? 0);
@endphp

<div class="space-y-6">

    @if(!empty($isLocked))
        {{-- SECURE LOCK SCREEN --}}
        <div class="mx-auto max-w-lg mt-10 rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-xl dark:border-gray-800 dark:bg-gray-900"
             x-data="{
                 password: '',
                 loading: false,
                 errorMessage: '',
                 async verifyAndUnlock() {
                     if (!this.password) {
                         this.errorMessage = 'Silakan masukkan password akun Anda.';
                         return;
                     }
                     this.loading = true;
                     this.errorMessage = '';
                     try {
                         const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
                         const res = await fetch('{{ route('security.verify-pdf-access') }}', {
                             method: 'POST',
                             headers: {
                                 'Content-Type': 'application/json',
                                 'Accept': 'application/json',
                                 'X-CSRF-TOKEN': token || ''
                             },
                             body: JSON.stringify({
                                 password: this.password,
                                 document_type: 'rekap_show',
                                 document_id: '{{ $lhp->uuid }}'
                             })
                         });
                         const data = await res.json();
                         if (res.ok && data.success) {
                             window.location.href = data.url || window.location.href;
                         } else {
                             this.errorMessage = data.message || 'Password yang Anda masukkan salah.';
                             this.loading = false;
                         }
                     } catch (e) {
                         this.errorMessage = 'Terjadi kesalahan jaringan. Silakan coba lagi.';
                         this.loading = false;
                     }
                 }
             }">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400 border border-rose-100 dark:border-rose-900/50 shadow-inner">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h2 class="mt-4 text-xl font-bold text-gray-900 dark:text-white">Dokumen Rekap Dilindungi Keamanan</h2>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                Detail Rekapitulasi LHP <strong>{{ $lhp->nomor_lhp }}</strong> diproteksi. Masukkan password akun Anda untuk membuka dokumen resmi ini.
            </p>

            <div class="mt-4 rounded-xl bg-gray-50 dark:bg-gray-800/60 p-3 text-left border border-gray-100 dark:border-gray-800">
                <div class="text-[11px] font-semibold text-gray-400 uppercase">Unit Diperiksa / OPD</div>
                <div class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $lhp->unitDiperiksa?->nama_unit ?? 'Unit Kerja' }}</div>
            </div>

            <form @submit.prevent="verifyAndUnlock" class="mt-5 space-y-3">
                <div x-show="errorMessage" class="rounded-lg bg-rose-50 dark:bg-rose-950/40 p-2.5 text-xs text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60" x-text="errorMessage" x-cloak></div>

                <div class="relative text-left">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Password Akun</label>
                    <input type="password"
                           x-model="password"
                           placeholder="Masukkan password login Anda..."
                           required
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:border-rose-500 focus:ring-1 focus:ring-rose-500">
                </div>

                <div class="flex gap-2 pt-2">
                    <a href="{{ route('laporan.index') }}"
                       class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition">
                        Kembali
                    </a>
                    <button type="submit"
                            :disabled="loading"
                            class="flex-1 rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700 transition disabled:opacity-50">
                        <span x-show="!loading">Buka Rekap</span>
                        <span x-show="loading" x-cloak>Memverifikasi...</span>
                    </button>
                </div>
            </form>
        </div>
    @else
    {{-- HEADER --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="mb-1 flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ route('laporan.index') }}" class="hover:text-primary-600">Laporan</a>
                <span>/</span>
                <span class="text-gray-700 dark:text-white">Detail LHP</span>
            </nav>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $lhp->nomor_lhp }}</h1>
            <p class="text-sm text-gray-500">{{ $lhp->auditAssignment?->auditProgram?->nama_program ?? '-' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('laporan.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                &larr; Kembali
            </a>
            <button type="button"
               onclick="window.requestSecurePdf('lhp_download', '{{ $lhp->uuid }}', 'LHP: {{ addslashes($lhp->nomor_lhp) }}')"
               class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 cursor-pointer">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download PDF
            </button>
            <a href="{{ route('laporan.download-excel-per-lhp', $lhp) }}"
               class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download Excel
            </a>
        </div>
    </div>

    {{-- INFO + STAT GRID --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Info LHP --}}
        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-100 px-5 py-3.5 dark:border-gray-700">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Informasi LHP</h2>
            </div>
            <dl class="divide-y divide-gray-100 dark:divide-gray-700">
                @php
                    $infoRows = [
                        ['Tanggal LHP',      $lhp->tanggal_lhp?->translatedFormat('d F Y') ?? '-'],
                        ['Semester',         'Semester ' . $lhp->semester],
                        ['IRBAN',            $lhp->auditAssignment?->auditProgramDetail?->tim ?? '-'],
                        ['Jenis Pemeriksaan',$lhp->jenis_pemeriksaan ?? '-'],
                        ['Unit Diperiksa',   $lhp->unitDiperiksa?->nama_unit ?? '-'],
                        ['Dibuat Oleh',      $lhp->creator?->name ?? '-'],
                        ['Status',           ucfirst(str_replace('_', ' ', $lhp->status))],
                    ];
                @endphp
                @foreach ($infoRows as [$label, $value])
                <div class="flex justify-between gap-3 px-5 py-2.5">
                    <dt class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                    <dd class="text-xs font-medium text-gray-900 dark:text-white text-right">{{ $value }}</dd>
                </div>
                @endforeach
            </dl>
        </div>

        {{-- Statistik --}}
        <div class="lg:col-span-2 grid grid-cols-2 gap-4 sm:grid-cols-4 content-start">
            @php
                $cards = [
                    ['Total Temuan',    $stat?->total_temuan ?? 0,      'blue',  null],
                    ['Total Rekom',     $stat?->total_rekomendasi ?? 0,  'blue',  null],
                    ['Rekom Selesai',   $stat?->rekom_selesai ?? 0,      'green', null],
                    ['Rekom Proses',    $stat?->rekom_proses ?? 0,       'amber', null],
                    ['Rekom Belum',     $stat?->rekom_belum ?? 0,        'red',   null],
                    ['Progress TL',     number_format($persen,1) . '%',  'gray',  null],
                    ['Total Kerugian',  'Rp ' . number_format($stat?->total_kerugian ?? 0, 0, ',', '.'), 'red', null],
                    ['Sisa Kerugian',   'Rp ' . number_format($stat?->total_sisa_kerugian ?? 0, 0, ',', '.'), 'amber', null],
                ];
                $colorMap = [
                    'blue'  => 'bg-blue-50  border-blue-200  dark:bg-blue-900/20  dark:border-blue-800  text-blue-700  dark:text-blue-300',
                    'green' => 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800 text-green-700 dark:text-green-300',
                    'amber' => 'bg-amber-50 border-amber-200 dark:bg-amber-900/20 dark:border-amber-800 text-amber-700 dark:text-amber-300',
                    'red'   => 'bg-red-50   border-red-200   dark:bg-red-900/20   dark:border-red-800   text-red-700   dark:text-red-300',
                    'gray'  => 'bg-gray-50  border-gray-200  dark:bg-gray-700     dark:border-gray-600  text-gray-700  dark:text-gray-300',
                ];
            @endphp
            @foreach ($cards as [$label, $val, $color, $_])
            <div class="rounded-xl border p-4 {{ $colorMap[$color] }}">
                <p class="text-xs opacity-70">{{ $label }}</p>
                <p class="mt-1 text-lg font-bold leading-tight">{{ $val }}</p>
            </div>
            @endforeach
        </div>

    </div>

    {{-- TEMUAN --}}
    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                Daftar Temuan
                <span class="ml-1 text-xs font-normal text-gray-400">({{ $lhp->temuans->count() }})</span>
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50/50 dark:bg-gray-900/50 text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-center w-10">No</th>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Kondisi</th>
                        <th class="px-4 py-3 text-right">Total Kerugian</th>
                        <th class="px-4 py-3 text-center">Rekom</th>
                        <th class="px-4 py-3 text-center">Status TL</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($lhp->temuans as $i => $t)
                    @php
                        $tlConf = match($t->status_tl) {
                            'selesai'               => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                            'dalam_proses'          => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                            'belum_ditindaklanjuti' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                            default                 => 'bg-gray-100 text-gray-600',
                        };
                        $tlLabel = match($t->status_tl) {
                            'selesai'               => 'Selesai',
                            'dalam_proses'          => 'Dalam Proses',
                            'belum_ditindaklanjuti' => 'Belum TL',
                            default                 => $t->status_tl,
                        };
                    @endphp
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                        <td class="px-4 py-3 text-center font-semibold text-gray-500">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                {{ $t->kodeTemuan?->kode ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300 max-w-xs">{{ Str::limit($t->kondisi, 120) }}</td>
                        <td class="px-4 py-3 text-right font-medium text-red-600 dark:text-red-400 whitespace-nowrap">
                            Rp {{ number_format($t->total_nilai_temuan, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-300">{{ $t->recommendations->count() }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $tlConf }}">
                                {{ $tlLabel }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Tidak ada temuan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- REKOMENDASI --}}
    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                Rekomendasi & Tindak Lanjut
                <span class="ml-1 text-xs font-normal text-gray-400">({{ $lhp->temuans->flatMap->recommendations->count() }})</span>
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50/50 dark:bg-gray-900/50 text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-center w-10">No</th>
                        <th class="px-4 py-3">Temuan</th>
                        <th class="px-4 py-3">Uraian Rekomendasi</th>
                        <th class="px-4 py-3 text-center">Jenis</th>
                        <th class="px-4 py-3 text-right">Nilai Rekom</th>
                        <th class="px-4 py-3 text-right">TL Selesai</th>
                        <th class="px-4 py-3 text-right">Sisa</th>
                        <th class="px-4 py-3 text-center">Progress</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @php $no = 1; @endphp
                    @forelse ($lhp->temuans as $ti => $t)
                        @foreach ($t->recommendations as $r)
                        @php
                            $pR = $r->progress();
                            $barR = $pR >= 100 ? 'bg-green-500' : ($pR >= 50 ? 'bg-amber-400' : ($pR > 0 ? 'bg-blue-500' : 'bg-gray-200 dark:bg-gray-600'));
                            $statusConf = match($r->status) {
                                'selesai'               => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                'proses'                => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                'belum_ditindaklanjuti' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                default                 => 'bg-gray-100 text-gray-600',
                            };
                            $statusLabel = match($r->status) {
                                'selesai'               => 'Selesai',
                                'proses'                => 'Proses',
                                'belum_ditindaklanjuti' => 'Belum TL',
                                default                 => $r->status,
                            };
                            $jenisConf = match($r->jenis_rekomendasi) {
                                'uang'         => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                'administrasi' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                                'barang'       => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                default        => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-4 py-3 text-center font-semibold text-gray-500">{{ $no++ }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">T{{ $ti + 1 }}</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300 max-w-xs text-xs">
                                {{ Str::limit(strip_tags($r->uraian_rekom), 100) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $jenisConf }}">
                                    {{ ucfirst($r->jenis_rekomendasi) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-xs font-medium whitespace-nowrap">
                                Rp {{ number_format($r->nilai_rekom ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-xs text-green-600 dark:text-green-400 whitespace-nowrap">
                                Rp {{ number_format($r->nilai_tl_selesai ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-xs text-red-500 whitespace-nowrap">
                                Rp {{ number_format($r->nilai_sisa ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    <div class="h-1.5 w-16 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                        <div class="{{ $barR }} h-full rounded-full" style="width:{{ min(100,$pR) }}%"></div>
                                    </div>
                                    <span class="text-[10px] font-semibold text-gray-500">{{ number_format($pR,0) }}%</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $statusConf }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    @empty
                    <tr><td colspan="9" class="px-4 py-10 text-center text-gray-400">Tidak ada rekomendasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Bagian Pengesahan / Tanda Tangan --}}
        @php
            $inspektur = \App\Models\User::find(2);
        @endphp
        <div class="border-t border-gray-100 dark:border-gray-800 p-6">
            <div class="flex justify-end">
                <div class="text-center w-80 text-xs">
                    <p class="text-gray-500 dark:text-gray-400">Mengetahui / Mengesahkan,</p>
                    <p class="font-bold text-gray-900 dark:text-white uppercase mt-0.5">Inspektur Daerah Kab. Rembang</p>
                    <div class="h-20"></div>
                    <p class="font-bold uppercase underline text-gray-900 dark:text-white">{{ $inspektur?->name ?? 'IMUNG TRI WIJAYANTI, S.P., M.T., M.A., CGCAE.' }}</p>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ $inspektur?->pangkat_gol ?? 'Pembina Utama Ahli Muda (IV/c)' }}</p>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">NIP. {{ $inspektur?->nip ?? '197411281999032003' }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection