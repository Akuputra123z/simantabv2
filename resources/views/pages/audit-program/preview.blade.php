@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 p-4 lg:p-8">

    @if(session('success') || session('error'))
    <div id="global-alert" class="transform transition-all duration-500 ease-in-out rounded-2xl border px-5 py-4 {{ session('success') ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-500/20 dark:bg-emerald-500/10' : 'border-rose-200 bg-rose-50 dark:border-rose-500/20 dark:bg-rose-500/10' }}">
        <div class="flex items-start gap-3">
            <div class="{{ session('success') ? 'text-emerald-500' : 'text-rose-500' }}">
                @if(session('success'))
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                @else
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                @endif
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium {{ session('success') ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">
                    {{ session('success') ? 'Berhasil' : 'Perhatian' }}
                </p>
                <p class="mt-1 text-sm {{ session('success') ? 'text-emerald-600 dark:text-emerald-400/90' : 'text-rose-600 dark:text-rose-400/90' }}">
                    {{ session('success') ?? session('error') }}
                </p>
            </div>
            <button onclick="dismissAlert('global-alert')" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('audit-program.show', $auditProgram->id) }}" class="group flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
            <svg class="h-5 w-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Preview PKPT</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $auditProgram->nama_program }} — TA {{ $auditProgram->tahun }}</p>
        </div>
    </div>

    {{-- PDF Viewer --}}
    <div class="rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
        <iframe src="{{ route('audit-program.preview-pdf', $auditProgram->id) }}"
                class="w-full"
                style="height: 80vh; border: none;">
        </iframe>
    </div>

    {{-- Approval Actions --}}
    @php
        $canApprove = auth()->user()->hasRole('kepala_inspektorat') || auth()->user()->hasRole('super_admin');
    @endphp
    @if($canApprove && !$auditProgram->isApproved())
    <div class="flex flex-wrap items-center justify-end gap-3 p-6 bg-white rounded-3xl border border-gray-200 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        @if($auditProgram->approval_status !== \App\Models\AuditProgram::APPROVAL_MENUNGGU)
        <form action="{{ route('audit-program.approve', $auditProgram->id) }}" method="POST" onsubmit="return confirm('Setujui PKPT ini?')">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 shadow-sm">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Setujui &amp; TTD
            </button>
        </form>
        <form action="{{ route('audit-program.reject', $auditProgram->id) }}" method="POST" onsubmit="return confirm('Tolak PKPT ini?')">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-white px-6 py-3 text-sm font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/20 dark:bg-gray-800">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Tolak
            </button>
        </form>
        @else
        <p class="text-sm text-amber-600 font-semibold">Menunggu persetujuan...</p>
        @endif
    </div>
    @elseif($auditProgram->isApproved())
    <div class="flex flex-wrap items-center justify-between gap-3 p-6 bg-emerald-50/50 rounded-3xl border border-emerald-200 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/5">
        <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-300">
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span class="text-sm font-semibold">PKPT telah disetujui</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ Storage::url($auditProgram->approved_pdf) }}" target="_blank"
               class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 shadow-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Unduh PDF Disetujui
            </a>
            <form action="{{ route('audit-program.batal-setujui', $auditProgram->id) }}" method="POST" onsubmit="return confirm('Batalkan persetujuan PKPT ini?')">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-white px-5 py-2.5 text-sm font-semibold text-amber-600 transition hover:bg-amber-50 dark:border-amber-500/20 dark:bg-gray-800">
                    Batal Setujui
                </button>
            </form>
        </div>
    </div>
    @endif
</div>

<script>
window.dismissAlert = (id) => {
    const el = document.getElementById(id);
    if (el) { el.style.opacity = '0'; el.style.transform = 'translateY(-10px)'; setTimeout(() => el.remove(), 500); }
};
if(document.getElementById('global-alert')) {
    setTimeout(() => dismissAlert('global-alert'), 6000);
}
</script>
@endsection