@extends('layouts.app')

@section('content')

{{-- Header Halaman --}}
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Buat Laporan Hasil Reviu (LHR) Baru</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Formulir lengkap tersinkronisasi dengan tata naskah dinas pengawasan dan template 26 halaman resmi Kabupaten Rembang
        </p>
    </div>
    <div>
        <a href="{{ route('lhr.index') }}"
           class="inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition">
            &larr; Kembali ke Daftar LHR
        </a>
    </div>
</div>

{{-- Container Form Full-Width --}}
<div class="w-full rounded-2xl border border-gray-200 bg-white p-6 sm:p-8 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <form method="POST" action="{{ route('lhr.store') }}" class="space-y-8" id="formCreateLhr">
        @csrf

        {{-- ==================== SEKSI 1: DASAR PENUGASAN & KLASIFIKASI ==================== --}}
        <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-5 sm:p-6 dark:border-gray-800/80 dark:bg-gray-800/30">
            <div class="mb-5 border-b border-gray-200/80 pb-3 dark:border-gray-700/80">
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white shadow-sm">1</span>
                    Dasar Penugasan & Klasifikasi Dokumen
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pilih Surat Perintah Tugas (SPT) dan jenis pengawasan perencanaan yang mendasari naskah LHR</p>
            </div>

            <div class="space-y-6">
                {{-- 1. Pilih SPT (Full-Width) --}}
                <div class="w-full">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                        Surat Perintah Tugas (SPT) Reviu <span class="text-red-500">*</span>
                    </label>
                    <select name="audit_assignment_id" id="selectSpt" required
                            class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                        <option value="">-- Pilih Surat Perintah Tugas (SPT) Pengawasan --</option>
                        @foreach($assignments as $asg)
                            <option value="{{ $asg->id }}" 
                                    data-mulai="{{ $asg->tanggal_mulai ? $asg->tanggal_mulai->format('Y-m-d') : '' }}"
                                    data-selesai="{{ $asg->tanggal_selesai ? $asg->tanggal_selesai->format('Y-m-d') : '' }}"
                                    data-nomor="{{ $asg->nomor_surat }}"
                                    @selected(old('audit_assignment_id') == $asg->id)>
                                Nomor SPT: {{ $asg->nomor_surat ?? 'SPT Tanpa Nomor' }} &bull; Tim: {{ $asg->nama_tim ?? 'Tim Reviu' }} &bull; Ketua: {{ $asg->ketuaTim?->name ?? '-' }} ({{ $asg->tanggal_mulai ? $asg->tanggal_mulai->format('d/m/Y') : '-' }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Nomor dan tanggal SPT penugasan ini akan otomatis tercantum pada Bagian B (Dasar Hukum Reviu).</p>
                    @error('audit_assignment_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 2. Jenis Dokumen Reviu (Full-Width dengan Card Selector Luas agar tidak terpotong) --}}
                <div class="w-full">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Jenis Dokumen Reviu <span class="text-red-500">*</span>
                    </label>

                    {{-- Pilihan Visual Card Responsif & Luas --}}
                    <div class="grid grid-cols-1 gap-3 lg:grid-cols-3 mb-2" id="cardContainerJenis">
                        @foreach($jenisDokumens as $jd)
                            @php
                                $isRkpd = $jd->kode === 'RKPD';
                                $isKua = $jd->kode === 'KUA_PPAS';
                                $isDefault = (old('jenis_dokumen_reviu_id', 2) == $jd->id);
                            @endphp
                            <div class="jenis-card cursor-pointer rounded-xl border p-4 transition relative {{ $isDefault ? 'border-blue-600 bg-blue-50/70 ring-2 ring-blue-600/30 dark:border-blue-500 dark:bg-blue-950/30' : 'border-gray-200 bg-white hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800/80 dark:hover:border-gray-600' }}"
                                 data-id="{{ $jd->id }}" data-kode="{{ $jd->kode }}">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $jd->kode }}</span>
                                            @if($isRkpd || $isKua)
                                                <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                                                    ★ Prioritas
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mt-1 leading-relaxed">
                                            {{ $jd->nama }}
                                        </p>
                                    </div>
                                    <div class="check-circle mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border {{ $isDefault ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300 dark:border-gray-600' }}">
                                        @if($isDefault)
                                            <span class="text-[9px] leading-none">&#10003;</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">
                                    @if($isRkpd)
                                        Katalog 14 Butir Pengujian & Template 26 Halaman Rembang
                                    @elseif($isKua)
                                        Katalog 11 Butir Pengujian & Penyelarasan Pagu
                                    @else
                                        Katalog 6 Butir Pengujian Standar Harga Satuan
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Native Select Luas (Full-Width, Tersembunyi tapi Tetap Menjadi Value Form Utama) --}}
                    <select name="jenis_dokumen_reviu_id" id="selectJenisDokumen" required
                            class="h-11 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                        @foreach($jenisDokumens as $jd)
                            <option value="{{ $jd->id }}" data-kode="{{ $jd->kode }}" @selected(old('jenis_dokumen_reviu_id', 2) == $jd->id)>
                                {{ $jd->kode }} — {{ $jd->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('jenis_dokumen_reviu_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 3. Perangkat Daerah (OPD) Pengampu (Full-Width Luas) --}}
                <div class="w-full">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                        Perangkat Daerah Pengampu (Auditee) <span class="text-red-500">*</span>
                    </label>
                    <select name="unit_diperiksa_id" id="selectUnit" required
                            class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                        <option value="">-- Pilih Perangkat Daerah Pengampu --</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" data-nama="{{ $u->nama_unit }}" @selected(old('unit_diperiksa_id', 315) == $u->id)>
                                {{ $u->nama_unit }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                        Untuk dokumen RKPD, pengampu utama perencanaan pembangunan daerah adalah <strong>Badan Perencanaan Pembangunan Daerah (BAPPEDA)</strong>.
                    </p>
                    @error('unit_diperiksa_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ==================== SEKSI 2: IDENTITAS DOKUMEN & LEGALITAS RESMI ==================== --}}
        <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-5 sm:p-6 dark:border-gray-800/80 dark:bg-gray-800/30">
            <div class="mb-5 border-b border-gray-200/80 pb-3 dark:border-gray-700/80">
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white shadow-sm">2</span>
                    Identitas Naskah LHR & Pejabat Penandatangan
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Penomoran dokumen formal dan pengesahan laporan hasil reviu sesuai tata naskah dinas Kabupaten Rembang</p>
            </div>

            <div class="space-y-5">
                {{-- Baris 1: Nomor LHR & Tanggal Terbit LHR (2 Kolom Luas) --}}
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    {{-- Nomor LHR --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                            Nomor Dokumen LHR Resmi
                        </label>
                        <input type="text" name="nomor_lhr" value="{{ old('nomor_lhr', $suggestedNomorLhr ?? '') }}"
                               placeholder="Contoh: 700/027/REVIU/INSP/2026 (atau kosongkan untuk otomatis)"
                               class="h-12 w-full rounded-xl border @error('nomor_lhr') border-red-500 @else border-gray-300 @enderror bg-white px-4 text-sm font-medium text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Tercantum pada Halaman Sampul Depan (Cover) dan Surat Pengantar resmi. Nomor terisi otomatis dengan nomor unik berikutnya.</p>
                        @error('nomor_lhr')
                            <p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal Terbit LHR --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                            Tanggal Pengesahan / Terbit LHR
                        </label>
                        <input type="date" name="tanggal_lhr" value="{{ old('tanggal_lhr', '2026-05-11') }}"
                               class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Tanggal pengesahan laporan oleh Inspektur Daerah Kabupaten Rembang.</p>
                        @error('tanggal_lhr')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Baris 2: Pejabat Penandatangan (Full-Width Luas agar Nama & Gelar Lengkap Tidak Terpotong) --}}
                <div class="w-full">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                        Pejabat Penandatangan LHR (Inspektur Daerah)
                    </label>
                    <div class="relative">
                        <select name="ditandatangani_oleh" id="selectSigner"
                                class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                            @foreach($signers as $sgn)
                                <option value="{{ $sgn->id }}" @selected(old('ditandatangani_oleh', 77) == $sgn->id || str_contains(strtoupper($sgn->name), 'IMUNG'))>
                                    {{ $sgn->name }} — {{ $sgn->jabatan ?? 'Inspektur Daerah' }} (NIP. {{ $sgn->nip ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                        Pejabat yang bertindak menandatangani lembar pengesahan Bagian J (Apresiasi) pada Halaman 24 naskah LHR.
                    </p>
                    @error('ditandatangani_oleh')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ==================== SEKSI 3: SURAT PERMOHONAN DARI OPD ==================== --}}
        <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-5 sm:p-6 dark:border-gray-800/80 dark:bg-gray-800/30">
            <div class="mb-5 border-b border-gray-200/80 pb-3 dark:border-gray-700/80">
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-600 text-xs font-bold text-white shadow-sm">3</span>
                    Surat Permohonan Reviu dari Perangkat Daerah
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Surat resmi permohonan reviu dokumen dari Kepala BAPPEDA kepada Inspektur Daerah</p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                {{-- Nomor Permohonan OPD --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                        Nomor Surat Permohonan OPD
                    </label>
                    <input type="text" name="permohonan_nomor" value="{{ old('permohonan_nomor', '000.7/250/2026') }}" 
                           placeholder="Contoh: 000.7/250/2026"
                           class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Dikutip pada Bagian B (Dasar Hukum Reviu butir ke-10).</p>
                    @error('permohonan_nomor')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tanggal Permohonan OPD --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                        Tanggal Surat Permohonan OPD
                    </label>
                    <input type="date" name="permohonan_tanggal" value="{{ old('permohonan_tanggal', '2026-04-28') }}"
                           class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Tanggal surat permohonan yang diajukan oleh Kepala BAPPEDA.</p>
                    @error('permohonan_tanggal')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ==================== SEKSI 4: TAHUN ANGGARAN & JADWAL PELAKSANAAN ==================== --}}
        <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-5 sm:p-6 dark:border-gray-800/80 dark:bg-gray-800/30">
            <div class="mb-5 border-b border-gray-200/80 pb-3 dark:border-gray-700/80">
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-purple-600 text-xs font-bold text-white shadow-sm">4</span>
                    Tahun Anggaran & Jadwal Pelaksanaan Reviu
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Tahun rencana pembangunan yang direviu dan rentang waktu penelaahan dokumen oleh tim APIP</p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                {{-- Tahun Anggaran --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                        Tahun Anggaran Dokumen <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="tahun_anggaran" value="{{ old('tahun_anggaran', 2027) }}" required min="2020" max="2035"
                           class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Contoh: <strong>2027</strong> untuk dokumen RKPD Tahun 2027.</p>
                    @error('tahun_anggaran')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tanggal Mulai --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                        Tanggal Mulai Reviu
                    </label>
                    <input type="date" name="tanggal_mulai" id="inputTanggalMulai" value="{{ old('tanggal_mulai', '2026-04-30') }}"
                           class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Tercantum pada Bagian F (Waktu Pelaksanaan Reviu).</p>
                    @error('tanggal_mulai')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tanggal Selesai --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                        Tanggal Selesai Reviu
                    </label>
                    <input type="date" name="tanggal_selesai" id="inputTanggalSelesai" value="{{ old('tanggal_selesai', '2026-05-11') }}"
                           class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Batas akhir pelaksanaan reviu dokumen oleh tim APIP.</p>
                    @error('tanggal_selesai')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Action Buttons & Status Footer --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-t border-gray-100 pt-6 dark:border-gray-800">
            <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2">
                <span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Template Resmi 26 Halaman, Bagian A–J, dan 14 Butir Pengujian Rembang otomatis aktif.</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('lhr.index') }}"
                   class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 shadow-md shadow-blue-600/20 transition">
                    <span>Simpan & Buka Ruang Kerja LHR</span>
                    <span class="text-lg leading-none">&rarr;</span>
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Interaktif Helper Script --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectSpt = document.getElementById('selectSpt');
        const inputMulai = document.getElementById('inputTanggalMulai');
        const inputSelesai = document.getElementById('inputTanggalSelesai');
        const selectJenis = document.getElementById('selectJenisDokumen');
        const selectUnit = document.getElementById('selectUnit');
        const cards = document.querySelectorAll('.jenis-card');

        // Klik Card Jenis Dokumen -> sinkronkan ke select
        cards.forEach(card => {
            card.addEventListener('click', function() {
                const id = this.dataset.id;
                const kode = this.dataset.kode;
                
                // Update select value
                if (selectJenis) {
                    selectJenis.value = id;
                    selectJenis.dispatchEvent(new Event('change'));
                }

                // Update styling cards
                cards.forEach(c => {
                    c.classList.remove('border-blue-600', 'bg-blue-50/70', 'ring-2', 'ring-blue-600/30', 'dark:border-blue-500', 'dark:bg-blue-950/30');
                    c.classList.add('border-gray-200', 'bg-white', 'dark:border-gray-700', 'dark:bg-gray-800/80');
                    const circle = c.querySelector('.check-circle');
                    if (circle) {
                        circle.className = 'check-circle mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border border-gray-300 dark:border-gray-600';
                        circle.innerHTML = '';
                    }
                });

                this.classList.remove('border-gray-200', 'bg-white', 'dark:border-gray-700', 'dark:bg-gray-800/80');
                this.classList.add('border-blue-600', 'bg-blue-50/70', 'ring-2', 'ring-blue-600/30', 'dark:border-blue-500', 'dark:bg-blue-950/30');
                const myCircle = this.querySelector('.check-circle');
                if (myCircle) {
                    myCircle.className = 'check-circle mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border border-blue-600 bg-blue-600 text-white';
                    myCircle.innerHTML = '<span class="text-[9px] leading-none">&#10003;</span>';
                }
            });
        });

        // Sinkronkan Select Jenis Dokumen -> update Card styling & Auto-select BAPPEDA
        if (selectJenis) {
            selectJenis.addEventListener('change', function() {
                const val = this.value;
                const opt = this.options[this.selectedIndex];
                const kode = opt ? opt.dataset.kode : '';

                cards.forEach(c => {
                    if (c.dataset.id === val) {
                        c.classList.remove('border-gray-200', 'bg-white', 'dark:border-gray-700', 'dark:bg-gray-800/80');
                        c.classList.add('border-blue-600', 'bg-blue-50/70', 'ring-2', 'ring-blue-600/30', 'dark:border-blue-500', 'dark:bg-blue-950/30');
                        const circle = c.querySelector('.check-circle');
                        if (circle) {
                            circle.className = 'check-circle mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border border-blue-600 bg-blue-600 text-white';
                            circle.innerHTML = '<span class="text-[9px] leading-none">&#10003;</span>';
                        }
                    } else {
                        c.classList.remove('border-blue-600', 'bg-blue-50/70', 'ring-2', 'ring-blue-600/30', 'dark:border-blue-500', 'dark:bg-blue-950/30');
                        c.classList.add('border-gray-200', 'bg-white', 'dark:border-gray-700', 'dark:bg-gray-800/80');
                        const circle = c.querySelector('.check-circle');
                        if (circle) {
                            circle.className = 'check-circle mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border border-gray-300 dark:border-gray-600';
                            circle.innerHTML = '';
                        }
                    }
                });

                // Jika memilih RKPD, otomatis arahkan OPD ke BAPPEDA
                if (kode === 'RKPD' && selectUnit) {
                    for (let i = 0; i < selectUnit.options.length; i++) {
                        if (selectUnit.options[i].text.includes('Perencanaan Pembangunan')) {
                            selectUnit.selectedIndex = i;
                            break;
                        }
                    }
                }
            });
        }

        // Otomatis isi rentang tanggal saat SPT dipilih
        if (selectSpt) {
            selectSpt.addEventListener('change', function() {
                const opt = selectSpt.options[selectSpt.selectedIndex];
                if (opt && opt.dataset.mulai && inputMulai) {
                    inputMulai.value = opt.dataset.mulai;
                }
                if (opt && opt.dataset.selesai && inputSelesai) {
                    inputSelesai.value = opt.dataset.selesai;
                    const inputTanggalLhr = document.querySelector('input[name="tanggal_lhr"]');
                    if (inputTanggalLhr && (!inputTanggalLhr.value || inputTanggalLhr.value < opt.dataset.selesai)) {
                        inputTanggalLhr.value = opt.dataset.selesai;
                    }
                }
            });
        }
    });
</script>

@endsection
