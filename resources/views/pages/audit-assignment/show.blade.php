{{-- resources/views/pages/audit-assignment/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Detail Audit Assignment</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $data->nomor_surat }}</p>
        </div>
        <div class="flex items-center gap-3">
            @php $canSign = auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('kepala_inspektorat'); @endphp
            @if($canSign && !$data->isSigned())
            <form action="{{ route('audit-assignment.sign', $data->id) }}" method="POST" class="inline">
                @csrf
                <button class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-green-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-width="2"/></svg>
                    Tanda Tangani
                </button>
            </form>
            @endif
            <a href="{{ route('audit-assignment.edit', $data->id) }}"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white
                hover:bg-blue-700 transition-colors shadow-sm">
                Edit
            </a>
            <a href="{{ route('audit-assignment.preview', $data->id) }}" target="_blank"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium
                text-gray-600 hover:bg-gray-50 transition-colors dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-width="2"/></svg>
                Pratinjau
            </a>
            <a href="{{ route('audit-assignment.print', $data->id) }}" target="_blank"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium
                text-gray-600 hover:bg-gray-50 transition-colors dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Unduh PDF
            </a>
            <a href="{{ route('audit-assignment.index') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium
                text-gray-600 hover:bg-gray-50 transition-colors dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                Kembali
            </a>
        </div>
    </div>

    {{-- STATUS BADGES --}}
    <div class="flex flex-wrap items-center gap-3">
        @php
            $badge = match($data->status) {
                'berjalan' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                'selesai'  => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                default    => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
            };
        @endphp
        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold capitalize {{ $badge }}">
            {{ $data->status }}
        </span>
        @if($data->isSigned())
            <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Ditandatangani
            </span>
            <span class="text-xs text-gray-400">oleh {{ $data->signer->name }}, {{ $data->approved_at->translatedFormat('d F Y H:i') }}</span>
        @endif
    </div>

    {{-- CARD: Informasi Audit --}}
   {{-- CARD: Informasi Audit --}}
