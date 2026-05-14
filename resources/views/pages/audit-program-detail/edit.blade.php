@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl">
    <form action="{{ route('audit-program-detail.update', $detail->id) }}" method="POST" id="formEdit">
        @csrf
        @method('PUT')

        {{-- Header Section --}}
        <div class="mb-6 px-2">
            <h2 class="text-3xl font-medium tracking-tight text-gray-900 dark:text-white">Edit Audit Program</h2>
            <p class="text-gray-500 dark:text-gray-400">Update parameter dan ruang lingkup kegiatan audit SIMANTAP.</p>
        </div>

        {{-- Form Card --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-800/50">
            <div class="h-1.5 w-full bg-gray-100 dark:bg-gray-700">
                <div class="h-full bg-blue-600 w-full"></div>
            </div>

            <div class="p-6 md:p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Kolom Kiri --}}
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.15em] text-gray-400 mb-2">Audit Program ID</label>
                        <div class="flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 font-mono text-sm text-gray-500 dark:bg-gray-900 dark:border-gray-700">
                            <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            {{ $detail->id }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.15em] text-gray-400 mb-2">Nama Detail Program</label>
                        <input type="text" name="nama_detail_program" value="{{ old('nama_detail_program', $detail->nama_detail_program) }}" 
                               class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3 text-sm text-gray-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.15em] text-gray-400 mb-2">Jenis Kegiatan</label>
                            <select name="jenis_kegiatan" class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                                @foreach(['Audit', 'Review', 'Evaluasi', 'Pemantauan'] as $jenis)
                                    <option value="{{ $jenis }}" {{ $detail->jenis_kegiatan == $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.15em] text-gray-400 mb-2">Status</label>
                            <select name="status" class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                                <option value="rencana" {{ $detail->status == 'rencana' ? 'selected' : '' }}>Rencana</option>
                                <option value="aktif" {{ $detail->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan --}}
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.15em] text-gray-400 mb-2">Anggaran</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                            <input type="text" id="input-anggaran" value="{{ number_format($detail->anggaran, 0, ',', '.') }}" 
                                   class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/50 pl-12 pr-4 py-3 text-sm focus:ring-4 focus:ring-blue-500/10 transition-all dark:bg-gray-900 dark:border-gray-700">
                            <input type="hidden" name="anggaran" id="anggaran-asli" value="{{ $detail->anggaran }}">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.15em] text-gray-400 mb-2">Tingkat Resiko</label>
                        <div class="flex gap-6 p-3 bg-gray-50/50 border-2 border-gray-100 rounded-xl dark:bg-gray-900 dark:border-gray-700">
                            @foreach(['Rendah' => 'text-slate-500', 'Sedang' => 'text-slate-500', 'Tinggi' => 'text-slate-500'] as $level => $color)
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="radio" 
                                        name="tingkat_resiko" 
                                        value="{{ $level }}" 
                                        {{ $detail->tingkat_resiko == $level ? 'checked' : '' }} 
                                        class="w-4 h-4 border-gray-300 text-blue-600 focus:ring-blue-500 transition-all bg-gray-50 dark:bg-gray-800 dark:border-gray-600">
                                    
                                    <span class="text-[11px] font-bold uppercase tracking-wider transition-colors
                                        {{ $detail->tingkat_resiko == $level ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600' }}">
                                        {{ $level }}
                                    </span>
                                </label>
                                @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.15em] text-gray-400 mb-2">Objek Pengawasan</label>
                        <input type="text" name="objek_pengawasan" value="{{ old('objek_pengawasan', $detail->objek_pengawasan) }}" 
                               class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                    </div>
                </div>

                {{-- Full Width --}}
                <div class="md:col-span-2 space-y-6 pt-2">
                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.15em] text-gray-400 mb-2">Tujuan</label>
                        <textarea name="tujuan" rows="3" class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="Jelaskan tujuan audit...">{{ old('tujuan', $detail->tujuan) }}</textarea>
                    </div>

                   <div>
    <label class="block text-[10px] uppercase tracking-[0.15em] text-gray-400 mb-2 font-black">Bulan Pelaksanaan</label>
    <select name="jadwal" class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3.5 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
        <option value="">Pilih Bulan</option>
        @foreach([
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ] as $bulan)
            <option value="{{ $bulan }}" {{ old('jadwal', $detail->jadwal) == $bulan ? 'selected' : '' }}>
                {{ $bulan }}
            </option>
        @endforeach
    </select>
</div>
                </div>
            </div>

            {{-- FOOTER ACTION SECTION (Pindahan Tombol) --}}
            <div class="bg-gray-50/80 px-8 py-6 border-t border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4 dark:bg-gray-900/50 dark:border-gray-800">
                <p class="text-[10px] text-gray-400 tracking-wide uppercase italic">
                    SIMANTAP - Update terakhir: {{ now()->format('d/m/Y H:i') }}
                </p>
                
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <a href="{{ route('audit-program.show', $detail->audit_program_id) }}" 
                       class="flex-1 md:flex-none text-center px-6 py-2.5 rounded-xl border border-gray-200 bg-white text-sm text-gray-600 hover:bg-gray-50 transition dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300">
                       Batal
                    </a>
                    <button type="submit" 
                            class="flex-1 md:flex-none px-8 py-2.5 rounded-xl bg-blue-600 text-sm font-medium text-white hover:bg-blue-700 shadow-lg shadow-blue-500/20 transition-all active:scale-95">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputAnggaran = document.getElementById('input-anggaran');
        const anggaranAsli = document.getElementById('anggaran-asli');

        if(inputAnggaran) {
            inputAnggaran.addEventListener('input', function(e) {
                let value = this.value.replace(/[^0-9]/g, '');
                anggaranAsli.value = value;
                this.value = value ? new Intl.NumberFormat('id-ID').format(value) : '';
            });
        }
    });
</script>
@endsection