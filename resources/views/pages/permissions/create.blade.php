@extends('layouts.app')

@section('content')
<div class="space-y-6 pb-20" x-data="rolePermissionCreator()">

    {{-- Navigation & Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('permissions.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white mb-2 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                <span>Kembali ke Daftar Peran</span>
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                Buat Peran Baru
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Tambahkan peran khusus dan tentukan matriks hak akses awal untuk peran tersebut.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button"
                    @click="selectAll()"
                    class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Pilih Semua
            </button>
            <button type="button"
                    @click="clearAll()"
                    class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Kosongkan
            </button>
        </div>
    </div>

    @if($errors->any())
    <div class="p-3.5 rounded-xl border border-red-200 bg-red-50 text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300 text-xs space-y-1">
        @foreach($errors->all() as $e)
            <p class="flex items-center gap-1.5">
                <span>&bull;</span>
                <span>{{ $e }}</span>
            </p>
        @endforeach
    </div>
    @endif

    <form action="{{ route('permissions.store') }}" method="POST" id="createRoleForm">
        @csrf

        {{-- Input Nama Peran --}}
        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900/40 p-4 mb-5 shadow-xs">
            <label for="role_name" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                Kunci Identifikasi Peran (Slug) <span class="text-red-500">*</span>
            </label>
            <div class="max-w-md">
                <input type="text"
                       id="role_name"
                       name="name"
                       value="{{ old('name') }}"
                       required
                       pattern="[a-z0-9_]+"
                       placeholder="contoh: auditor_investigasi"
                       class="h-9.5 w-full px-3 text-xs font-mono rounded-lg border {{ $errors->has('name') ? 'border-red-500' : 'border-gray-300 dark:border-gray-700' }} bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                    Gunakan huruf kecil, angka, dan underscore tanpa spasi.
                </p>
            </div>
        </div>

        {{-- Matriks Hak Akses --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between px-1">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider text-xs">
                    Pilih Hak Akses Awal
                </h2>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Terpilih: <strong class="text-gray-900 dark:text-white font-mono" x-text="selectedPermissions.length"></strong> izin
                </span>
            </div>

            @foreach($permissionGroups as $group => $perms)
            @php
                $groupSlug = Str::slug($group);
                $totalGroupPerms = count($perms);
            @endphp
            <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900/40 overflow-hidden shadow-xs">
                
                {{-- Modul Header --}}
                <div class="flex items-center justify-between px-5 py-3 bg-gray-50/75 dark:bg-gray-800/40 border-b border-gray-200 dark:border-gray-800">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input type="checkbox"
                               class="w-4 h-4 rounded text-blue-600 border-gray-300 dark:border-gray-700 focus:ring-blue-500 cursor-pointer"
                               :checked="isGroupAllChecked('{{ $groupSlug }}', {{ json_encode($perms) }})"
                               @change="toggleGroup('{{ $groupSlug }}', {{ json_encode($perms) }}, $event.target.checked)">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">
                            {{ $group }}
                        </span>
                    </label>

                    <span class="text-xs font-mono text-gray-400">
                        <span x-text="countCheckedInGroup({{ json_encode($perms) }})"></span> / {{ $totalGroupPerms }}
                    </span>
                </div>

                {{-- Modul Items Checkboxes --}}
                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                    @foreach($perms as $permission)
                    @php
                        $action = explode('.', $permission)[1] ?? $permission;
                        $actionLabel = match($action) {
                            'view' => 'Lihat Dokumen',
                            'create' => 'Tambah Data Baru',
                            'edit', 'update' => 'Ubah & Perbarui',
                            'delete' => 'Hapus Data',
                            'submit' => 'Kirim / Ajukan',
                            'review' => 'Telaah Dokumen',
                            'approve' => 'Setujui / Approve',
                            'revise' => 'Kembalikan / Revisi',
                            'cancel' => 'Batalkan',
                            'verifikasi' => 'Verifikasi Kas',
                            'manage' => 'Kelola Konfigurasi',
                            'export' => 'Ekspor Laporan',
                            'download-final' => 'Unduh Naskah Final',
                            'assign-role' => 'Tugaskan Peran',
                            'opd-view' => 'Portal OPD - Lihat',
                            'opd-upload' => 'Portal OPD - Unggah Bukti',
                            'buka-kunci-opd' => 'Buka Kunci Akses OPD',
                            'tolak-opd' => 'Tolak Pengajuan OPD',
                            'import' => 'Impor Data',
                            'run' => 'Jalankan Analisis',
                            'confirm' => 'Konfirmasi Berita Acara',
                            default => ucfirst(str_replace(['_', '-'], ' ', $action)),
                        };
                    @endphp
                    <label class="flex items-start gap-2.5 p-2.5 rounded-lg border border-gray-100 dark:border-gray-800/80 hover:border-gray-300 dark:hover:border-gray-700 hover:bg-gray-50/50 dark:hover:bg-gray-800/20 cursor-pointer transition-colors select-none">
                        <input type="checkbox"
                               name="permissions[]"
                               value="{{ $permission }}"
                               x-model="selectedPermissions"
                               class="w-4 h-4 mt-0.5 rounded text-blue-600 border-gray-300 dark:border-gray-700 focus:ring-blue-500 cursor-pointer">
                        <div class="min-w-0">
                            <span class="text-xs font-medium text-gray-900 dark:text-white block truncate">
                                {{ $actionLabel }}
                            </span>
                            <code class="text-[11px] font-mono text-gray-400 block truncate" title="{{ $permission }}">
                                {{ $permission }}
                            </code>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        {{-- Bottom Action Bar --}}
        <div class="sticky bottom-0 z-30 -mx-4 -mb-6 mt-8 border-t border-gray-200 dark:border-gray-800 bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm px-6 py-3.5 flex items-center justify-between gap-4">
            <span class="text-xs text-gray-500 dark:text-gray-400">
                Pastikan nama peran unik dan hak akses sudah sesuai kebijakan organisasi.
            </span>
            <div class="flex items-center gap-3">
                <a href="{{ route('permissions.index') }}"
                   class="px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700 shadow-sm transition-all active:scale-[0.98]">
                    <span>Simpan Peran</span>
                </button>
            </div>
        </div>
    </form>

</div>

<script>
function rolePermissionCreator() {
    return {
        selectedPermissions: @json(old('permissions', [])),
        allPermissionsList: @json(collect($permissionGroups)->flatten()->values()->all()),

        selectAll() {
            this.selectedPermissions = [...this.allPermissionsList];
        },

        clearAll() {
            this.selectedPermissions = [];
        },

        isGroupAllChecked(slug, perms) {
            return perms.every(p => this.selectedPermissions.includes(p));
        },

        countCheckedInGroup(perms) {
            return perms.filter(p => this.selectedPermissions.includes(p)).length;
        },

        toggleGroup(slug, perms, isChecked) {
            if (isChecked) {
                perms.forEach(p => {
                    if (!this.selectedPermissions.includes(p)) {
                        this.selectedPermissions.push(p);
                    }
                });
            } else {
                this.selectedPermissions = this.selectedPermissions.filter(p => !perms.includes(p));
            }
        }
    };
}
</script>
@endsection