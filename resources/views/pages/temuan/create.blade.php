@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-8" x-data="temuanForm()">
    <form action="{{ route('temuan.store') }}" method="POST" enctype="multipart/form-data">
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
                <h2 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white uppercase">Tambah Temuan Baru</h2>
            </div>
        </div>

        <div class="space-y-6">
            {{-- Card: Klasifikasi --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="mb-4 text-xs font-black uppercase tracking-[0.2em] text-blue-600">01. Klasifikasi Kode</h3>
                <div class="space-y-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Kode Temuan</label>
                    <select name="kode_temuan_id" id="kode-temuan-select" required class="w-full">
                        <option value="">-- Pilih Kode Temuan --</option>
                        @foreach($kodeTemuans as $kode)
                            <option value="{{ $kode->id }}" {{ old('kode_temuan_id') == $kode->id ? 'selected' : '' }}>
                                [{{ $kode->kode }}] — {{ $kode->deskripsi }}
                            </option>
                        @endforeach
                    </select>
                    <div id="kode-info" class="hidden rounded-xl border border-blue-200 bg-blue-50/50 p-4 dark:border-blue-500/20 dark:bg-blue-500/10">
                        <div class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div class="space-y-1.5 text-sm">
                                <p id="kode-info-kode" class="font-bold text-blue-800 dark:text-blue-200"></p>
                                <p id="kode-info-kelompok" class="text-xs text-blue-600 dark:text-blue-300"></p>
                                <p id="kode-info-deskripsi" class="text-xs text-blue-700/80 dark:text-blue-300/80 leading-relaxed"></p>
                            </div>
                        </div>
                    </div>
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

            {{-- Card: Detail Barang (Opsional) --}}
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

            {{-- Card: Nilai Kerugian --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="mb-4 text-xs font-black uppercase tracking-[0.2em] text-blue-600">04. Nilai Kerugian</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase text-gray-500">Kerugian Negara</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-mono text-gray-400">Rp</span>
                            <input type="text" inputmode="numeric"
                                   x-init="$el._raw = 0"
                                   @input="const r = parseInt($el.value.replace(/[^0-9]/g, '')) || 0; _raw_kerugian_negara = r; $el.value = r || ''"
                                   @focus="$el.value = _raw_kerugian_negara || ''"
                                   @blur="$el.value = _raw_kerugian_negara ? new Intl.NumberFormat('id-ID').format(_raw_kerugian_negara) : ''"
                                   placeholder="0"
                                   class="w-full rounded-lg border border-gray-300 pl-10 p-2 text-sm font-mono dark:bg-gray-900 dark:border-gray-700 dark:text-white outline-none focus:border-blue-500">
                            <input type="hidden" name="nilai_kerugian_negara" :value="_raw_kerugian_negara">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase text-gray-500">Kerugian Daerah</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-mono text-gray-400">Rp</span>
                            <input type="text" inputmode="numeric"
                                   x-init="$el._raw = 0"
                                   @input="const r = parseInt($el.value.replace(/[^0-9]/g, '')) || 0; _raw_kerugian_daerah = r; $el.value = r || ''"
                                   @focus="$el.value = _raw_kerugian_daerah || ''"
                                   @blur="$el.value = _raw_kerugian_daerah ? new Intl.NumberFormat('id-ID').format(_raw_kerugian_daerah) : ''"
                                   placeholder="0"
                                   class="w-full rounded-lg border border-gray-300 pl-10 p-2 text-sm font-mono dark:bg-gray-900 dark:border-gray-700 dark:text-white outline-none focus:border-blue-500">
                            <input type="hidden" name="nilai_kerugian_daerah" :value="_raw_kerugian_daerah">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase text-gray-500">Kerugian Desa</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-mono text-gray-400">Rp</span>
                            <input type="text" inputmode="numeric"
                                   x-init="$el._raw = 0"
                                   @input="const r = parseInt($el.value.replace(/[^0-9]/g, '')) || 0; _raw_kerugian_desa = r; $el.value = r || ''"
                                   @focus="$el.value = _raw_kerugian_desa || ''"
                                   @blur="$el.value = _raw_kerugian_desa ? new Intl.NumberFormat('id-ID').format(_raw_kerugian_desa) : ''"
                                   placeholder="0"
                                   class="w-full rounded-lg border border-gray-300 pl-10 p-2 text-sm font-mono dark:bg-gray-900 dark:border-gray-700 dark:text-white outline-none focus:border-blue-500">
                            <input type="hidden" name="nilai_kerugian_desa" :value="_raw_kerugian_desa">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-bold uppercase text-gray-500">Kerugian BOS/BLUD</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-mono text-gray-400">Rp</span>
                            <input type="text" inputmode="numeric"
                                   x-init="$el._raw = 0"
                                   @input="const r = parseInt($el.value.replace(/[^0-9]/g, '')) || 0; _raw_kerugian_bos_blud = r; $el.value = r || ''"
                                   @focus="$el.value = _raw_kerugian_bos_blud || ''"
                                   @blur="$el.value = _raw_kerugian_bos_blud ? new Intl.NumberFormat('id-ID').format(_raw_kerugian_bos_blud) : ''"
                                   placeholder="0"
                                   class="w-full rounded-lg border border-gray-300 pl-10 p-2 text-sm font-mono dark:bg-gray-900 dark:border-gray-700 dark:text-white outline-none focus:border-blue-500">
                            <input type="hidden" name="nilai_kerugian_bos_blud" :value="_raw_kerugian_bos_blud">
                        </div>
                    </div>
                </div>
                <div class="mt-4 rounded-xl bg-gray-50 border border-gray-200 p-4 dark:bg-white/[0.03] dark:border-gray-700">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Kalkulasi Kerugian</span>
                    <div class="mt-1 text-xl font-black text-gray-900 dark:text-white">
                        Rp <span x-text="formatRupiah(totalKerugian())"></span>
                    </div>
                </div>
            </div>

            {{-- Card: Lampiran --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-xs font-black uppercase tracking-[0.2em] text-blue-600">05. Lampiran</h3>
                    <button type="button" @click="addFile()"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 shadow-sm transition-all active:scale-95">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        TAMBAH FILE
                    </button>
                </div>

                <template x-if="attachments.length === 0">
                    <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50/50 px-6 py-10 dark:border-gray-700 dark:bg-gray-800/20">
                        <svg class="mb-3 h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <p class="text-sm font-medium text-gray-400 dark:text-gray-500">Belum ada lampiran</p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-600">Klik "Tambah File" untuk mengunggah dokumen pendukung</p>
                    </div>
                </template>

                <div class="space-y-3" x-show="attachments.length > 0">
                    <template x-for="(file, index) in attachments" :key="index">
                        <div class="group relative rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition-all hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800/50 dark:hover:border-gray-600">
                            <button type="button" @click="removeFile(index)"
                                    class="absolute -right-2.5 -top-2.5 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow-sm opacity-0 group-hover:opacity-100 transition-all hover:bg-red-600 hover:scale-110">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                            <div class="flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-500 dark:bg-blue-500/10">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400">File</label>
                                        <span class="text-[10px] text-gray-400 truncate max-w-[180px]" x-text="attachments[index]?.fileName || ''"></span>
                                    </div>
                                    <input type="file" :name="`attachments[${index}][file]`"
                                           @change="attachments[index].fileName = $event.target.files[0]?.name || ''"
                                           class="block w-full text-xs text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-blue-600 hover:file:bg-blue-100 dark:file:bg-blue-500/10 dark:file:text-blue-400">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1 mt-3">Nama Dokumen</label>
                                    <input type="text" :name="`attachments[${index}][name]`" placeholder="cth: Bukti Foto, Laporan Pendukung..."
                                           class="w-full border-b border-gray-200 bg-transparent py-1 text-sm outline-none focus:border-blue-500 dark:border-gray-700 dark:text-white">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="attachments.length > 0" class="mt-4 flex justify-center">
                    <button type="button" @click="addFile()"
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors dark:text-blue-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Tambah file lain
                    </button>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
            <a href="{{ route('lhps.show', $lhp->id) }}"
               class="rounded-xl border border-gray-300 bg-white px-6 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-all">BATAL</a>
            <button type="submit"
                    class="rounded-xl bg-blue-600 px-8 py-2.5 text-sm font-bold text-white hover:bg-blue-700 shadow-lg shadow-blue-500/20 active:scale-95 transition-all">SIMPAN TEMUAN</button>
        </div>
    </form>
</div>

<script>
const KODE_TEMUANS = @json($kodeTemuanOptions);

function temuanForm() {
    return {
        _raw_kerugian_negara: 0,
        _raw_kerugian_daerah: 0,
        _raw_kerugian_desa: 0,
        _raw_kerugian_bos_blud: 0,
        attachments: [],

        totalKerugian() {
            return this._raw_kerugian_negara + this._raw_kerugian_daerah + this._raw_kerugian_desa + this._raw_kerugian_bos_blud;
        },

        addFile() {
            this.attachments.push({ file: null, name: '', fileName: '' });
        },

        removeFile(index) {
            this.attachments.splice(index, 1);
        },

        formatRupiah(number) {
            return number ? new Intl.NumberFormat('id-ID').format(number) : '0';
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const kodeSelect = document.getElementById('kode-temuan-select');
    const kodeInfo = document.getElementById('kode-info');
    const kodeInfoKode = document.getElementById('kode-info-kode');
    const kodeInfoKelompok = document.getElementById('kode-info-kelompok');
    const kodeInfoDeskripsi = document.getElementById('kode-info-deskripsi');

    const tsKode = new TomSelect(kodeSelect, {
        maxOptions: null,
        placeholder: '-- Pilih Kode Temuan --',
    });

    function showKodeInfo(value) {
        const found = KODE_TEMUANS.find(k => String(k.id) === String(value));
        if (found) {
            kodeInfoKode.textContent = `[${found.kode}] ${found.kelompok}`;
            kodeInfoKelompok.textContent = found.subKelompok ? `→ ${found.subKelompok}` : '';
            kodeInfoDeskripsi.textContent = found.deskripsi || '';
            kodeInfo.classList.remove('hidden');
        } else {
            kodeInfo.classList.add('hidden');
        }
    }

    tsKode.on('change', showKodeInfo);

    if (kodeSelect.value) {
        showKodeInfo(kodeSelect.value);
    }
});
</script>
@endsection