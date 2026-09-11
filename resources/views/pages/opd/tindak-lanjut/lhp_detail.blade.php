@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10 space-y-6">

    {{-- TOP NAVIGATION & BREADCRUMBS --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 dark:border-slate-800 pb-5">
        <div>
            <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('opd.tindak-lanjut.index') }}" class="hover:text-blue-700 transition-colors">
                    Tindak Lanjut OPD
                </a>
                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-slate-900 dark:text-slate-200">Detail LHP</span>
            </nav>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Detail LHP
                </h1>
                <span class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-0.5 text-xs font-mono font-bold text-slate-800 dark:bg-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                    {{ $lhp->nomor_lhp }}
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Kelola dan kirimkan bukti tindak lanjut untuk setiap temuan dan rekomendasi pada LHP ini.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full sm:w-auto">
            <a href="{{ route('opd.tindak-lanjut.index') }}"
               class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 min-h-[44px] text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 transition-colors shadow-xs w-full sm:w-auto">
                <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Daftar LHP
            </a>

            @php
                $allTls = $lhp->temuans->flatMap->recommendations->flatMap->tindakLanjuts;
                $hasDraft = $allTls->contains(fn($t) => $t->status_opd === 'draft' || empty($t->status_opd));
            @endphp

            @if($hasDraft)
                <form action="{{ route('opd.tindak-lanjut.lhp.kirim-semua', $lhp) }}" method="POST" class="w-full sm:w-auto"
                      onsubmit="return confirm('Kirim seluruh tindak lanjut bertanda draft pada LHP ini ke Inspektorat?')">
                    @csrf
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-700 px-4 py-2.5 min-h-[44px] text-xs font-bold text-white hover:bg-emerald-800 transition-colors shadow-xs cursor-pointer">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Kirim Semua Draft LHP Ini
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- FLASH ALERTS --}}
    @if(session('success'))
        <div class="flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50/90 p-4 text-xs font-semibold text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/50 dark:text-emerald-300 shadow-xs">
            <svg class="h-4 w-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="flex items-center gap-3 rounded-lg border border-blue-200 bg-blue-50/90 p-4 text-xs font-semibold text-blue-800 dark:border-blue-900/50 dark:bg-blue-950/50 dark:text-blue-300 shadow-xs">
            <svg class="h-4 w-4 shrink-0 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-3 rounded-lg border border-rose-200 bg-rose-50/90 p-4 text-xs font-semibold text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/50 dark:text-rose-300 shadow-xs">
            <svg class="h-4 w-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- LHP EXECUTIVE METADATA CARD --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900/80 space-y-5">
        
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4">
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Unit Diperiksa</p>
                <p class="text-sm font-bold text-slate-900 dark:text-white mt-0.5">{{ $lhp->unitDiperiksa?->nama_unit ?? '-' }}</p>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tanggal LHP</p>
                <p class="text-sm font-bold text-slate-900 dark:text-white mt-0.5">
                    {{ $lhp->tanggal_lhp ? $lhp->tanggal_lhp->format('d M Y') : '-' }}
                </p>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Kategori Audit</p>
                <p class="mt-0.5">
                    <span class="inline-block rounded bg-indigo-50 px-2 py-0.5 text-xs font-bold text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                        {{ $lhp->auditAssignment?->auditProgramDetail?->auditProgram?->kategori ?? 'PKPT' }}
                    </span>
                </p>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Surat Tugas / Tim</p>
                <p class="text-sm font-bold text-slate-900 dark:text-white mt-0.5">
                    {{ $lhp->auditAssignment?->nomor_surat_tugas ?? '-' }}
                </p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                    Ketua: {{ $lhp->auditAssignment?->ketuaTim?->name ?? '-' }}
                </p>
            </div>
        </div>

        @php
            $totalKerugian = (float) ($lhp->total_kerugian ?? 0);
            $totalSetor = (float) ($allTls->sum('total_terbayar') ?? 0);
            $progres = round($lhp->persen_selesai ?? 0);
        @endphp

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 border-t border-slate-200/80 pt-4 dark:border-slate-800">
            <div class="rounded-lg bg-slate-50 p-3.5 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-700">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nilai Temuan / Kerugian</p>
                <p class="text-base font-bold font-mono text-slate-900 dark:text-white mt-0.5">
                    Rp {{ number_format($totalKerugian, 0, ',', '.') }}
                </p>
            </div>
            <div class="rounded-lg bg-emerald-50/80 p-3.5 dark:bg-emerald-950/40 border border-emerald-200/60 dark:border-emerald-900">
                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Realisasi Setor / Diselesaikan</p>
                <p class="text-base font-bold font-mono text-emerald-800 dark:text-emerald-300 mt-0.5">
                    Rp {{ number_format($totalSetor, 0, ',', '.') }}
                </p>
            </div>
            <div class="rounded-lg bg-blue-50/80 p-3.5 dark:bg-blue-950/40 border border-blue-200/60 dark:border-blue-900">
                <div class="flex justify-between items-center text-[10px] font-bold uppercase tracking-wider text-blue-700 dark:text-blue-400 mb-1">
                    <span>Progres Penyelesaian LHP</span>
                    <span class="text-xs font-mono font-bold">{{ $progres }}%</span>
                </div>
                <div class="h-2 w-full rounded-full bg-blue-200/70 dark:bg-blue-900/60 overflow-hidden">
                    <div class="h-full rounded-full {{ $progres >= 100 ? 'bg-emerald-500' : ($progres > 0 ? 'bg-blue-600' : 'bg-slate-300 dark:bg-slate-700') }}" style="width: {{ min(100, max(0, $progres)) }}%"></div>
                </div>
            </div>
        </div>

    </div>

    {{-- LIST OF FINDINGS AND RECOMMENDATIONS WITH DROPDOWN / ACCORDION --}}
    <div x-data="{ globalOpen: true }" class="space-y-5">
        
        {{-- SECTION HEADER & GLOBAL ACCORDION TOGGLES --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-200/80 pb-3 dark:border-slate-800">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">
                    Daftar Temuan & Rekomendasi LHP
                </h2>
                <p class="text-xs text-slate-500">Klik header temuan untuk membuka / menutup detail rekomendasi</p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <button type="button" @click="$dispatch('toggle-all-temuans', true)"
                        class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 hover:text-blue-800 dark:text-blue-400 cursor-pointer">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                    Buka Semua Temuan
                </button>
                <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                <button type="button" @click="$dispatch('toggle-all-temuans', false)"
                        class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400 cursor-pointer">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                    </svg>
                    Tutup Semua
                </button>
                <span class="text-xs font-semibold text-slate-400 ml-1">
                    Total: {{ $lhp->temuans->count() }} Temuan &bull; {{ $lhp->temuans->flatMap->recommendations->count() }} Rekomendasi
                </span>
            </div>
        </div>

        @forelse($lhp->temuans as $tIndex => $temuan)
            <div x-data="{ open: true }"
                 @toggle-all-temuans.window="open = $event.detail"
                 class="rounded-xl border border-slate-200 bg-white shadow-xs dark:border-slate-800 dark:bg-slate-900/80 overflow-hidden transition-all">
                
                {{-- TEMUAN ACCORDION HEADER (CLICKABLE DROPDOWN TOGGLE) --}}
                <button type="button" @click="open = !open"
                        class="w-full text-left bg-slate-100/80 hover:bg-slate-200/60 px-5 py-3.5 border-b border-slate-200 dark:bg-slate-800/80 dark:hover:bg-slate-800 dark:border-slate-700 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3 cursor-pointer">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="inline-flex items-center rounded bg-slate-800 px-2 py-0.5 text-xs font-mono font-bold text-white dark:bg-slate-700">
                                TEMUAN {{ $tIndex + 1 }}
                            </span>
                            <span class="rounded bg-blue-100 px-2 py-0.5 text-[11px] font-mono font-bold text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                Kode: {{ $temuan->kodeTemuan?->kode ?? '-' }}
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 leading-relaxed line-clamp-2">
                            {{ strip_tags($temuan->uraian_temuan) }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        @if($temuan->nilai_temuan > 0)
                            <span class="text-xs font-mono font-bold text-slate-900 dark:text-white bg-white px-2.5 py-1 rounded border border-slate-200 dark:border-slate-700 dark:bg-slate-800">
                                Rp {{ number_format($temuan->nilai_temuan, 0, ',', '.') }}
                            </span>
                        @endif
                        <span class="rounded bg-slate-200 px-2 py-0.5 text-[11px] font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                            {{ $temuan->recommendations->count() }} Rekomendasi
                        </span>
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600">
                            <svg class="h-3.5 w-3.5 text-slate-600 dark:text-slate-300 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </button>

                {{-- RECOMMENDATIONS UNDER TEMUAN (COLLAPSIBLE DROPDOWN BODY) --}}
                <div x-show="open" x-cloak class="p-5 divide-y divide-slate-100 dark:divide-slate-800 space-y-5">
                    @foreach($temuan->recommendations as $rIndex => $rekom)
                        @php
                            $tl = $rekom->tindakLanjuts->first();
                            $isUang = $rekom->isUang();
                            $nilaiRekom = (float) ($rekom->nilai_rekom ?? 0);
                            $totalTerbayar = (float) ($tl?->total_terbayar ?? 0);
                            $sisaKewajiban = max(0, $nilaiRekom - $totalTerbayar);
                            $progressPct = $tl ? $tl->progress() : 0;
                            $cicilans = $tl ? $tl->cicilans : collect();

                            $isDitolak = $tl && $tl->status_opd === 'draft' && !empty($tl->alasan_tolak_opd);
                            $isDikirim = $tl && $tl->status_opd === 'dikirim';
                            $isDraft = $tl && $tl->status_opd === 'draft' && empty($tl->alasan_tolak_opd);

                            $opdLabel = match(true) {
                                $isDitolak => 'Ditolak (Perlu Revisi)',
                                $isDikirim => 'Terkirim ke Inspektorat',
                                $isDraft   => 'Draft Bukti Tersimpan',
                                default    => 'Belum Upload'
                            };

                            $opdCls = match(true) {
                                $isDitolak => 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800',
                                $isDikirim => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800',
                                $isDraft   => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800',
                                default    => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700',
                            };

                            $verifCls = match($tl?->status_verifikasi) {
                                'lunas'    => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800',
                                'berjalan' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800',
                                default    => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700',
                            };

                            $defaultSkema = $tl?->jenis_penyelesaian ?? ($isUang ? 'setor_kas' : 'pengembalian_barang');
                        @endphp

                        <div class="{{ $rIndex > 0 ? 'pt-6 border-t border-slate-200/80 dark:border-slate-800' : '' }} space-y-4">
                            
                            {{-- RECOMMENDATION INFO HEADER --}}
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                <div class="space-y-1.5 max-w-3xl">
                                    <div class="flex items-center gap-2">
                                        <span class="rounded bg-blue-50 px-2 py-0.5 text-[11px] font-mono font-bold text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                            Rekomendasi {{ $rIndex + 1 }} &bull; Kode: {{ $rekom->kodeRekomendasi?->kode ?? '-' }}
                                        </span>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                            {{ $isUang ? 'Finansial / Nilai Uang' : 'Non-Finansial / Fisik & Administrasi' }}
                                        </span>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 text-justify leading-relaxed">
                                        {{ strip_tags($rekom->uraian_rekom) }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap items-center gap-2 shrink-0">
                                    <span class="rounded-md px-2.5 py-1 text-[10px] font-bold uppercase {{ $opdCls }}">
                                        OPD: {{ $opdLabel }}
                                    </span>
                                    <span class="rounded-md px-2.5 py-1 text-[10px] font-bold uppercase {{ $verifCls }}">
                                        Verif: {{ str_replace('_', ' ', $tl?->status_verifikasi ?? 'menunggu verifikasi') }}
                                    </span>
                                </div>
                            </div>

                            {{-- FINANCIAL METRICS / NON-FINANCIAL STATUS STRIP --}}
                            @if($isUang)
                                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5 dark:border-slate-800 dark:bg-slate-800/40 space-y-2.5">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                        <div>
                                            <span class="text-[10px] font-bold uppercase text-slate-400">Nilai Rekomendasi</span>
                                            <p class="font-mono font-bold text-slate-900 dark:text-white mt-0.5">
                                                Rp {{ number_format($nilaiRekom, 0, ',', '.') }}
                                            </p>
                                        </div>
                                        <div>
                                            <span class="text-[10px] font-bold uppercase text-emerald-600 dark:text-emerald-400">Telah Disetor / Lunas</span>
                                            <p class="font-mono font-bold text-emerald-700 dark:text-emerald-300 mt-0.5">
                                                Rp {{ number_format($totalTerbayar, 0, ',', '.') }}
                                            </p>
                                        </div>
                                        <div>
                                            <span class="text-[10px] font-bold uppercase text-rose-500">Sisa Kewajiban</span>
                                            <p class="font-mono font-bold text-rose-600 dark:text-rose-400 mt-0.5">
                                                Rp {{ number_format($sisaKewajiban, 0, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="space-y-1 pt-1">
                                        <div class="flex justify-between items-center text-[10px] font-semibold text-slate-500">
                                            <span>Progress Pemenuhan</span>
                                            <span class="font-mono font-bold {{ $progressPct >= 100 ? 'text-emerald-600' : 'text-blue-600' }}">{{ $progressPct }}%</span>
                                        </div>
                                        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                            <div class="h-full {{ $progressPct >= 100 ? 'bg-emerald-600' : 'bg-blue-600' }} transition-all duration-500 rounded-full"
                                                 style="width: {{ min(100, $progressPct) }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="rounded-xl border border-slate-200 bg-slate-50/70 px-4 py-2.5 dark:border-slate-800 dark:bg-slate-800/40 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-bold uppercase text-slate-400">Target Penyelesaian:</span>
                                        <span class="font-semibold text-slate-800 dark:text-slate-200">
                                            Pengembalian Fisik Barang / Aset atau Perbaikan Administrasi (SPJ)
                                        </span>
                                    </div>
                                    <span class="text-[11px] font-bold {{ $tl?->status_verifikasi === 'lunas' ? 'text-emerald-600' : 'text-amber-600' }}">
                                        {{ $tl?->status_verifikasi === 'lunas' ? 'Telah Diverifikasi Lunas / Selesai' : 'Belum Diverifikasi Selesai' }}
                                    </span>
                                </div>
                            @endif

                            {{-- REJECTION ALERT IF ANY --}}
                            @if($isDitolak)
                                <div class="rounded-xl border border-rose-200 bg-rose-50/90 p-3.5 text-xs dark:border-rose-900/50 dark:bg-rose-950/40">
                                    <div class="flex items-start gap-2.5">
                                        <svg class="h-4 w-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                        </svg>
                                        <div>
                                            <p class="font-bold text-rose-800 dark:text-rose-300">Catatan Penolakan Inspektorat:</p>
                                            <p class="mt-0.5 text-rose-700 dark:text-rose-400 font-medium leading-relaxed">{{ $tl->alasan_tolak_opd }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- RIWAYAT CICILAN TABLE (JIKA ADA DATA CICILAN) --}}
                            @if($cicilans->isNotEmpty())
                                <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900/60 p-4 space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                            <svg class="h-3.5 w-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Riwayat Setoran Cicilan ({{ $cicilans->count() }} Tahap)
                                        </h4>
                                        <span class="text-[11px] font-mono font-bold text-slate-500">
                                            Terverifikasi: Rp {{ number_format($tl->cicilanDiterima->sum('nilai_bayar'), 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div class="overflow-x-auto rounded-lg border border-slate-100 dark:border-slate-800">
                                        <table class="w-full text-left text-xs">
                                            <thead class="bg-slate-50 text-[10px] font-bold uppercase text-slate-500 dark:bg-slate-800/60">
                                                <tr>
                                                    <th class="px-3 py-2">Ke</th>
                                                    <th class="px-3 py-2">Tanggal</th>
                                                    <th class="px-3 py-2">Nominal</th>
                                                    <th class="px-3 py-2">No. Bukti / STS</th>
                                                    <th class="px-3 py-2 text-center">Status Verifikasi</th>
                                                    <th class="px-3 py-2">Catatan Verifikator</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                                                @foreach($cicilans as $c)
                                                    @php
                                                        $cBadge = match($c->status) {
                                                            'diterima' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
                                                            'ditolak'  => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800',
                                                            default    => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800',
                                                        };
                                                    @endphp
                                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                                        <td class="px-3 py-2 font-bold text-slate-800 dark:text-slate-200">#{{ $c->ke }}</td>
                                                        <td class="px-3 py-2 text-slate-600 dark:text-slate-400">
                                                            {{ $c->tanggal_bayar ? $c->tanggal_bayar->format('d/m/Y') : '-' }}
                                                        </td>
                                                        <td class="px-3 py-2 font-mono font-bold text-slate-900 dark:text-white">
                                                            Rp {{ number_format($c->nilai_bayar, 0, ',', '.') }}
                                                        </td>
                                                        <td class="px-3 py-2 font-mono text-slate-600 dark:text-slate-400">
                                                            {{ $c->nomor_bukti ?? '-' }}
                                                        </td>
                                                        <td class="px-3 py-2 text-center">
                                                            <span class="inline-flex rounded border px-2 py-0.5 text-[10px] font-bold uppercase {{ $cBadge }}">
                                                                {{ str_replace('_', ' ', $c->status) }}
                                                            </span>
                                                        </td>
                                                        <td class="px-3 py-2 text-slate-500 text-[11px]">
                                                            {{ $c->catatan_verifikasi ?? '-' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            {{-- EXISTING UPLOADED FILES AND NOTES --}}
                            @if($tl && ($tl->keterangan_pendukung_opd || $tl->attachments->isNotEmpty()))
                                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5 text-xs space-y-2.5 dark:border-slate-700 dark:bg-slate-800/40">
                                    <p class="font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 text-[10px]">
                                        Berkas Lampiran & Catatan Ter-upload
                                    </p>

                                    @if($tl->keterangan_pendukung_opd)
                                        <div>
                                            <span class="text-slate-500 dark:text-slate-400 text-[11px] font-semibold">Keterangan / Dokumen OPD:</span>
                                            <p class="mt-0.5 text-slate-800 dark:text-slate-200 bg-white dark:bg-slate-800 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700 font-medium whitespace-pre-line leading-relaxed">
                                                {{ $tl->keterangan_pendukung_opd }}
                                            </p>
                                        </div>
                                    @endif

                                    @php
                                        $opdFiles = $tl->attachments->values();
                                        $hapusUrl = route('opd.tindak-lanjut.hapus-lampiran', [$tl, '__ID__']);
                                    @endphp
                                    @if($opdFiles->isNotEmpty())
                                        <div x-data='opdFiles(@json($opdFiles->map(fn($f) => ["id" => $f->id, "name" => $f->file_name, "url" => $f->file_url])), @json($hapusUrl), @json($isDikirim))'>
                                            <div class="space-y-1.5">
                                                <template x-for="(file, idx) in files" :key="file.id">
                                                    <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs transition-colors dark:border-slate-700 dark:bg-slate-800"
                                                         :class="{'opacity-50': deleting === file.id}">
                                                        <a :href="file.url" target="_blank"
                                                           class="flex flex-1 items-center gap-2 text-slate-700 hover:text-blue-700 dark:text-slate-200 min-w-0">
                                                            <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                            </svg>
                                                            <span class="truncate font-medium" x-text="file.name"></span>
                                                        </a>
                                                        <button x-show="!readonly" type="button" @click="confirmHapus(file.id, file.name)"
                                                                class="shrink-0 rounded p-1 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-colors cursor-pointer"
                                                                :disabled="deleting === file.id" title="Hapus berkas">
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>

                                            {{-- Konfirmasi Hapus Modal --}}
                                            <div x-show="showConfirm" x-cloak
                                                 class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40"
                                                 @click.self="showConfirm = false">
                                                <div class="mx-4 w-full max-w-sm rounded-xl bg-white p-6 shadow-xl dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Hapus Lampiran</h3>
                                                    <p class="mt-2 text-xs text-slate-600 dark:text-slate-300">
                                                        Yakin ingin menghapus <span class="font-semibold" x-text="hapusName"></span>?
                                                    </p>
                                                    <div class="mt-5 flex items-center justify-end gap-3">
                                                        <button type="button" @click="showConfirm = false"
                                                                class="rounded-lg bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200 transition-colors dark:bg-slate-700 dark:text-slate-200">
                                                            Batal
                                                        </button>
                                                        <button type="button" @click="hapus"
                                                                class="rounded-lg bg-rose-600 px-4 py-2 text-xs font-bold text-white hover:bg-rose-700 transition-colors"
                                                                x-text="deleting ? 'Menghapus...' : 'Ya, Hapus'"
                                                                :disabled="deleting">
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- IF ALREADY SUBMITTED (READ ONLY NOTICE) --}}
                            @if($isDikirim)
                                <div class="rounded-xl border border-emerald-200 bg-emerald-50/80 px-4 py-3 dark:border-emerald-900/60 dark:bg-emerald-950/40 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2 text-emerald-800 dark:text-emerald-300">
                                        <svg class="h-4 w-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>Tindak lanjut rekomendasi ini telah terkirim ke Inspektorat {{ $tl->dikirim_pada ? '(' . $tl->dikirim_pada->format('d M Y H:i') . ')' : '' }}. Sedang dalam antrean verifikasi.</span>
                                    </div>
                                    <span class="rounded bg-emerald-200 px-2 py-0.5 text-[10px] font-bold text-emerald-900 dark:bg-emerald-900 dark:text-emerald-200">
                                        Terkunci
                                    </span>
                                </div>
                            @endif

                            {{-- UPLOAD / SETTLEMENT FORM (IF NOT DIKIRIM) --}}
                            @if($tl && ! $isDikirim)
                                <div x-data="{
                                        skema: '{{ $defaultSkema }}',
                                        isUang: {{ $isUang ? 'true' : 'false' }},
                                        targetNominal: {{ $nilaiRekom }},
                                        sisaNominal: {{ $sisaKewajiban }}
                                     }"
                                     class="pt-1 rounded-xl border border-slate-200 bg-slate-50/50 dark:border-slate-800 dark:bg-slate-900/50 p-4 space-y-4">
                                    
                                    <form action="{{ route('opd.tindak-lanjut.upload', $tl) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                        @csrf

                                        {{-- PILIHAN SKEMA RESOLUSI --}}
                                        <div>
                                            <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-2">
                                                Pilih Metode & Skema Tindak Lanjut:
                                            </label>
                                            
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                                @if($isUang)
                                                    {{-- Opsi 1: Setor Lunas Sekaligus --}}
                                                    <label class="relative flex items-center gap-2.5 rounded-lg border p-3 cursor-pointer transition-all"
                                                           :class="skema === 'setor_kas' ? 'border-blue-600 bg-blue-50/60 dark:border-blue-500 dark:bg-blue-950/30' : 'border-slate-200 bg-white hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800'">
                                                        <input type="radio" name="jenis_penyelesaian" value="setor_kas" x-model="skema" class="text-blue-600 focus:ring-blue-500">
                                                        <div>
                                                            <p class="text-xs font-bold text-slate-900 dark:text-white">Setor Kas Lunas Sekaligus</p>
                                                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Pembayaran tuntas melalui STS / Transfer Kas Daerah</p>
                                                        </div>
                                                    </label>

                                                    {{-- Opsi 2: Cicilan / Bertahap --}}
                                                    <label class="relative flex items-center gap-2.5 rounded-lg border p-3 cursor-pointer transition-all"
                                                           :class="skema === 'cicilan' ? 'border-blue-600 bg-blue-50/60 dark:border-blue-500 dark:bg-blue-950/30' : 'border-slate-200 bg-white hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800'">
                                                        <input type="radio" name="jenis_penyelesaian" value="cicilan" x-model="skema" class="text-blue-600 focus:ring-blue-500">
                                                        <div>
                                                            <p class="text-xs font-bold text-slate-900 dark:text-white">Setoran Cicilan / Bertahap</p>
                                                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Penyetoran bertahap dengan pencatatan per tahap</p>
                                                        </div>
                                                    </label>
                                                @else
                                                    {{-- Opsi 3: Pengembalian Barang / Aset Fisik --}}
                                                    <label class="relative flex items-center gap-2.5 rounded-lg border p-3 cursor-pointer transition-all"
                                                           :class="skema === 'pengembalian_barang' ? 'border-blue-600 bg-blue-50/60 dark:border-blue-500 dark:bg-blue-950/30' : 'border-slate-200 bg-white hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800'">
                                                        <input type="radio" name="jenis_penyelesaian" value="pengembalian_barang" x-model="skema" class="text-blue-600 focus:ring-blue-500">
                                                        <div>
                                                            <p class="text-xs font-bold text-slate-900 dark:text-white">Pengembalian Barang / Aset (BAST)</p>
                                                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Penyerahan fisik barang dengan Berita Acara (BAST)</p>
                                                        </div>
                                                    </label>

                                                    {{-- Opsi 4: Perbaikan Administrasi --}}
                                                    <label class="relative flex items-center gap-2.5 rounded-lg border p-3 cursor-pointer transition-all"
                                                           :class="skema === 'perbaikan_administrasi' ? 'border-blue-600 bg-blue-50/60 dark:border-blue-500 dark:bg-blue-950/30' : 'border-slate-200 bg-white hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800'">
                                                        <input type="radio" name="jenis_penyelesaian" value="perbaikan_administrasi" x-model="skema" class="text-blue-600 focus:ring-blue-500">
                                                        <div>
                                                            <p class="text-xs font-bold text-slate-900 dark:text-white">Perbaikan Administrasi / SPJ</p>
                                                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Perbaikan dokumen, laporan, SOP, atau bukti SPJ</p>
                                                        </div>
                                                    </label>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- DYNAMIC CONTEXTUAL FIELDS --}}
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                            {{-- Nominal Field (for financial) --}}
                                            <template x-if="isUang">
                                                <div>
                                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                        <span x-text="skema === 'cicilan' ? 'Nominal Setoran Cicilan Saat Ini (Rp)' : 'Nominal Disetor (Rp)'"></span>
                                                        <span class="text-rose-500">*</span>
                                                    </label>
                                                    <input type="number" step="any" name="nilai_tindak_lanjut"
                                                           :value="skema === 'cicilan' ? '' : (sisaNominal > 0 ? sisaNominal : targetNominal)"
                                                           placeholder="Contoh: 5000000"
                                                           class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 font-mono text-xs text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white" required>
                                                </div>
                                            </template>

                                            {{-- Nomor Bukti / BAST Field --}}
                                            <div>
                                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                    <span x-text="skema === 'pengembalian_barang' ? 'Nomor BAST / Berita Acara' : (skema === 'perbaikan_administrasi' ? 'Nomor Surat / Dokumen Perbaikan' : 'Nomor STS / Bukti Setor Bank')"></span>
                                                </label>
                                                <input type="text" name="nomor_bukti"
                                                       placeholder="Contoh: STS-01/{{ date('Y') }} atau BAST/02/Asset"
                                                       class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                            </div>

                                            {{-- Tanggal Penyetoran / Penyerahan --}}
                                            <template x-if="isUang || skema === 'pengembalian_barang'">
                                                <div>
                                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                        <span x-text="skema === 'pengembalian_barang' ? 'Tanggal BAST' : 'Tanggal Penyetoran'"></span>
                                                    </label>
                                                    <input type="date" name="tanggal_bayar" value="{{ date('Y-m-d') }}"
                                                           class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                                </div>
                                            </template>

                                            {{-- File Upload --}}
                                            <div :class="{'sm:col-span-2': !isUang && skema !== 'pengembalian_barang'}">
                                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                    Upload File Bukti Pendukung (Maks 10MB/file: PDF, JPG, PNG, DOC, XLS)
                                                </label>
                                                <input type="file" name="attachments[]" multiple
                                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                                                       class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-800 hover:file:bg-slate-300 dark:file:bg-slate-700 dark:file:text-slate-200">
                                            </div>
                                        </div>

                                        {{-- Keterangan Pendukung --}}
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                Catatan Pendukung OPD
                                            </label>
                                            <textarea name="keterangan_pendukung" rows="2"
                                                      placeholder="Jelaskan rincian penyelesaian tindak lanjut..."
                                                      class="w-full rounded-lg border border-slate-300 bg-white p-2.5 text-xs text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white">{{ old('keterangan_pendukung', $tl->keterangan_pendukung_opd) }}</textarea>
                                        </div>

                                        {{-- Action Buttons --}}
                                        <div class="flex flex-wrap items-center justify-between gap-3 pt-1 border-t border-slate-200 dark:border-slate-700">
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 transition-colors cursor-pointer shadow-xs">
                                                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                                </svg>
                                                Simpan Draft Bukti
                                            </button>

                                            @if($tl->status_opd === 'draft' || $tl->keterangan_pendukung_opd || $tl->attachments->isNotEmpty())
                                                <form action="{{ route('opd.tindak-lanjut.kirim', $tl) }}" method="POST"
                                                      onsubmit="return confirm('Kirim tindak lanjut rekomendasi ini ke Inspektorat untuk diverifikasi?')">
                                                    @csrf
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-700 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-800 transition-colors shadow-xs cursor-pointer">
                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                                        </svg>
                                                        Kirim Rekomendasi Ini
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </form>
                                </div>
                            @endif

                        </div>
                    @endforeach
                </div>

            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-12 text-center text-slate-400 dark:border-slate-800 dark:bg-slate-900/80">
                Tidak ada temuan / rekomendasi untuk LHP ini.
            </div>
        @endforelse
    </div>

</div>

<style>
[x-cloak] { display: none !important; }
</style>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('opdFiles', (files, deleteUrl, readonly) => ({
        files: files,
        deleteUrl: deleteUrl,
        readonly: readonly,
        deleting: null,
        showConfirm: false,
        hapusId: null,
        hapusName: '',

        confirmHapus(id, name) {
            this.hapusId = id;
            this.hapusName = name;
            this.showConfirm = true;
        },

        async hapus() {
            this.deleting = this.hapusId;
            try {
                const res = await fetch(this.deleteUrl.replace('__ID__', this.hapusId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!res.ok) {
                    const body = await res.json().catch(() => ({}));
                    throw new Error(body?.message || 'Gagal');
                }
                this.files = this.files.filter(f => f.id !== this.hapusId);
                this.showConfirm = false;
            } catch (e) {
                alert(e.message || 'Gagal menghapus lampiran.');
            } finally {
                this.deleting = null;
                this.hapusId = null;
                this.hapusName = '';
            }
        }
    }));
});
</script>
@endsection
