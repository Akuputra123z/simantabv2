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
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase text-center">Status</th>
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

                            {{-- Status --}}
                            <td class="px-5 py-4 text-center">
                                <span class="px-2 py-1 text-xs rounded
                                    {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('users.show', $user) }}" class="text-gray-500 hover:text-indigo-600">Detail</a>
                                    @can('user.edit')
                                        <a href="{{ route('users.edit', $user) }}" class="text-blue-500">Edit</a>
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