<div class="rounded-2xl border border-gray-200 bg-white p-6 sm:p-8 dark:border-gray-800 dark:bg-white/[0.03] space-y-8">
    <div>
        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">Informasi Audit</h3>
        <p class="text-sm text-gray-500">Detail utama penugasan audit</p>
    </div>

    <dl class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Program Audit</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">
                {{ $data->auditProgramDetail?->auditProgram?->nama_program ?? '-' }}
                @if($data->auditProgramDetail?->auditProgram?->tahun)
                    <span class="ml-1 text-xs font-normal text-gray-400">({{ $data->auditProgramDetail->auditProgram->tahun }})</span>
                @endif
            </dd>
        </div>

        <div>
            <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Detail Program (PKPT)</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">
                {{ $data->auditProgramDetail?->nama_detail_program ?? '-' }}
            </dd>
        </div>

        <div>
            <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Jenis Kegiatan</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">
                {{ $data->auditProgramDetail?->jenis_kegiatan ?? '-' }}
            </dd>
        </div>

        <div>
            <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Tim</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">
                {{ $data->auditProgramDetail?->tim ?? '-' }}
            </dd>
        </div>

       

        <div>
            <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Nomor Surat</dt>
            <dd class="mt-1 text-sm font-mono font-medium text-blue-600 dark:text-blue-400">{{ $data->nomor_surat }}</dd>
        </div>

        @if($data->pengendaliTeknis)
        <div>
            <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Pengendali Teknis (Dalnis)</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">{{ $data->pengendaliTeknis->name }}</dd>
        </div>
        @elseif($data->pengendali_teknis)
        <div>
            <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Pengendali Teknis (Dalnis)</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">{{ $data->pengendali_teknis }}</dd>
        </div>
        @endif
       

        {{-- ── UNIT DIPERIKSA (OPTIMIZED) ── --}}
        <div class="sm:col-span-2 lg:col-span-3 mt-4">
            <dt class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                    Unit Diperiksa
                    <span class="ml-2 rounded-full bg-blue-600 px-2 py-0.5 text-[10px] text-white">
                        {{ $data->unitDiperiksas->count() }}
                    </span>
                </span>
                
                {{-- Live Search --}}
                <div class="relative">
                    <input type="text" id="searchUnit" placeholder="Cari unit..." 
                        class="h-8 w-40 sm:w-64 rounded-lg border border-gray-200 bg-gray-50 pl-8 pr-3 text-xs focus:border-blue-500 focus:ring-0 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <svg class="absolute left-2.5 top-2 h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </dt>

            <dd class="mt-4">
                @if($data->unitDiperiksas->count() > 0)
                    <div class="max-h-[380px] overflow-y-auto rounded-xl border border-gray-100 bg-gray-50/30 p-4 dark:border-gray-800 dark:bg-black/10 custom-scrollbar">
                        <div id="unitContainer" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            @foreach($data->unitDiperiksas->sortBy('nama_unit') as $unit)
                                <div class="unit-item flex items-center gap-3 rounded-lg border border-white bg-white p-3 shadow-sm transition-all hover:border-blue-400 dark:border-gray-700 dark:bg-gray-800">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-[11px] font-bold text-gray-800 dark:text-gray-200" title="{{ $unit->nama_unit }}">
                                            {{ $unit->nama_unit }}
                                        </p>
                                        <p class="truncate text-[9px] font-medium uppercase text-gray-400">
                                            {{ $unit->nama_kecamatan ?? 'Kab. Rembang' }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p id="noResults" class="hidden text-sm text-gray-400 italic text-center py-6">Tidak ada unit ditemukan</p>
                    </div>
                @else
                    <p class="text-sm text-gray-400 italic text-center py-6">Tidak ada unit terpilih</p>
                @endif
            </dd>
        </div>
    </dl>
</div>



    {{-- CARD: Jadwal --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-white/[0.03] space-y-6">
        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Jadwal</h3>
        <dl class="grid grid-cols-1 gap-y-6 sm:grid-cols-3">
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Tanggal Mulai</dt>
                <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                    {{ $data->tanggal_mulai?->translatedFormat('d F Y') ?? '-' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Tanggal Selesai</dt>
                <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                    {{ $data->tanggal_selesai?->translatedFormat('d F Y') ?? '-' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Durasi</dt>
                <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                    @if($data->tanggal_mulai && $data->tanggal_selesai)
                        {{ $data->tanggal_mulai->diffInDays($data->tanggal_selesai) + 1 }} hari
                    @else -
                    @endif
                </dd>
            </div>
        </dl>
    </div>

   {{-- CARD: Tim Audit --}}
<div class="rounded-2xl border border-gray-200 bg-white p-6 sm:p-8 dark:border-gray-800 dark:bg-white/[0.03] space-y-6">
    <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
        <div>
            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">Tim Audit</h3>
            <p class="text-sm text-gray-500">Struktur personil penugasan</p>
        </div>
        <div class="text-right">
            <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Total Personil</dt>
            <dd class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ ($data->members->count() + ($data->ketuaTim ? 1 : 0)) }} Orang</dd>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
        {{-- Kiri: Ketua Tim & Info Waktu --}}
        <div class="lg:col-span-4 space-y-6">
            <div>
                <p class="mb-3 text-[10px] font-bold uppercase tracking-widest text-gray-400">Ketua Tim (Penanggung Jawab)</p>
                @if($data->ketuaTim)
                    <div class="group flex items-center gap-4 rounded-xl border border-blue-100 bg-blue-50/50 p-4 dark:border-blue-900/30 dark:bg-blue-900/10">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-600 text-lg font-bold text-white shadow-blue-200 dark:shadow-none">
                            {{ strtoupper(substr($data->ketuaTim->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-gray-800 dark:text-white">{{ $data->ketuaTim->name }}</p>
                            <p class="truncate text-xs text-gray-500">{{ $data->ketuaTim->email }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-sm italic text-gray-400">Belum ditentukan</p>
                @endif
            </div>

            <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-black/10">
                <p class="mb-3 text-[10px] font-bold uppercase tracking-widest text-gray-400">Log Aktivitas</p>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Dibuat</span>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $data->created_at->translatedFormat('d M Y, H:i') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Update terakhir</span>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $data->updated_at->translatedFormat('d M Y, H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kanan: Anggota Tim (Scrollable Grid) --}}
        <div class="lg:col-span-8">
            <p class="mb-3 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                Anggota Tim 
                <span class="ml-2 text-blue-500">({{ $data->members->count() }})</span>
            </p>
            
            @if($data->members->count())
                <div class="max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach($data->members as $member)
                        <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-white p-3 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-xs font-bold text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                {{ strtoupper(substr($member->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-800 dark:text-white">{{ $member->name }}</p>
                                <p class="text-[10px] uppercase tracking-tighter text-gray-400">
                                    {{ $member->pivot->jabatan_tim ?? 'Anggota Pemeriksa' }}
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="flex h-[100px] items-center justify-center rounded-xl border-2 border-dashed border-gray-100 dark:border-gray-800">
                    <p class="text-sm text-gray-400 italic">Tidak ada anggota tim yang terdaftar</p>
                </div>
            @endif
        </div>
    </div>
</div>
    {{-- CARD: Detail Program --}}
    @if($data->auditProgramDetail)
    <div class="rounded-2xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-white/[0.03] space-y-6">
        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Info Detail Program</h3>
        <dl class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 lg:grid-cols-3">
            @if($data->auditProgramDetail->ruang_lingkup)
            <div class="sm:col-span-2 lg:col-span-3">
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Ruang Lingkup</dt>
                <dd class="mt-1 text-sm text-gray-800 dark:text-white">{{ $data->auditProgramDetail->ruang_lingkup }}</dd>
            </div>
            @endif
            @if($data->auditProgramDetail->tujuan)
            <div class="sm:col-span-2 lg:col-span-3">
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Tujuan</dt>
                <dd class="mt-1 text-sm text-gray-800 dark:text-white">{{ $data->auditProgramDetail->tujuan }}</dd>
            </div>
            @endif
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Jenis Kegiatan</dt>
                <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white">{{ $data->auditProgramDetail->jenis_kegiatan ?? '-' }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Tim</dt>
                <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white">{{ $data->auditProgramDetail->tim ?? '-' }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Personil</dt>
                <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white">{{ $data->auditProgramDetail->personil ?? '-' }}</dd>
            </div>
            
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Anggaran</dt>
                <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                    Rp {{ number_format($data->auditProgramDetail->anggaran ?? 0, 0, ',', '.') }}
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Anggaran Disetujui</dt>
                <dd class="mt-1 text-sm font-medium text-blue-600 dark:text-blue-400">
                    Rp {{ number_format($data->anggaran_disetujui ?? 0, 0, ',', '.') }}
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Tingkat Risiko</dt>
                <dd class="mt-1">
                    @php
                        $risikoClass = match(strtolower($data->auditProgramDetail->tingkat_resiko ?? '')) {
                            'tinggi' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
                            'sedang' => 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400',
                            'rendah' => 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
                            default  => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                        };
                    @endphp
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize {{ $risikoClass }}">
                        {{ $data->auditProgramDetail->tingkat_resiko ?? '-' }}
                    </span>
                </dd>
            </div>
        </dl>
    </div>
    @endif

    {{-- CARD: Lampiran --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-white/[0.03]">
        <h3 class="mb-6 text-lg font-semibold text-gray-700 dark:text-gray-200">
            Lampiran
            <span class="ml-2 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500
                dark:bg-gray-800 dark:text-gray-400">{{ $data->attachments->count() }}</span>
        </h3>
        @if($data->attachments->count())
            <ul class="space-y-2">
                @foreach($data->attachments as $att)
                <li class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5
                    dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex items-center gap-3 min-w-0">
                        <svg class="h-5 w-5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/>
                        </svg>
                        <a href="{{ Storage::url($att->file_path) }}" target="_blank"
                            class="truncate text-sm text-blue-600 hover:underline dark:text-blue-400">
                            {{ $att->file_name }}
                        </a>
                    </div>
                    <span class="ml-4 shrink-0 text-xs text-gray-400">
                        {{ number_format($att->file_size / 1024, 1) }} KB
                    </span>
                </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-400">Belum ada lampiran.</p>
        @endif
    </div>

    {{-- DELETE --}}
    <div class="flex items-center justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
        <form action="{{ route('audit-assignment.destroy', $data->id) }}" method="POST"
            onsubmit="return confirm('Yakin ingin menghapus assignment ini? Tindakan ini tidak dapat dibatalkan.')">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-red-50 px-5 py-2.5 text-sm font-medium text-red-600
                hover:bg-red-100 transition-colors dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus Assignment
            </button>
        </form>
    </div>

</div>
@endsection

{{-- Tambahkan Style dan Script ini di akhir file --}}
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchUnit');
        const unitItems = document.querySelectorAll('.unit-item');
        const noResults = document.getElementById('noResults');
        const unitContainer = document.getElementById('unitContainer');

        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase().trim();
                let hasVisibleItems = false;

                unitItems.forEach(item => {
                    // Mengambil teks dari nama unit dan kecamatan untuk pencarian yang lebih akurat
                    const text = item.innerText.toLowerCase();
                    
                    if (text.includes(term)) {
                        item.classList.remove('hidden'); // Menggunakan class hidden Tailwind
                        item.style.display = 'flex';     // Memastikan display kembali ke flex
                        hasVisibleItems = true;
                    } else {
                        item.classList.add('hidden');
                        item.style.display = 'none';
                    }
                });

                // Tampilkan pesan jika tidak ada hasil
                if (hasVisibleItems) {
                    noResults.classList.add('hidden');
                } else {
                    noResults.classList.remove('hidden');
                }
            });
        }
    });
</script>