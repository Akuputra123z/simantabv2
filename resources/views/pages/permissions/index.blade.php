@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen dark:bg-gray-950">
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Role & Permission</h1>
                <p class="text-sm text-gray-500 mt-0.5">Kelola hak akses setiap role secara fleksibel.</p>
            </div>
            <a href="{{ route('permissions.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Role
            </a>
        </div>

        {{-- Flash --}}
        @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
            {{ session('error') }}
        </div>
        @endif

        {{-- Tabel Role --}}
        <div class="bg-white dark:bg-white/[0.03] border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Daftar Role</h2>
                <span class="text-xs text-gray-400">{{ $roles->count() }} role · {{ $totalPermissions }} permission</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Nama Role</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase text-center">Permission</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase text-center">User</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Cakupan Akses</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($roles as $role)
                        @php
                            $isSuperAdmin = $role->name === \App\Models\User::ROLE_SUPER_ADMIN;
                            $isProtected  = in_array($role->name, [
                                \App\Models\User::ROLE_SUPER_ADMIN,
                                \App\Models\User::ROLE_KEPALA_INSPEKTORAT,
                                \App\Models\User::ROLE_KETUA_TIM,
                                \App\Models\User::ROLE_ANGGOTA,
                                \App\Models\User::ROLE_STAFF_INSPEKTORAT,
                            ]);
                            $pct = $totalPermissions > 0
                                ? round($role->permissions_count / $totalPermissions * 100)
                                : 0;
                            $barColor = $pct >= 80 ? 'bg-purple-500'
                                : ($pct >= 50 ? 'bg-indigo-500'
                                : ($pct >= 30 ? 'bg-teal-500' : 'bg-gray-400'));
                            $label = \App\Models\User::ROLES[$role->name]
                                ?? ucfirst(str_replace('_', ' ', $role->name));
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-800 dark:text-gray-100">{{ $label }}</span>
                                    @if($isSuperAdmin)
                                        <span class="px-1.5 py-0.5 rounded text-xs bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 font-medium">Penuh</span>
                                    @endif
                                    @if($isProtected)
                                        <span class="px-1.5 py-0.5 rounded text-xs bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">sistem</span>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-400 font-mono">{{ $role->name }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-mono font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $isSuperAdmin ? 'Semua' : $role->permissions_count }}
                                </span>
                                @if(! $isSuperAdmin)
                                <span class="text-xs text-gray-400"> / {{ $totalPermissions }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-mono font-medium text-gray-700 dark:text-gray-300">{{ $role->users_count }}</span>
                            </td>
                            <td class="px-6 py-4 w-48">
                                @if($isSuperAdmin)
                                    <div class="h-2 rounded-full bg-purple-500"></div>
                                @else
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full {{ $barColor }} transition-all" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-400 w-8 text-right">{{ $pct }}%</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-1.5">
                                    @if(! $isSuperAdmin)
                                    <a href="{{ route('permissions.edit', $role) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-400 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 hover:border-indigo-400 hover:text-indigo-600 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 012.828 0L21 3.586a2 2 0 010 2.828l-7 7a2 2 0 01-.883.515l-3 1a1 1 0 01-1.265-1.265l1-3a1 1 0 01.515-.883l7-7z"/>
                                        </svg>
                                        Atur Permission
                                    </a>
                                    @if(! $isProtected)
                                    <form action="{{ route('permissions.destroy', $role) }}" method="POST"
                                          onsubmit="return confirm('Hapus role \'{{ $role->name }}\'?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="p-1.5 text-gray-400 hover:text-red-600 transition-colors" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                    @else
                                    <span class="text-xs text-gray-400 italic">tidak dapat diubah</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Panel bawah: daftar permission + tambah/hapus --}}
        <div class="bg-white dark:bg-white/[0.03] border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Daftar Permission</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Tambah permission baru atau hapus yang tidak terpakai.</p>
                </div>
                {{-- Form tambah permission baru --}}
                <form action="{{ route('permissions.permission.store') }}" method="POST" class="flex gap-2" id="form-add-perm">
                    @csrf
                    <input type="text" name="name"
                           placeholder="contoh: laporan.cetak"
                           class="h-9 w-52 px-3 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-transparent dark:text-white focus:outline-none focus:border-indigo-400"
                           pattern="[a-z0-9\-\.]+" title="Hanya huruf kecil, angka, titik, strip">
                    <button type="submit"
                            class="h-9 px-4 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Tambah
                    </button>
                </form>
            </div>

            @if(session('success') && str_contains(session('success'), 'Permission'))
            <div class="px-6 py-3 bg-green-50 dark:bg-green-900/20 border-b border-green-100 dark:border-green-800 text-sm text-green-800 dark:text-green-300">
                {{ session('success') }}
            </div>
            @endif
            @if($errors->has('name'))
            <div class="px-6 py-3 bg-red-50 dark:bg-red-900/20 border-b border-red-100 dark:border-red-800 text-sm text-red-800 dark:text-red-300">
                {{ $errors->first('name') }}
            </div>
            @endif

            <div class="p-6">
                @php
                    $allPermissions = \Spatie\Permission\Models\Permission::with('roles')
                        ->orderBy('name')->get();

                    // Kelompokkan berdasarkan prefix (sebelum titik pertama)
                    $grouped = $allPermissions->groupBy(fn($p) =>
                        str_contains($p->name, '.') ? explode('.', $p->name)[0] : 'lainnya'
                    );
                @endphp

                <div class="space-y-4">
                    @foreach($grouped as $prefix => $perms)
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">{{ ucfirst(str_replace('-', ' ', $prefix)) }}</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($perms as $perm)
                            <div class="group flex items-center gap-1.5 px-2.5 py-1 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-white/5 hover:border-red-300 transition-colors">
                                <span class="text-xs font-mono text-gray-600 dark:text-gray-400">{{ $perm->name }}</span>
                                @if($perm->roles->isEmpty())
                                {{-- Hanya bisa dihapus jika tidak dipakai role manapun --}}
                                <form action="{{ route('permissions.permission.destroy', $perm) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm('Hapus permission \'{{ $perm->name }}\'?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="opacity-0 group-hover:opacity-100 text-gray-300 hover:text-red-500 transition-all" title="Hapus">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </form>
                                @else
                                {{-- Dipakai role — tampilkan tooltip jumlah role --}}
                                <span class="text-xs text-indigo-400" title="Digunakan oleh: {{ $perm->roles->pluck('name')->join(', ') }}">
                                    ×{{ $perm->roles->count() }}
                                </span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>
@endsection