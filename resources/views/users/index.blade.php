@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen dark:bg-gray-950">
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Manajemen User</h1>
                <p class="text-sm text-gray-500 mt-0.5">Kelola akun dan hak akses pengguna sistem.</p>
            </div>
            @can('user.create')
            <a href="{{ route('users.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah User
            </a>
            @endcan
        </div>

        {{-- Flash --}}
        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">{{ session('error') }}</div>
        @endif

        {{-- Stats Cards --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-gray-800 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Total User</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-gray-800 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Aktif</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['aktif'] }}</p>
            </div>
            <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-gray-800 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Nonaktif</p>
                <p class="text-2xl font-bold text-red-500 mt-1">{{ $stats['nonaktif'] }}</p>
            </div>
        </div>

        {{-- Filter --}}
        <form method="GET" class="mb-4 flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nama, email, NIP..."
                   class="h-10 flex-1 min-w-48 px-4 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 dark:text-white focus:outline-none focus:border-indigo-400">

            <select name="role"
                    class="h-10 px-3 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 dark:text-white focus:outline-none focus:border-indigo-400">
                <option value="">Semua Role</option>
                @foreach($roles as $role)
                <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>
                    {{ \App\Models\User::ROLES[$role] ?? ucfirst(str_replace('_', ' ', $role)) }}
                </option>
                @endforeach
            </select>

            <select name="status"
                    class="h-10 px-3 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 dark:text-white focus:outline-none focus:border-indigo-400">
                <option value="">Semua Status</option>
                <option value="aktif"    {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>

            <button type="submit" class="h-10 px-4 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                Filter
            </button>
            @if(request()->hasAny(['search', 'role', 'status']))
            <a href="{{ route('users.index') }}" class="h-10 px-4 text-sm font-medium border border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-400 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 flex items-center transition-colors">
                Reset
            </a>
            @endif
        </form>

        {{-- Table --}}
        <div class="bg-white dark:bg-white/5 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase">User</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase">NIP / Jabatan</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase">Role</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase text-center">Status</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors {{ ! $user->is_active ? 'opacity-60' : '' }}">
                            {{-- Avatar + Nama --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-sm font-bold text-indigo-700 dark:text-indigo-300 shrink-0">
                                        {{ $user->initials() }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-gray-700 dark:text-gray-300 font-mono text-xs">{{ $user->nip ?? '-' }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $user->jabatan ?? '-' }}</p>
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $roleName = $user->getRoleNames()->first();
                                    $roleCls = match($roleName) {
                                        'super_admin'        => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                        'kepala_inspektorat' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                        'ketua_tim'          => 'bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-300',
                                        'anggota'            => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                                        'staff_inspektorat'  => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                        default              => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $roleCls }}">
                                    {{ $user->roleName() }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @can('user.edit')
                                <form action="{{ route('users.toggle-active', $user) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            onclick="return confirm('{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} user ini?')"
                                            class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold transition-colors
                                                {{ $user->is_active
                                                    ? 'bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400'
                                                    : 'bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400' }}">
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
                                @else
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                                @endcan
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex justify-end gap-1.5">
                                    <a href="{{ route('users.show', $user) }}"
                                       class="p-1.5 text-gray-400 hover:text-indigo-600 transition-colors" title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    @can('user.edit')
                                    <a href="{{ route('users.edit', $user) }}"
                                       class="p-1.5 text-gray-400 hover:text-blue-600 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 012.828 0L21 3.586a2 2 0 010 2.828l-7 7a2 2 0 01-.883.515l-3 1a1 1 0 01-1.265-1.265l1-3a1 1 0 01.515-.883l7-7z"/>
                                        </svg>
                                    </a>
                                    @endcan
                                    @can('user.delete')
                                    @if($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user) }}" method="POST"
                                          onsubmit="return confirm('Hapus user {{ $user->name }}? Tindakan ini tidak dapat dibatalkan.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 transition-colors" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-gray-400 italic">
                                Tidak ada user ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-800">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection