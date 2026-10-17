@extends('layouts.app')

@section('content')
<div x-data="{ activeTab: 'roles', searchPermission: '' }" class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                Hak Akses & Peran Pengguna
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Pengaturan kewenangan berbasis peran (Role-Based Access Control) untuk seluruh modul SIMANTABv2.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('permissions.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 shadow-sm shadow-blue-500/10 active:scale-[0.98] transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Peran Baru</span>
            </a>
        </div>
    </div>

    {{-- Tab Segmented Navigation --}}
    <div class="border-b border-gray-200 dark:border-gray-800">
        <nav class="flex gap-6 -mb-px" aria-label="Tabs">
            <button type="button"
                    @click="activeTab = 'roles'"
                    :class="activeTab === 'roles'
                        ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400 font-semibold'
                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:border-gray-300 font-medium'"
                    class="py-3 px-1 border-b-2 text-sm flex items-center gap-2 transition-colors">
                <span>Daftar Peran</span>
                <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-mono">
                    {{ $roles->count() }}
                </span>
            </button>

            <button type="button"
                    @click="activeTab = 'permissions'"
                    :class="activeTab === 'permissions'
                        ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400 font-semibold'
                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:border-gray-300 font-medium'"
                    class="py-3 px-1 border-b-2 text-sm flex items-center gap-2 transition-colors">
                <span>Katalog Hak Akses</span>
                <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-mono">
                    {{ $totalPermissions }}
                </span>
            </button>
        </nav>
    </div>

    {{-- TAB 1: DAFTAR PERAN (ROLES) --}}
    <div x-show="activeTab === 'roles'" class="space-y-4">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900/40">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50/75 border-b border-gray-200 dark:bg-gray-800/40 dark:border-gray-800 text-gray-600 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider">Peran Pengguna</th>
                            <th scope="col" class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider">Tipe Peran</th>
                            <th scope="col" class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-center">Hak Akses</th>
                            <th scope="col" class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-center">Pegawai Terdaftar</th>
                            <th scope="col" class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        @foreach($roles as $role)
                        @php
                            $isSuperAdmin = $role->name === \App\Models\User::ROLE_SUPER_ADMIN;
                            $isProtected = in_array($role->name, $protectedRoles);
                            $label = \App\Models\User::ROLES[$role->name] ?? ucfirst(str_replace(['_', '-'], ' ', $role->name));
                            $description = $roleDescriptions[$role->name] ?? 'Peran kustom operasional sistem SIMANTABv2.';
                        @endphp
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900 dark:text-white">
                                    {{ $label }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-1">
                                    {{ $description }}
                                </div>
                                <code class="text-[11px] text-gray-400 font-mono mt-1 inline-block">{{ $role->name }}</code>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($isSuperAdmin)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300 border border-purple-200 dark:border-purple-800/50">
                                        Hak Penuh
                                    </span>
                                @elseif($isProtected)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                        Standar Sistem
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200 dark:border-blue-800/50">
                                        Peran Kustom
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if($isSuperAdmin)
                                    <span class="text-xs font-semibold text-purple-700 dark:text-purple-300">
                                        Semua Izin (Bypass)
                                    </span>
                                @else
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        {{ $role->permissions_count }}
                                    </span>
                                    <span class="text-xs text-gray-400">/ {{ $totalPermissions }} izin</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-50 dark:bg-gray-800/60 text-gray-700 dark:text-gray-300 border border-gray-200/80 dark:border-gray-700">
                                    {{ $role->users_count }} Pegawai
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-3">
                                    @if(! $isSuperAdmin)
                                        <a href="{{ route('permissions.edit', $role) }}"
                                           class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                            <span>Atur Izin</span>
                                        </a>

                                        @if(! $isProtected)
                                            <form action="{{ route('permissions.destroy', $role) }}" method="POST"
                                                  onsubmit="return confirm('Hapus peran \'{{ $role->name }}\' secara permanen?')" class="inline-block">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="text-xs font-semibold text-red-600 hover:text-red-700 dark:text-red-400 transition-colors">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-400 italic">Terkunci</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- TAB 2: KATALOG HAK AKSES (PERMISSIONS) --}}
    <div x-show="activeTab === 'permissions'" x-cloak class="space-y-5">
        {{-- Toolbar Katalog --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900/40">
            <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text"
                       x-model="searchPermission"
                       placeholder="Cari kunci izin akses (contoh: lhp, temuan, edit)..."
                       class="h-9.5 w-full pl-9 pr-3 text-sm rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            {{-- Form Tambah Permission Baru --}}
            <form action="{{ route('permissions.permission.store') }}" method="POST" class="flex items-center gap-2">
                @csrf
                <input type="text"
                       name="name"
                       placeholder="nama.izin.baru"
                       class="h-9.5 px-3 text-xs font-mono rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 w-44"
                       required
                       pattern="[a-z0-9\-\.]+">
                <button type="submit"
                        class="h-9.5 px-3 text-xs font-semibold rounded-lg bg-gray-900 hover:bg-gray-800 text-white dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white transition-colors shrink-0">
                    + Tambah Izin
                </button>
            </form>
        </div>

        {{-- Grid Modul Izin --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($groupedPermissions as $module => $perms)
            <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900/40 p-4 space-y-3"
                 x-show="!searchPermission || '{{ strtolower($module) }}'.includes(searchPermission.toLowerCase()) || {{ json_encode($perms->pluck('name')->map(fn($n) => strtolower($n))) }}.some(p => p.includes(searchPermission.toLowerCase()))">
                
                <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-xs font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">
                        {{ $module }}
                    </h3>
                    <span class="text-xs text-gray-400 font-mono">
                        {{ count($perms) }} izin
                    </span>
                </div>

                <div class="space-y-1.5">
                    @foreach($perms as $perm)
                    <div x-show="!searchPermission || '{{ strtolower($perm->name) }}'.includes(searchPermission.toLowerCase())"
                         class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                        <code class="text-xs font-mono text-gray-800 dark:text-gray-200">{{ $perm->name }}</code>

                        <div class="flex items-center gap-2">
                            @if($perm->roles->isEmpty())
                                <span class="text-[11px] text-gray-400">Belum dipakai</span>
                                <form action="{{ route('permissions.permission.destroy', $perm) }}" method="POST"
                                      onsubmit="return confirm('Hapus izin \'{{ $perm->name }}\'?')" class="inline-block">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors" title="Hapus izin tidak terpakai">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </form>
                            @else
                                <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400"
                                      title="Dimiliki oleh: {{ $perm->roles->pluck('name')->join(', ') }}">
                                    {{ $perm->roles->count() }} peran
                                </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection