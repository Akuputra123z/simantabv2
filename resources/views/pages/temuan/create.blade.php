@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-8" x-data="temuanForm()">
    <form action="{{ route('temuans.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="lhp_id" value="{{ $lhp->id }}">

        {{-- Header Section --}}
        <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <nav class="mb-2 flex items-center gap-2 text-sm text-gray-500">
                    <a href="{{ route('lhps.index') }}" class="hover:text-blue-600">LHP</a>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                    <a href="{{ route('lhps.show', $lhp->id) }}" class="hover:text-blue-600">{{ $lhp->nomor_lhp }}</a>
                </nav>
                <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white uppercase">Tambah Temuan Baru</h2>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('lhps.show', $lhp->id) }}" class="rounded-xl border border-gray-300 bg-white px-6 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-all">BATAL</a>
                <button type="submit" class="rounded-xl bg-blue-600 px-8 py-2.5 text-sm font-bold text-white hover:bg-blue-700 shadow-lg shadow-blue-500/20 active:scale-95 transition-all">SIMPAN TEMUAN</button>
            </div>
        </div>

        {{-- Main Bento Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            
            {{-- KIRI: Klasifikasi & Kondisi (8 Kolom) --}}
            <div class="md:col-span-8 space-y-6">
                {{-- Card: Klasifikasi --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="mb-4 text-xs font-black uppercase tracking-[0.2em] text-blue-600">01. Klasifikasi Kode</h3>
                    <div class="space-y-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Kode Temuan</label>
                        <select name="kode_temuan_id" required class="w-full rounded-xl border border-gray-300 bg-gray-50 p-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="">-- Pilih Kode Temuan --</option>
                            @foreach($kodeTemuans as $kode)
                                <option value="{{ $kode->id }}" {{ old('kode_temuan_id') == $kode->id ? 'selected' : '' }}>
                                    [{{ $kode->kode }}] — {{ $kode->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Card: Narasi (Kondisi, Sebab, Akibat) --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="mb-4 text-xs font-black uppercase tracking-[0.2em] text-blue-600">02. Uraian Temuan</h3>
                    <div class="space-y-6">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Kondisi <span class="text-red-500">*</span></label>
                            <textarea name="kondisi" rows="4" required placeholder="Uraikan kondisi temuan secara mendetail..." class="w-full rounded-xl border border-gray-300 p-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white outline-none focus:border-blue-500">{{ old('kondisi') }}</textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Sebab</label>
                                <textarea name="sebab" rows="4" placeholder="Uraikan penyebab..." class="w-full rounded-xl border border-gray-300 p-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white outline-none focus:border-blue-500">{{ old('sebab') }}</textarea>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Akibat</label>
                                <textarea name="akibat" rows="4" placeholder="Uraikan dampak/akibat..." class="w-full rounded-xl border border-gray-300 p-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white outline-none focus:border-blue-500">{{ old('akibat') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Detail Barang (Opsional - Bento Style) --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="mb-4 text-xs font-black uppercase tracking-[0.2em] text-blue-600">03. Detail Fisik / Barang (Opsional)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Barang</label>
                            <input type="text" name="nama_barang" value="{{ old('nama_barang') }}" placeholder="Contoh: Laptop, Kendaraan Dinas, dsb" class="w-full rounded-xl border border-gray-300 p-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah / Volume</label>
                            <input type="number" name="jumlah_barang" value="{{ old('jumlah_barang') }}" class="w-full rounded-xl border border-gray-300 p-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Kondisi Barang</label>
                            <select name="kondisi_barang" class="w-full rounded-xl border border-gray-300 p-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white outline-none focus:border-blue-500">
                                <option value="">-- Pilih Kondisi --</option>
                                <option value="baik">Baik</option>
                                <option value="rusak_ringan">Rusak Ringan</option>
                                <option value="rusak_berat">Rusak Berat</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KANAN: Nilai & Lampiran (4 Kolom) --}}
            <div class="md:col-span-4 space-y-6">
                {{-- Card: Nilai Finansial --}}
                <div class="rounded-2xl border border-blue-100 bg-blue-50/50 p-6 dark:border-blue-900/30 dark:bg-blue-900/10">
                    <h3 class="mb-4 text-xs font-black uppercase tracking-[0.2em] text-blue-700 dark:text-blue-400">04. Nilai Kerugian</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-[10px] font-bold uppercase text-gray-500">Nilai Temuan (Total)</label>
                            <input type="number" step="0.01" name="nilai_temuan" x-model.number="nilai_temuan" class="w-full rounded-lg border border-gray-300 p-2.5 text-sm font-mono dark:bg-gray-900">
                        </div>
                        <div class="pt-2 border-t border-blue-100 dark:border-blue-900/30">
                            <label class="mb-1 block text-[10px] font-bold uppercase text-gray-500">Kerugian Negara</label>
                            <input type="number" step="0.01" name="nilai_kerugian_negara" x-model.number="kerugian_negara" class="w-full rounded-lg border border-gray-300 p-2 text-sm font-mono dark:bg-gray-900">
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-bold uppercase text-gray-500">Kerugian Daerah</label>
                            <input type="number" step="0.01" name="nilai_kerugian_daerah" x-model.number="kerugian_daerah" class="w-full rounded-lg border border-gray-300 p-2 text-sm font-mono dark:bg-gray-900">
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-bold uppercase text-gray-500">Kerugian Desa</label>
                            <input type="number" step="0.01" name="nilai_kerugian_desa" x-model.number="kerugian_desa" class="w-full rounded-lg border border-gray-300 p-2 text-sm font-mono dark:bg-gray-900">
                        </div>

                        {{-- Total Live Calculation --}}
                        <div class="mt-6 rounded-xl bg-blue-600 p-4 text-white">
                            <span class="text-[10px] font-bold uppercase opacity-80">Total Kalkulasi Kerugian</span>
                            <div class="text-xl font-black">
                                Rp <span x-text="formatRupiah(totalKerugian())"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Lampiran Repeater --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-xs font-black uppercase tracking-[0.2em] text-blue-600">05. Lampiran</h3>
                        <button type="button" @click="addFile()" class="text-xs font-bold text-blue-600 hover:underline">+ TAMBAH</button>
                    </div>
                    
                    <div class="space-y-3">
                        <template x-for="(file, index) in attachments" :key="index">
                            <div class="group relative rounded-lg border border-gray-100 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800/50">
                                <button type="button" @click="removeFile(index)" class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow-sm transition-transform group-hover:scale-110">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                                <input type="file" :name="`attachments[${index}][file]`" class="mb-2 w-full text-[10px]">
                                <input type="text" :name="`attachments[${index}][name]`" placeholder="Nama Dokumen" class="w-full border-b border-gray-200 bg-transparent text-xs outline-none focus:border-blue-500 dark:border-gray-700">
                            </div>
                        </template>
                        <div x-show="attachments.length === 0" class="py-4 text-center text-xs text-gray-400 italic italic">
                            Belum ada lampiran.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function temuanForm() {
    return {
        // Data angka untuk kalkulasi
        nilai_temuan: 0,
        kerugian_negara: 0,
        kerugian_daerah: 0,
        kerugian_desa: 0,
        kerugian_bos: 0,

        // Lampiran
        attachments: [],

        totalKerugian() {
            return this.kerugian_negara + this.kerugian_daerah + this.kerugian_desa;
        },

        addFile() {
            this.attachments.push({ file: null, name: '' });
        },

        removeFile(index) {
            this.attachments.splice(index, 1);
        },

        formatRupiah(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }
    }
}
</script>

<style>
    /* Custom font style untuk kesan Swiss Style */
    body { font-family: 'Inter', sans-serif; }
    .font-black { font-weight: 900; }
</style>
@endsection