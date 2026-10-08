@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="dasarPeraturanApp()">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Master Dasar Peraturan</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Kelola daftar dasar hukum dan peraturan yang digunakan pada Surat Perintah Tugas (SPT) pengawasan
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                {{ $totalAktif }} Aktif
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                {{ $dasarPeraturans->total() }} Total Peraturan
            </span>
        </div>
    </div>

    {{-- Feedback Alerts --}}
    @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-800/40 dark:bg-green-900/20 dark:text-green-300">
            <svg class="h-5 w-5 shrink-0 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800/40 dark:bg-red-900/20 dark:text-red-300">
            <svg class="h-5 w-5 shrink-0 text-red-600 dark:text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Form Tambah Baru --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 self-start">
            <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-gray-100 dark:border-gray-800">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">
                        Tambah Dasar Peraturan
                    </h2>
                    <p class="text-xs text-gray-400">Tambahkan dasar hukum untuk SPT</p>
                </div>
            </div>

            <form method="POST" action="{{ route('dasar-peraturan.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Isi / Nama Dasar Peraturan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="nama" rows="4" required
                              placeholder="Mis: Peraturan Daerah Kabupaten Rembang Nomor 6 Tahun 2021 tentang Perubahan Kedua atas Peraturan Daerah Nomor 5 Tahun 2016..."
                              class="w-full rounded-xl border border-gray-200 bg-white p-3 text-xs text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white">{{ old('nama') }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Nomor Peraturan (Opsional)
                        </label>
                        <input type="text" name="nomor" value="{{ old('nomor') }}"
                               placeholder="Mis. Perda No. 6/2021"
                               class="h-9 w-full rounded-lg border border-gray-200 bg-white px-3 text-xs text-gray-800 focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Tahun (Opsional)
                        </label>
                        <input type="text" name="tahun" value="{{ old('tahun') }}"
                               placeholder="Mis. 2021"
                               class="h-9 w-full rounded-lg border border-gray-200 bg-white px-3 text-xs text-gray-800 focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 items-center">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Urutan Tampil
                        </label>
                        <input type="number" name="urutan" min="1" value="{{ old('urutan', $dasarPeraturans->total() + 1) }}"
                               class="h-9 w-full rounded-lg border border-gray-200 bg-white px-3 text-xs text-gray-800 focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div class="pt-5">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="is_active" value="1" checked
                                   class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Status Aktif</span>
                        </label>
                    </div>
                </div>

                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 py-2.5 text-xs font-bold text-white shadow-md shadow-blue-500/20 hover:bg-blue-700 active:scale-95 transition-all">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Dasar Peraturan
                </button>
            </form>
        </div>

        {{-- Tabel Daftar Data --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Filter & Search Bar --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <form method="GET" action="{{ route('dasar-peraturan.index') }}" class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                            </svg>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Cari teks peraturan, nomor, atau tahun..."
                               class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 pl-9 pr-3 text-xs text-gray-800 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <select name="status" onchange="this.form.submit()"
                            class="h-9 rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">Semua Status</option>
                        <option value="aktif" @selected(request('status') === 'aktif')>Aktif Saja</option>
                        <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif Saja</option>
                    </select>

                    <button type="submit"
                            class="h-9 rounded-lg bg-gray-900 px-4 text-xs font-semibold text-white hover:bg-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600 transition">
                        Filter
                    </button>

                    @if(request('search') || request('status'))
                        <a href="{{ route('dasar-peraturan.index') }}"
                           class="h-9 inline-flex items-center rounded-lg border border-gray-200 px-3 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            {{-- Table Card --}}
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                        <thead class="border-b border-gray-100 bg-gray-50/75 uppercase font-semibold text-gray-700 dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-3 text-center w-12">No</th>
                                <th class="px-4 py-3">Dasar Peraturan / Hukum</th>
                                <th class="px-4 py-3 w-28 text-center">Tahun / No</th>
                                <th class="px-4 py-3 w-24 text-center">Status</th>
                                <th class="px-4 py-3 w-28 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($dasarPeraturans as $index => $item)
                                <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors">
                                    <td class="px-4 py-3 text-center font-bold text-gray-400">
                                        {{ $item->urutan ?: ($dasarPeraturans->firstItem() + $index) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900 dark:text-white leading-relaxed">
                                            {{ $item->nama }}
                                        </div>
                                        @if($item->nomor)
                                            <div class="mt-1 flex items-center gap-2">
                                                <span class="inline-flex items-center rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-mono text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                                    {{ $item->nomor }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono">
                                        {{ $item->tahun ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <form method="POST" action="{{ route('dasar-peraturan.toggle', $item->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    title="Klik untuk mengubah status"
                                                    class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold transition-all hover:scale-105 {{ $item->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300' }}">
                                                {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            {{-- Edit Button --}}
                                            <button type="button"
                                                    @click="openEditModal(@js($item))"
                                                    class="flex h-7 w-7 items-center justify-center rounded-lg text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20"
                                                    title="Edit Dasar Peraturan">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                </svg>
                                            </button>

                                            {{-- Delete Button --}}
                                            <form method="POST" action="{{ route('dasar-peraturan.destroy', $item->id) }}"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus dasar peraturan ini?');"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="flex h-7 w-7 items-center justify-center rounded-lg text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-900/20"
                                                        title="Hapus Dasar Peraturan">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="h-8 w-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <p class="text-xs font-medium">Belum ada dasar peraturan yang sesuai filter.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($dasarPeraturans->hasPages())
                    <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                        {{ $dasarPeraturans->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Edit Dasar Peraturan --}}
    <div x-show="isEditModalOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm transition-opacity"
         @keydown.escape.window="closeEditModal()">
        
        <div class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-800 dark:bg-gray-900"
             @click.outside="closeEditModal()">
            
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Edit Dasar Peraturan</h3>
                </div>
                <button type="button" @click="closeEditModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form :action="editFormAction" method="POST" class="mt-4 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Isi / Nama Dasar Peraturan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="nama" x-model="editItem.nama" rows="4" required
                              class="w-full rounded-xl border border-gray-200 bg-white p-3 text-xs text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Nomor Peraturan (Opsional)
                        </label>
                        <input type="text" name="nomor" x-model="editItem.nomor"
                               class="h-9 w-full rounded-lg border border-gray-200 bg-white px-3 text-xs text-gray-800 focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Tahun (Opsional)
                        </label>
                        <input type="text" name="tahun" x-model="editItem.tahun"
                               class="h-9 w-full rounded-lg border border-gray-200 bg-white px-3 text-xs text-gray-800 focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 items-center">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Urutan Tampil
                        </label>
                        <input type="number" name="urutan" min="1" x-model="editItem.urutan"
                               class="h-9 w-full rounded-lg border border-gray-200 bg-white px-3 text-xs text-gray-800 focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div class="pt-5">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="is_active" value="1" x-model="editItem.is_active"
                                   class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Status Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="closeEditModal()"
                            class="rounded-xl border border-gray-200 px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-5 py-2 text-xs font-bold text-white hover:bg-blue-700 transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function dasarPeraturanApp() {
    return {
        isEditModalOpen: false,
        editItem: {
            id: null,
            nama: '',
            nomor: '',
            tahun: '',
            urutan: 1,
            is_active: true
        },
        editFormAction: '',
        openEditModal(item) {
            this.editItem = {
                id: item.id,
                nama: item.nama,
                nomor: item.nomor || '',
                tahun: item.tahun || '',
                urutan: item.urutan || 1,
                is_active: Boolean(item.is_active)
            };
            this.editFormAction = `{{ url('dasar-peraturan') }}/${item.id}`;
            this.isEditModalOpen = true;
        },
        closeEditModal() {
            this.isEditModalOpen = false;
        }
    }
}
</script>
@endsection
