@extends('layouts.app')

@section('content')
<style>
    input[type="date"] { color-scheme: light; }
    .dark input[type="date"] { color-scheme: dark; }
    input[type="date"]::-webkit-calendar-picker-indicator { display: block; }
</style>
<div class="mx-auto max-w-3xl px-4 pb-12">
    
    {{-- Notifikasi Error Validasi --}}
    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4">
            <h3 class="text-sm font-bold text-red-800 uppercase tracking-tight mb-2">Gagal Menyimpan:</h3>
            <ul class="list-disc pl-5 text-xs text-red-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('audit-program-detail.update', $detail->id) }}" method="POST" x-data="{ kategori: '{{ $kategori }}' }">
        @csrf
        @method('PUT')

        {{-- Header --}}
        <div class="mb-8 px-2">
            <h2 class="text-3xl font-medium tracking-tight text-gray-900 dark:text-white">Edit Sub-Program</h2>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                Program Induk: <span class="font-bold text-blue-600 uppercase">{{ $auditProgram->nama_program }}</span>
            </p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-800/50">
           

            <div class="p-8 space-y-6">
                {{-- Nama Detail Program --}}
                <div>
                    <label class="block text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-2 font-black">Nama Detail Program Kerja</label>
                    <input type="text" name="nama_detail_program" value="{{ old('nama_detail_program', $detail->nama_detail_program) }}" 
                           class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3.5 text-sm focus:border-blue-500 transition-all dark:bg-gray-900 dark:text-white" 
                           placeholder="Contoh: Audit Kinerja Pelayanan Publik" required>
                </div>

                {{-- Jenis Kegiatan --}}
                <div>
                    <label class="block text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-2 font-black">Jenis Kegiatan</label>
                    <select name="jenis_kegiatan" class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3.5 text-sm focus:border-blue-500 dark:bg-gray-900" required>
                        <option value="">Pilih Jenis</option>
                        @foreach(['Audit', 'Review', 'Evaluasi', 'Pemantauan','Tindak Lanjut'] as $jenis)
                            <option value="{{ $jenis }}" {{ old('jenis_kegiatan', $detail->jenis_kegiatan) == $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tingkat Risiko --}}
                <div>
                    <label class="block text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-2 font-black">Tingkat Risiko</label>
                    <div class="flex items-center h-[52px] gap-2 px-2 bg-gray-50/50 border-2 border-gray-100 rounded-xl dark:bg-gray-900">
                        @foreach(['Rendah', 'Sedang', 'Tinggi'] as $level)
                        <label class="flex items-center gap-1.5 cursor-pointer px-3 py-1.5 rounded-lg hover:bg-white transition-all">
                            <input type="radio" name="tingkat_resiko" value="{{ $level }}" 
                                   {{ old('tingkat_resiko', $detail->tingkat_resiko ?? 'Rendah') == $level ? 'checked' : '' }} 
                                   class="w-3.5 h-3.5 border-gray-300 text-blue-600">
                            <span class="text-[10px] font-bold text-gray-500 uppercase">{{ $level }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Objek Pengawasan --}}
                <div>
                    <label class="block text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-2 font-black">Objek Pengawasan (Lokasi)</label>
                    <input type="text" name="objek_pengawasan" value="{{ old('objek_pengawasan', $detail->objek_pengawasan) }}" 
                           class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3.5 text-sm focus:border-blue-500 dark:bg-gray-900"
                           placeholder="Nama Unit Kerja/Instansi">
                </div>

                {{-- Personil (PKPT only) --}}
                <div x-show="kategori === 'PKPT'" x-transition>
                    <label class="block text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-2 font-black">Personil / Auditor</label>
                    <input type="text" name="personil" value="{{ old('personil', $detail->personil) }}" 
                           class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3.5 text-sm focus:border-blue-500 dark:bg-gray-900" 
                           placeholder="Contoh: 4 Orang">
                </div>

                {{-- Anggaran --}}
                <div>
                    <label class="block text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-2 font-black">Anggaran (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-sm">Rp</span>
                        <input type="text" id="display_anggaran"
                               class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/50 pl-12 pr-4 py-3.5 text-sm font-bold text-blue-600 focus:ring-4 focus:ring-blue-500/10 transition-all dark:bg-gray-900"
                               placeholder="0">
                        <input type="hidden" name="anggaran" id="real_anggaran" value="{{ old('anggaran', (int) $detail->anggaran) }}">
                    </div>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-2 font-black">Status</label>
                    <select name="status" class="w-full rounded-xl border-2 border-blue-100 bg-blue-50/30 px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-blue-600 focus:border-blue-500 transition-all dark:bg-gray-900" required>
                        <option value="rencana" {{ old('status', $detail->status) == 'rencana' ? 'selected' : '' }}>RENCANA (DRAFT)</option>
                        <option value="aktif" {{ old('status', $detail->status) == 'aktif' ? 'selected' : '' }}>AKTIF (RUNNING)</option>
                    </select>
                </div>

                {{-- Ruang Lingkup --}}
                <div>
                    <label class="block text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-2 font-black">Ruang Lingkup</label>
                    <textarea name="ruang_lingkup" rows="2" class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3.5 text-sm focus:border-blue-500 dark:bg-gray-900" placeholder="Batasan area pemeriksaan...">{{ old('ruang_lingkup', $detail->ruang_lingkup) }}</textarea>
                </div>

                {{-- Tim Pengawas (PKPT only) --}}
                <div x-show="kategori === 'PKPT'" x-transition>
                    <label class="block text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-2 font-black">Tim Pengawas</label>
                    <select name="tim" class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3.5 text-sm focus:border-blue-500 dark:bg-gray-900">
                        <option value="">Pilih Tim</option>
                        @foreach(['Irban I', 'Irban II', 'Irban III', 'Irban IV', 'Irbansus', 'Semua Irban', 'Sekretariat', 'Tim'] as $ir)
                            <option value="{{ $ir }}" {{ old('tim', $detail->tim) == $ir ? 'selected' : '' }}>{{ $ir }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Bulan Pelaksanaan --}}
                @php
                    use App\Helpers\DateHelper;
                    $jadwalValue = old('jadwal', DateHelper::toInputDate($detail->jadwal));
                @endphp
                <div>
                    <label class="block text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-2 font-black">Bulan Pelaksanaan</label>
                    <input type="date" name="jadwal" value="{{ $jadwalValue }}"
                           class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3.5 text-sm focus:border-blue-500 dark:bg-gray-900" required>
                </div>

                {{-- Laporan Akhir --}}
                <div>
                    <label class="block text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-2 font-black">Output / Laporan Akhir</label>
                    <input type="text" name="laporan_akhir" value="{{ old('laporan_akhir', $detail->laporan_akhir) }}" 
                           class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3.5 text-sm focus:border-blue-500 dark:bg-gray-900" 
                           placeholder="Contoh: LHP / LHR">
                </div>

                {{-- Tujuan --}}
                <div>
                    <label class="block text-[10px] uppercase tracking-[0.2em] text-gray-400 mb-2 font-black">Tujuan Audit</label>
                    <textarea name="tujuan" rows="3" class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3.5 text-sm focus:border-blue-500 dark:bg-gray-900" placeholder="Jelaskan tujuan pemeriksaan..." required>{{ old('tujuan', $detail->tujuan) }}</textarea>
                </div>
            </div>

            {{-- Footer Action --}}
            <div class="bg-gray-50/80 px-8 py-6 border-t border-gray-100 flex items-center justify-end gap-4 dark:bg-gray-900/50">
                <a href="{{ route('audit-program.show', $detail->audit_program_id) }}" class="px-6 py-2.5 text-sm text-gray-500 hover:text-gray-800 transition font-semibold italic">Batal</a>
                <button type="submit" class="px-10 py-3 rounded-xl bg-blue-600 text-sm font-bold text-white hover:bg-blue-700 shadow-lg shadow-blue-500/20 transition-all active:scale-95">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const displayInput = document.getElementById('display_anggaran');
        const realInput = document.getElementById('real_anggaran');

        if(displayInput) {
            let initVal = realInput.value.replace(/[^0-9]/g, '');
            if (initVal) {
                displayInput.value = new Intl.NumberFormat('id-ID').format(initVal);
            }

            displayInput.addEventListener('input', function(e) {
                let val = this.value.replace(/[^0-9]/g, '');
                realInput.value = val || 0;
                this.value = val ? new Intl.NumberFormat('id-ID').format(val) : '';
            });
        }
    });
</script>
@endsection
