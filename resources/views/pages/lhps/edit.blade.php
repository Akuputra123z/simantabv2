{{-- resources/views/pages/lhps/edit.blade.php --}}
@extends('layouts.app')

@push('scripts')
    @include('components._rupiah-input')
@endpush

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8" 
     x-data="lhpEditForm()" 
     x-init="init()">

    {{-- BREADCRUMB & HEADER --}}
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                <a href="{{ route('lhps.index') }}" class="hover:text-brand-500">LHP</a>
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="max-w-[150px] truncate text-gray-900 dark:text-white">{{ $lhp->nomor_lhp }}</span>
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="text-gray-400">Edit</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Perbarui LHP</h1>
        </div>
        <div class="flex items-center gap-3">
             <button type="button" onclick="confirmRefresh()" 
                    class="rounded-lg px-4 py-2 text-sm font-medium text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                Refresh Statistik
            </button>
            <a href="{{ route('lhps.show', $lhp) }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                Kembali
            </a>
        </div>
    </div>

    {{-- ALERTS --}}
    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900/30 dark:bg-red-900/10">
            <ul class="list-inside list-disc text-sm text-red-700 dark:text-red-400">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('lhps.update', $lhp) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            
            {{-- SIDEBAR: Info Penugasan (Read Only) --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                    <h3 class="mb-4 text-[11px] font-bold uppercase tracking-wider text-gray-400">Referensi Penugasan</h3>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="mt-1 rounded-md bg-brand-50 p-2 text-brand-600 dark:bg-brand-900/20">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-medium text-gray-500 uppercase">Program Audit</p>
                                <p class="mt-0.5 text-sm font-semibold text-gray-900 dark:text-white truncate">
                                    {{ $lhp->auditAssignment->auditProgram->nama_program ?? '-' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- List Lampiran yang Sudah Ada --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="mb-4 text-[11px] font-bold uppercase tracking-wider text-gray-400">Lampiran Tersimpan</h3>
                    <div class="space-y-3">
                        @forelse ($lhp->attachments as $att)
                            <div class="group flex items-center justify-between rounded-lg border border-gray-100 p-2.5 dark:border-gray-700 hover:border-brand-200 transition-colors">
                                <div class="flex items-center gap-2 min-w-0">
                                    <svg class="h-4 w-4 text-gray-400 group-hover:text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    <span class="truncate text-xs text-gray-600 dark:text-gray-400">{{ $att->file_name ?? basename($att->file_path) }}</span>
                                </div>
                                <a href="{{ Storage::url($att->file_path) }}" target="_blank" class="text-[10px] font-bold text-brand-500 hover:text-brand-600 uppercase">Lihat</a>
                            </div>
                        @empty
                            <p class="text-center text-xs text-gray-400 py-2 italic">Belum ada file terlampir</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- MAIN FORM --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- CARD 1: INFORMASI UTAMA --}}
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800 flex items-center gap-3">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-50 text-[11px] font-bold text-brand-600 dark:bg-brand-900/30">1</span>
                        <h2 class="text-sm font-bold text-gray-800 dark:text-white/90">Informasi Utama</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div class="md:col-span-1">
                                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-gray-500">Nomor LHP <span class="text-red-500">*</span></label>
                                <input type="text" name="nomor_lhp" value="{{ old('nomor_lhp', $lhp->nomor_lhp) }}" required
                                       class="shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm focus:border-brand-300 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>
                            <div class="md:col-span-1">
                                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-gray-500">Tanggal LHP <span class="text-red-500">*</span></label>
                                <input type="date" name="tanggal_lhp" value="{{ old('tanggal_lhp', $lhp->tanggal_lhp?->format('Y-m-d')) }}" required
                                       class="shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm focus:border-brand-300 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-gray-500">Semester <span class="text-red-500">*</span></label>
                                <select name="semester" class="shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <option value="1" {{ old('semester', $lhp->semester) == 1 ? 'selected' : '' }}>Semester I</option>
                                    <option value="2" {{ old('semester', $lhp->semester) == 2 ? 'selected' : '' }}>Semester II</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-gray-500">IRBAN <span class="text-red-500">*</span></label>
                                <input type="text" name="irban" value="{{ old('irban', $lhp->irban) }}" required
                                       class="shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-gray-500">Catatan Umum</label>
                                <textarea name="catatan_umum" rows="3" class="shadow-theme-xs w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('catatan_umum', $lhp->catatan_umum) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 2: DATA TEMUAN --}}
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-50 text-[11px] font-bold text-brand-600 dark:bg-brand-900/30">2</span>
                            <h2 class="text-sm font-bold text-gray-800 dark:text-white/90">Data Temuan</h2>
                        </div>
                        <button type="button" @click="addTemuan()" class="text-[10px] font-bold text-brand-500 hover:text-brand-600 uppercase tracking-widest border border-brand-200 px-3 py-1.5 rounded-lg hover:bg-brand-50 transition-all">+ Tambah Temuan</button>
                    </div>
                    
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                   <template x-for="(temuan, index) in temuans" :key="temuan.key">
    <div class="p-6 space-y-4 bg-white dark:bg-transparent">
        <div class="flex items-center justify-between">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-gray-400 uppercase">
                Temuan #<span x-text="index + 1"></span>
            </span>
            <button type="button" @click="removeTemuan(index)" class="text-rose-500 hover:text-rose-600 p-1">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <input type="hidden" :name="`temuans[${index}][id]`" x-model="temuan.id">

            {{-- Kode Temuan --}}
            <div class="md:col-span-2">
                <label class="mb-1 block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kode Temuan</label>
                <select :name="`temuans[${index}][kode_temuan_id]`" x-model="temuan.kode_temuan_id"
                        class="shadow-theme-xs h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Pilih Kode</option>
                    @foreach ($kodeTemuans as $k)
                        <option value="{{ $k->id }}">{{ $k->kode }} - {{ $k->deskripsi }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Kondisi --}}
            <div class="md:col-span-2">
                <label class="mb-1 block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kondisi / Uraian <span class="text-red-500">*</span></label>
                <textarea :name="`temuans[${index}][kondisi]`" x-model="temuan.kondisi" rows="3"
                          placeholder="Jelaskan kondisi temuan..."
                          class="shadow-theme-xs w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
            </div>

            {{-- Kerugian Negara --}}
            <div>
                <label class="mb-1 block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kerugian Negara (Rp)</label>
                <div class="rupiah-wrap relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-gray-400">Rp</span>
                    <input type="text" inputmode="numeric" autocomplete="off" placeholder="0"
                           class="rupiah-field shadow-theme-xs h-10 w-full rounded-lg border border-gray-300 bg-transparent pl-9 pr-3 text-sm font-semibold dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                           :data-name="`temuans[${index}][nilai_kerugian_negara]`"
                           :data-value="temuan.nilai_kerugian_negara"
                           @input="temuan.nilai_kerugian_negara = $event.target._hiddenEl ? $event.target._hiddenEl.value : $event.target.value.replace(/\D/g, '')">
                </div>
            </div>

            {{-- Kerugian Daerah --}}
            <div>
                <label class="mb-1 block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kerugian Daerah (Rp)</label>
                <div class="rupiah-wrap relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-gray-400">Rp</span>
                    <input type="text" inputmode="numeric" autocomplete="off" placeholder="0"
                           class="rupiah-field shadow-theme-xs h-10 w-full rounded-lg border border-gray-300 bg-transparent pl-9 pr-3 text-sm font-semibold dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                           :data-name="`temuans[${index}][nilai_kerugian_daerah]`"
                           :data-value="temuan.nilai_kerugian_daerah"
                           @input="temuan.nilai_kerugian_daerah = $event.target._hiddenEl ? $event.target._hiddenEl.value : $event.target.value.replace(/\D/g, '')">
                </div>
            </div>

            {{-- Kerugian Desa --}}
            <div>
                <label class="mb-1 block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kerugian Desa (Rp)</label>
                <div class="rupiah-wrap relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-gray-400">Rp</span>
                    <input type="text" inputmode="numeric" autocomplete="off" placeholder="0"
                           class="rupiah-field shadow-theme-xs h-10 w-full rounded-lg border border-gray-300 bg-transparent pl-9 pr-3 text-sm font-semibold dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                           :data-name="`temuans[${index}][nilai_kerugian_desa]`"
                           :data-value="temuan.nilai_kerugian_desa"
                           @input="temuan.nilai_kerugian_desa = $event.target._hiddenEl ? $event.target._hiddenEl.value : $event.target.value.replace(/\D/g, '')">
                </div>
            </div>

            {{-- Kerugian BOS/BLUD --}}
            <div>
                <label class="mb-1 block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kerugian BOS/BLUD (Rp)</label>
                <div class="rupiah-wrap relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-gray-400">Rp</span>
                    <input type="text" inputmode="numeric" autocomplete="off" placeholder="0"
                           class="rupiah-field shadow-theme-xs h-10 w-full rounded-lg border border-gray-300 bg-transparent pl-9 pr-3 text-sm font-semibold dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                           :data-name="`temuans[${index}][nilai_kerugian_bos_blud]`"
                           :data-value="temuan.nilai_kerugian_bos_blud"
                           @input="temuan.nilai_kerugian_bos_blud = $event.target._hiddenEl ? $event.target._hiddenEl.value : $event.target.value.replace(/\D/g, '')">
                </div>
            </div>

            {{-- Total Otomatis (read-only) --}}
            <div class="md:col-span-2">
                <label class="mb-1 block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Nilai Kerugian (Otomatis)</label>
                <div class="flex items-center gap-2 rounded-lg bg-gray-50 border border-gray-200 px-3 py-2.5 dark:bg-gray-900 dark:border-gray-700">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-200"
                          x-text="'Rp\u00a0' + formatRupiah(
                              (parseInt(temuan.nilai_kerugian_negara)  || 0) +
                              (parseInt(temuan.nilai_kerugian_daerah)  || 0) +
                              (parseInt(temuan.nilai_kerugian_desa)    || 0) +
                              (parseInt(temuan.nilai_kerugian_bos_blud)|| 0)
                          )">
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
                        <div x-show="temuans.length === 0" class="p-10 text-center">
                            <p class="text-xs text-gray-400 italic italic">Tidak ada data temuan. Klik tambah untuk memulai.</p>
                        </div>
                    </div>
                </div>

                {{-- CARD 3: LAMPIRAN BARU --}}
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-50 text-[11px] font-bold text-brand-600 dark:bg-brand-900/30">3</span>
                            <h2 class="text-sm font-bold text-gray-800 dark:text-white/90">Lampiran Baru</h2>
                        </div>
                        <button type="button" @click="addLampiran()" class="text-[10px] font-bold text-brand-500 hover:text-brand-600 uppercase tracking-widest border border-brand-200 px-3 py-1.5 rounded-lg hover:bg-brand-50 transition-all">+ Tambah File</button>
                    </div>
                    <div class="p-6 space-y-4">
                        <template x-for="(file, index) in attachments" :key="file.key">
                            <div class="flex flex-col gap-4 rounded-xl border border-dashed border-gray-200 p-4 dark:border-gray-700 md:flex-row md:items-end bg-gray-50/30 dark:bg-white/[0.01]">
                                <div class="flex-1">
                                    <label class="mb-1 block text-[10px] font-bold text-gray-400 uppercase">Pilih File</label>
                                    <input type="file" :name="`attachments[${index}][file_path]`" 
                                           class="w-full text-xs text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-[10px] file:font-bold file:text-brand-700 dark:file:bg-brand-900/20">
                                </div>
                                <div class="flex-1">
                                    <label class="mb-1 block text-[10px] font-bold text-gray-400 uppercase">Label File</label>
                                    <input type="text" :name="`attachments[${index}][file_name]`" placeholder="Nama Dokumen" 
                                           class="h-9 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                </div>
                                <button type="button" @click="removeLampiran(index)" class="mb-1 text-rose-500 hover:bg-rose-50 p-1.5 rounded-lg transition-colors">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                        <div x-show="attachments.length === 0" class="text-center py-4">
                            <p class="text-xs text-gray-400 italic">Tambahkan file pendukung jika diperlukan (PDF/Gambar).</p>
                        </div>
                    </div>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="flex items-center justify-end gap-3 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <a href="{{ route('lhps.show', $lhp) }}" class="rounded-lg px-6 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">Batal</a>
                    <button type="submit" id="btn-submit" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-8 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-500/20 hover:bg-brand-600 transition-all active:scale-[0.98]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span id="btn-text">Simpan Perubahan</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function lhpEditForm() {
    return {
        // Load data temuan yang sudah ada dari database
        temuans: @json($lhp->temuans).map(t => ({
            key: 'old-' + t.id,
            id: t.id,
            kode_temuan_id: t.kode_temuan_id,
            nilai_kerugian_negara:   parseInt(t.nilai_kerugian_negara)   || 0,
            nilai_kerugian_daerah:   parseInt(t.nilai_kerugian_daerah)   || 0,
            nilai_kerugian_desa:     parseInt(t.nilai_kerugian_desa)     || 0,
            nilai_kerugian_bos_blud: parseInt(t.nilai_kerugian_bos_blud) || 0,
            kondisi: t.kondisi
        })),
        attachments: [],

        // Helper untuk format angka ke Rupiah di UI
        formatRupiah(number) {
            if (!number) return '0';
            return new Intl.NumberFormat('id-ID').format(number);
        },

        addTemuan() {
            this.temuans.push({
                key: Date.now(),
                id: null,
                kode_temuan_id: '',
                nilai_kerugian_negara: 0,
                nilai_kerugian_daerah: 0,
                nilai_kerugian_desa: 0,
                nilai_kerugian_bos_blud: 0,
                kondisi: ''
            });
            this.reinitRupiah();
        },

        removeTemuan(index) {
            if(confirm('Hapus temuan ini? Data akan benar-benar terhapus saat form disimpan.')) {
                this.temuans.splice(index, 1);
            }
        },

        addLampiran() {
            this.attachments.push({ key: Date.now() });
        },

        removeLampiran(index) {
            this.attachments.splice(index, 1);
        },

        reinitRupiah() {
            this.$nextTick(() => {
                // Bersihkan flag init agar script masking bisa mendeteksi elemen baru
                document.querySelectorAll('.rupiah-field').forEach(el => {
                    if (!el._hiddenEl) el.removeAttribute('data-ri-init');
                });
                // Panggil library masking
                if (window.RupiahInput) {
                    window.RupiahInput.initAll();
                }
            });
        },

        init() {
            // Inisialisasi awal
            setTimeout(() => this.reinitRupiah(), 150);
            
            // Loading state saat submit
            const form = document.querySelector('form');
            form?.addEventListener('submit', () => {
                const btn = document.getElementById('btn-submit');
                const txt = document.getElementById('btn-text');
                if(btn) {
                    btn.disabled = true;
                    btn.classList.add('opacity-70', 'cursor-not-allowed');
                }
                if(txt) txt.textContent = 'Menyimpan...';
            });
        }
    }
}

function confirmRefresh() {
    if (confirm('Refresh statistik LHP ini? Perubahan yang belum disimpan akan hilang.')) {
        window.location.href = "{{ route('lhps.refresh', $lhp) }}";
    }
}
</script>
@endsection