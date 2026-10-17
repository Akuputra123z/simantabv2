@extends('layouts.app')

@section('content')
<div class="space-y-6 pb-20" x-data="rolePermissionEditor()">

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
                Konfigurasi Hak Akses: {{ \App\Models\User::ROLES[$role->name] ?? ucfirst(str_replace(['_', '-'], ' ', $role->name)) }}
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Kunci peran: <code class="font-mono text-xs text-gray-700 dark:text-gray-300 font-semibold">{{ $role->name }}</code> &bull; Terhubung dengan {{ $userCount }} pegawai aktif.
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

    {{-- Filter Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3.5 rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900/40">
        <div class="relative flex-1 max-w-sm">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text"
                   x-model="search"
                   placeholder="Saring hak akses..."
                   class="h-9 w-full pl-9 pr-3 text-xs sm:text-sm rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>

        <div class="text-xs font-medium text-gray-600 dark:text-gray-400">
            Terpilih: <strong class="text-gray-900 dark:text-white font-mono" x-text="selectedPermissions.length"></strong> dari <span class="font-mono">{{ count(collect($permissionGroups)->flatten()) }}</span> izin
        </div>
    </div>

    {{-- Form Matriks Hak Akses --}}
    <form action="{{ route('permissions.update', $role) }}" method="POST" id="permissionsForm">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            @foreach($permissionGroups as $group => $perms)
            @php
                $groupSlug = Str::slug($group);
                $totalGroupPerms = count($perms);
            @endphp
            <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900/40 overflow-hidden shadow-xs"
                 x-show="!search || '{{ strtolower($group) }}'.includes(search.toLowerCase()) || {{ json_encode($perms) }}.some(p => p.toLowerCase().includes(search.toLowerCase()))">
                
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
                    <label x-show="!search || '{{ strtolower($permission) }}'.includes(search.toLowerCase()) || '{{ strtolower($actionLabel) }}'.includes(search.toLowerCase())"
                           class="flex items-start gap-2.5 p-2.5 rounded-lg border border-gray-100 dark:border-gray-800/80 hover:border-gray-300 dark:hover:border-gray-700 hover:bg-gray-50/50 dark:hover:bg-gray-800/20 cursor-pointer transition-colors select-none">
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
                Perubahan izin akan langsung aktif ke seluruh pengguna terkait.
            </span>
            <div class="flex items-center gap-3">
                <a href="{{ route('permissions.index') }}"
                   class="px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700 shadow-sm transition-all active:scale-[0.98]">
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </div>
    </form>

</div>

<script>
function rolePermissionEditor() {
    return {
        search: '',
        selectedPermissions: @json($rolePermissions),
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