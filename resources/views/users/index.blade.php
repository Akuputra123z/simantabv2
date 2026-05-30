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
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white border rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase font-semibold">Total User</p>
                <p class="text-2xl font-bold mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white border rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase font-semibold">Aktif</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['aktif'] }}</p>
            </div>
            <div class="bg-white border rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase font-semibold">Nonaktif</p>
                <p class="text-2xl font-bold text-red-500 mt-1">{{ $stats['nonaktif'] }}</p>
            </div>
        </div>

        {{-- Filter --}}
        <form method="GET" class="mb-4 flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nama, email, NIP..."
                   class="h-10 flex-1 min-w-48 px-4 text-sm border rounded-lg">

            <select name="role" class="h-10 px-3 text-sm border rounded-lg">
                <option value="">Semua Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>
                        {{ \App\Models\User::ROLES[$role] ?? ucfirst(str_replace('_', ' ', $role)) }}
                    </option>
                @endforeach
            </select>

            <select name="status" class="h-10 px-3 text-sm border rounded-lg">
                <option value="">Semua Status</option>
                <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>

            <button type="submit" class="h-10 px-4 text-sm bg-indigo-600 text-white rounded-lg">
                Filter
            </button>

            @if(request()->hasAny(['search', 'role', 'status']))
                <a href="{{ route('users.index') }}" class="h-10 px-4 text-sm border rounded-lg flex items-center">
                    Reset
                </a>
            @endif
        </form>

        {{-- Table --}}
        <div class="bg-white border rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase">User</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase">NIP / Jabatan</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase">Role</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase">Sub Unit</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @forelse($users as $user)
                        <tr class="{{ ! $user->is_active ? 'opacity-60' : '' }}">

                            {{-- Avatar --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-sm font-bold text-indigo-700">
                                        {{ $user->initials ?? '-' }}
                                    </div>
                                    <div>
                                        <p class="font-semibold">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- NIP --}}
                            <td class="px-5 py-4">
                                <p class="text-xs font-mono">{{ $user->nip ?? '-' }}</p>
                                <p class="text-xs text-gray-400">{{ $user->jabatan ?? '-' }}</p>
                            </td>

                            {{-- Role --}}
                            <td class="px-5 py-4">
                                <span class="px-2 py-1 text-xs bg-gray-100 rounded">
                                    {{ $user->role_name ?? '-' }}
                                </span>
                            </td>

                            {{-- Sub Unit --}}
                            <td class="px-5 py-4">
                                <span class="text-xs font-medium text-gray-700">{{ $user->unit_kerja ?: '-' }}</span>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    @can('user.view')
                                    <a href="{{ route('users.show', $user) }}"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-400 transition hover:border-blue-200 hover:text-blue-600 dark:border-gray-700"
                                       title="Detail">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>
                                    @endcan

                                    @can('user.edit')
                                    <a href="{{ route('users.edit', $user) }}"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-400 transition hover:border-gray-300 hover:text-gray-700 dark:border-gray-700"
                                       title="Edit">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </a>
                                    @endcan

                                    @can('user.delete')
                                    <form action="{{ route('users.destroy', $user) }}" method="POST"
                                          onsubmit="return confirm('Hapus user {{ $user->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-400 transition hover:border-red-200 hover:text-red-600 dark:border-gray-700"
                                                title="Hapus">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 text-gray-400">
                                Tidak ada user
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 border-t">
                {{ $users->links() }}
            </div>
        </div>

    </div>
</div>
@endsection