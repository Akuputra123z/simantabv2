{{-- resources/views/pages/tindak-lanjuts/cicilans/create.blade.php --}}
@extends('layouts.app')

@push('scripts')
    @include('components._rupiah-input')
@endpush

@section('content')

@php
    $rekom  = $tindakLanjut->recommendation;
    $isUang = $rekom?->isUang();
    $sisaTl = (int) ($tindakLanjut->sisa_belum_bayar ?? 0);
    $nomorLhp = $rekom?->temuan?->lhp?->nomor_lhp ?? '-';
    $jenisRekom = $rekom?->jenis_rekomendasi ?? '-';
@endphp

<div class="min-h-screen bg-slate-50 dark:bg-slate-950 py-8 px-4">
<div class="max-w-2xl mx-auto">

    {{-- ── Breadcrumb ── --}}
    <nav class="mb-6 flex items-center gap-1.5 text-xs font-semibold text-slate-400">
        <a href="{{ route('tindak-lanjuts.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Tindak Lanjut</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('tindak-lanjuts.cicilans.index', $tindakLanjut) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Cicilan</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600 dark:text-slate-300">Tambah Cicilan #{{ $nextKe }}</span>
    </nav>

    {{-- ── Context Banner ── --}}
    <div class="mb-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-x-6 gap-y-2.5">

            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Nomor LHP</p>
                <p class="text-sm font-bold text-slate-900 dark:text-white font-mono">{{ $nomorLhp }}</p>
            </div>

            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Jenis</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-black uppercase tracking-wide
                    {{ $isUang ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-violet-50 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400' }}">
                    {{ $jenisRekom }}
                </span>
            </div>

            @if($isUang && $sisaTl > 0)
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Sisa Belum Lunas</p>
                <p class="text-sm font-black text-rose-600 dark:text-rose-400">Rp {{ number_format($sisaTl, 0, ',', '.') }}</p>
            </div>
            @endif

            <div class="ml-auto">
                <div class="flex items-center gap-2 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800 rounded-xl px-4 py-2">
                    <div class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></div>
                    <span class="text-[10px] font-black text-indigo-500 uppercase tracking-widest">Cicilan</span>
                    <span class="text-base font-black text-indigo-700 dark:text-indigo-400">#{{ $nextKe }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Error Alert ── --}}
    @if($errors->any())
    <div class="mb-5 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-2xl p-4">
        <div class="flex items-start gap-3">
            <svg class="w-4 h-4 text-rose-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <ul class="space-y-1">
                @foreach($errors->all() as $e)
                    <li class="text-xs font-semibold text-rose-700 dark:text-rose-300">{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- ── Main Form Card ── --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">

        <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
            <div class="w-8 h-8 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Input Pembayaran Cicilan</h3>
                <p class="text-xs text-slate-400 mt-0.5">Lengkapi detail bukti pembayaran di bawah ini</p>
            </div>
        </div>

        <div class="p-6">
            <form action="{{ route('tindak-lanjuts.cicilans.store', $tindakLanjut) }}" method="POST" id="form-cicilan" novalidate>
                @csrf
                <div class="space-y-5">

                    {{-- Row 1: Tanggal + Nomor Bukti --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        {{-- Tanggal Bayar --}}
                        <div class="space-y-1.5">
                            <label class="field-label">
                                Tanggal Bayar <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="tanggal_bayar"
                                   value="{{ old('tanggal_bayar', date('Y-m-d')) }}"
                                   onclick="this.showPicker()" required
                                   class="field-input {{ $errors->has('tanggal_bayar') ? 'field-input--error' : '' }}">
                            @error('tanggal_bayar')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nomor Bukti --}}
                        <div class="space-y-1.5">
                            <label class="field-label">Nomor Bukti</label>
                            <input type="text" name="nomor_bukti"
                                   value="{{ old('nomor_bukti') }}"
                                   placeholder="BKT/2026/001"
                                   autocomplete="off"
                                   class="field-input uppercase placeholder:normal-case placeholder:text-slate-300">
                        </div>
                    </div>

                    {{-- Nilai Bayar (hanya uang) --}}
                    @if($isUang)
                   <div class="space-y-1.5">
    <label class="field-label">
        Nilai Bayar (Rp) <span class="text-rose-500">*</span>
        @if($sisaTl > 0)
            <span class="ml-1.5 text-[10px] font-semibold text-slate-400 normal-case">
                — Maks: <strong class="text-slate-600 dark:text-slate-300">Rp {{ number_format($sisaTl, 0, ',', '.') }}</strong>
            </span>
        @endif
    </label>

    <div class="rupiah-wrap relative">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 z-10">
            <span class="text-slate-400 text-sm font-bold select-none">Rp</span>
        </div>

        <input type="text"
               id="display-nilai-bayar"
               inputmode="numeric"
               autocomplete="off"
               placeholder="0"
               class="rupiah-field field-input !pl-14 font-bold text-slate-900 dark:text-white text-base tracking-wide {{ $errors->has('nilai_bayar') ? 'field-input--error' : '' }}"
               style="padding-left: 3.5rem !important;" {{-- Jaminan tidak akan tertimpa CSS lain --}}
               data-name="nilai_bayar"
               data-value="{{ old('nilai_bayar', 0) }}">
    </div>

    {{-- Error & Progress (Sama seperti sebelumnya) --}}
    <div id="error-nilai-bayar" class="hidden">
        <p class="field-error" id="error-nilai-bayar-msg"></p>
    </div>

    @if($sisaTl > 0)
    <div id="sisa-progress" class="mt-2 space-y-1.5 hidden">
        <div class="flex justify-between text-xs text-slate-500">
            <span>Nilai diinput</span>
            <span id="sisa-label" class="font-semibold text-slate-700 dark:text-slate-300"></span>
        </div>
        <div class="h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
            <div id="sisa-bar" class="h-full rounded-full transition-all duration-300 bg-indigo-500" style="width:0%"></div>
        </div>
    </div>
    @endif
</div>

                    {{-- Breakdown (collapsible) --}}
                    <div>
                        <button type="button" id="toggle-breakdown"
                                class="flex items-center gap-2 text-[11px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest hover:opacity-80 transition-opacity group">
                            <svg id="chevron-breakdown" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
                            </svg>
                            Rincian Distribusi
                            <span class="text-slate-400 font-normal normal-case tracking-normal">(opsional)</span>
                        </button>

                        <div id="section-breakdown"
                             class="hidden mt-3 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700">
                            <div class="grid grid-cols-2 gap-3">
                                @foreach(['negara' => 'Negara', 'daerah' => 'Daerah', 'desa' => 'Desa', 'bos_blud' => 'BOS / BLUD'] as $key => $lbl)
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider">{{ $lbl }}</label>
                                    <div class="rupiah-wrap relative">
                                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 text-xs select-none">Rp</span>
                                        <input type="text"
                                               id="display-bd-{{ $key }}"
                                               inputmode="numeric"
                                               autocomplete="off"
                                               placeholder="0"
                                               class="rupiah-field field-input field-input--sm pl-9 text-xs font-semibold"
                                               data-name="nilai_bayar_{{ $key }}"
                                               data-value="{{ old('nilai_bayar_'.$key, 0) }}"
                                               data-breakdown="{{ $key }}">
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <p id="info-breakdown-sisa" class="mt-3 text-xs text-slate-400"></p>
                        </div>
                    </div>

                    @else
                    {{-- Non-uang: hidden value = 1 --}}
                    <input type="hidden" name="nilai_bayar" value="1">
                    @endif

                    {{-- Row 2: Jenis Bayar + Status --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        {{-- Jenis Bayar --}}
                        <div class="space-y-1.5">
                            <label class="field-label">Metode / Jenis Bayar</label>
                            <input type="text" name="jenis_bayar"
                                   value="{{ old('jenis_bayar') }}"
                                   placeholder="Contoh: Transfer Bank, Tunai"
                                   class="field-input">
                        </div>

                        {{-- Status --}}
                        <div class="space-y-1.5">
                            <label class="field-label">Status <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <select name="status" required class="field-input appearance-none pr-10">
                                    <option value="menunggu_verifikasi" {{ old('status', 'menunggu_verifikasi') === 'menunggu_verifikasi' ? 'selected' : '' }}>
                                        Menunggu Verifikasi
                                    </option>
                                    <option value="diterima" {{ old('status') === 'diterima' ? 'selected' : '' }}>
                                        Diterima (Lunas)
                                    </option>
                                    <option value="ditolak" {{ old('status') === 'ditolak' ? 'selected' : '' }}>
                                        Ditolak
                                    </option>
                                </select>
                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Keterangan --}}
                    <div class="space-y-1.5">
                        <label class="field-label">Keterangan</label>
                        <textarea name="keterangan" rows="3"
                                  placeholder="Catatan tambahan pembayaran (opsional)..."
                                  class="field-input h-auto py-3 resize-none leading-relaxed">{{ old('keterangan') }}</textarea>
                    </div>

                    {{-- ── Actions ── --}}
                    <div class="flex items-center gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="submit" id="btn-submit"
                                class="flex-1 flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] text-white text-sm font-bold py-3 px-6 rounded-xl transition-all shadow-md shadow-indigo-100 dark:shadow-none disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span id="btn-label">Simpan Cicilan #{{ $nextKe }}</span>
                        </button>

                        <a href="{{ route('tindak-lanjuts.cicilans.index', $tindakLanjut) }}"
                           class="flex-1 flex items-center justify-center py-3 px-6 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                            Batal
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

</div>
</div>

<style>
/* ── Field primitives ── */
.field-label {
    display: block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #94a3b8;
}
.dark .field-label { color: #64748b; }

.field-input {
    display: block;
    width: 100%;
    height: 2.75rem;
    padding: 0 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #1e293b;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 0.75rem;
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
    -webkit-appearance: none;
}
.dark .field-input {
    color: #f1f5f9;
    background: rgba(255,255,255,0.04);
    border-color: rgba(255,255,255,0.08);
}
.field-input:focus {
    border-color: #6366f1;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
}
.dark .field-input:focus {
    background: rgba(255,255,255,0.06);
    box-shadow: 0 0 0 3px rgba(99,102,241,0.2);
}
.field-input--error {
    border-color: #f43f5e !important;
    background: #fff1f2 !important;
    box-shadow: 0 0 0 3px rgba(244,63,94,0.1) !important;
}
.dark .field-input--error { background: rgba(244,63,94,0.07) !important; }
.field-input--sm { height: 2.25rem; padding: 0 0.75rem; font-size: 0.8125rem; }
.field-error { margin-top: 4px; font-size: 11px; font-weight: 600; color: #f43f5e; }

/* ── Select caret ── */
select.field-input { padding-right: 2.5rem; cursor: pointer; }
textarea.field-input { height: auto; }

/* ── Animate breakdown ── */
#section-breakdown { animation: slideDown .2s ease-out; }
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    @if($isUang)
    /* ── Refs ── */
    const displayBayar  = document.getElementById('display-nilai-bayar');
    const hiddenBayar   = document.querySelector('input[name="nilai_bayar"]');
    const errorBox      = document.getElementById('error-nilai-bayar');
    const errorMsg      = document.getElementById('error-nilai-bayar-msg');
    const sisaProgress  = document.getElementById('sisa-progress');
    const sisaBar       = document.getElementById('sisa-bar');
    const sisaLabel     = document.getElementById('sisa-label');
    const maxSisa       = {{ $sisaTl }};

    function fmt(val) {
        const n = String(val).replace(/\D/g,'');
        return n ? n.replace(/\B(?=(\d{3})+(?!\d))/g,'.') : '';
    }
    function parse(val) { return parseInt(String(val).replace(/\./g,'') || '0', 10); }
    function money(n) { return 'Rp ' + fmt(String(n)); }

    /* ── Validasi & progress bar ── */
    function onBayarChange() {
        const num = parse(hiddenBayar?.value ?? 0);

        /* Error jika melebihi sisa */
        if (maxSisa > 0 && num > maxSisa) {
            errorMsg.textContent = `Nilai melebihi sisa tindak lanjut (${money(maxSisa)}).`;
            errorBox.classList.remove('hidden');
            displayBayar.classList.add('field-input--error');
        } else {
            errorBox.classList.add('hidden');
            displayBayar.classList.remove('field-input--error');
        }

        /* Progress bar */
        if (sisaProgress && maxSisa > 0) {
            if (num > 0) {
                sisaProgress.classList.remove('hidden');
                const pct = Math.min(100, Math.round(num / maxSisa * 100));
                sisaBar.style.width = pct + '%';
                sisaBar.className   = 'h-full rounded-full transition-all duration-300 ' +
                    (num > maxSisa ? 'bg-rose-500' : num === maxSisa ? 'bg-emerald-500' : 'bg-indigo-500');
                sisaLabel.textContent = money(num) + ' / ' + money(maxSisa);
            } else {
                sisaProgress.classList.add('hidden');
            }
        }

        /* Auto-fill negara jika masih 0 */
        syncBreakdownNegara(num);
    }

    /* Dengarkan event 'change' dari RupiahInput */
    displayBayar?.addEventListener('change', onBayarChange);

    /* ── Auto-fill negara jika kosong ── */
    function syncBreakdownNegara(num) {
        const hNegara = document.querySelector('input[name="nilai_bayar_negara"]');
        const dNegara = document.getElementById('display-bd-negara');
        if (hNegara && dNegara && parse(hNegara.value) === 0) {
            hNegara.value = num;
            dNegara.value = fmt(String(num));
            updateBreakdownInfo();
        }
    }

    /* ── Info sisa breakdown ── */
    const bdKeys = ['negara','daerah','desa','bos_blud'];
    function updateBreakdownInfo() {
        const total  = parse(hiddenBayar?.value ?? 0);
        const sumBd  = bdKeys.reduce((s,k) => s + parse(document.querySelector(`input[name="nilai_bayar_${k}"]`)?.value ?? 0), 0);
        const sisa   = total - sumBd;
        const infoEl = document.getElementById('info-breakdown-sisa');
        if (!infoEl) return;

        if (sumBd === 0) {
            infoEl.innerHTML = '';
        } else if (Math.abs(sisa) < 1) {
            infoEl.innerHTML = '<span class="text-emerald-600 font-semibold">✓ Total distribusi sesuai nilai bayar.</span>';
        } else if (sisa > 0) {
            infoEl.innerHTML = `Sisa belum didistribusikan: <strong>${money(sisa)}</strong>`;
        } else {
            infoEl.innerHTML = `<span class="text-rose-600 font-semibold">⚠ Distribusi melebihi nilai bayar sebesar ${money(Math.abs(sisa))}.</span>`;
        }
    }

    /* Breakdown change listeners (setelah RupiahInput init) */
    setTimeout(() => {
        bdKeys.forEach(k => {
            document.getElementById(`display-bd-${k}`)?.addEventListener('change', updateBreakdownInfo);
        });
        /* Trigger awal jika ada old value */
        onBayarChange();
    }, 100);

    /* ── Toggle breakdown section ── */
    const toggleBtn = document.getElementById('toggle-breakdown');
    const section   = document.getElementById('section-breakdown');
    const chevron   = document.getElementById('chevron-breakdown');
    let bOpen = false;
    toggleBtn?.addEventListener('click', () => {
        bOpen = !bOpen;
        section.classList.toggle('hidden', !bOpen);
        chevron.style.transform = bOpen ? 'rotate(180deg)' : 'rotate(0deg)';
    });

    /* ── Guard submit ── */
    document.getElementById('form-cicilan')?.addEventListener('submit', function (e) {
        const num = parse(hiddenBayar?.value ?? 0);

        if (maxSisa > 0 && num > maxSisa) {
            e.preventDefault();
            displayBayar?.focus();
            return;
        }

        if (this.checkValidity()) {
            setTimeout(() => {
                const btn   = document.getElementById('btn-submit');
                const label = document.getElementById('btn-label');
                if (btn)   { btn.disabled = true; btn.classList.add('opacity-70'); }
                if (label) { label.textContent = 'Menyimpan...'; }
            }, 10);
        }
    });

    @else
    /* ── Non-uang: hanya guard submit ── */
    document.getElementById('form-cicilan')?.addEventListener('submit', function () {
        if (this.checkValidity()) {
            setTimeout(() => {
                const btn   = document.getElementById('btn-submit');
                const label = document.getElementById('btn-label');
                if (btn)   { btn.disabled = true; }
                if (label) { label.textContent = 'Menyimpan...'; }
            }, 10);
        }
    });
    @endif
});
</script>
@endsection