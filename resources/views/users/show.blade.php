@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-950 flex justify-center py-12 px-4">
    <div class="w-full max-w-xl">

        {{-- Breadcrumb (lebih subtle) --}}
        <nav class="mb-6 flex items-center gap-2 text-sm">
            <a href="{{ route('users.index') }}" class="text-gray-400 hover:text-indigo-500 transition">
                User
            </a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-900 dark:text-white font-semibold truncate">
                {{ $user->name }}
            </span>
        </nav>

        <div class="rounded-2xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-200 dark:ring-gray-800 overflow-hidden">

            {{-- HEADER --}}
            <div class="p-6 flex items-center gap-4">

                {{-- Avatar --}}
                <div class="w-16 h-16 rounded-xl bg-indigo-600 flex items-center justify-center text-lg font-semibold text-white">
                    {{ $user->initials }}
                </div>

                {{-- Info --}}
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $user->name }}
                        </h2>

                        <span class="text-xs px-2 py-0.5 rounded-md font-medium
                            {{ $user->is_active ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <p class="text-sm text-gray-500 truncate">
                        {{ $user->email }}
                    </p>

                    <p class="text-xs text-gray-400 mt-1">
                        {{ $user->role_name }}
                    </p>
                </div>
            </div>

            {{-- CONTENT --}}
            <div class="px-6 pb-6">

                {{-- GRID INFO --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">

                    <div>
                        <p class="text-gray-400 text-xs mb-1">NIP</p>
                        <p class="font-medium text-gray-800 dark:text-gray-200 font-mono">
                            {{ $user->nip ?: '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-400 text-xs mb-1">Jabatan</p>
                        <p class="font-medium text-gray-800 dark:text-gray-200">
                            {{ $user->jabatan }} ({{ $user->pangkat_gol ?: '-' }})
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-400 text-xs mb-1">Unit Kerja</p>
                        <p class="font-medium text-gray-800 dark:text-gray-200">
                            {{ $user->unit_kerja ?: '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-400 text-xs mb-1">Pendidikan</p>
                        <p class="font-medium text-gray-800 dark:text-gray-200">
                            {{ $user->pendidikan_terakhir ?: '—' }}
                        </p>
                    </div>

                    <div class="sm:col-span-2">
                        <p class="text-gray-400 text-xs mb-1">Kontak</p>
                        <p class="font-medium text-gray-800 dark:text-gray-200">
                            {{ $user->phone ?: '—' }}
                        </p>
                    </div>
                </div>

                {{-- PERMISSIONS --}}
                <div class="mt-6">
                    <p class="text-xs text-gray-400 mb-2">Permissions</p>

                    <div class="flex flex-wrap gap-2">
                        @forelse($user->getAllPermissions()->sortBy('name') as $perm)
                            <span class="text-xs px-2 py-1 rounded-md bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                                {{ str_replace('.', ':', $perm->name) }}
                            </span>
                        @empty
                            <span class="text-xs text-gray-400 italic">
                                No permissions
                            </span>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ACTION --}}
            <div class="flex items-center justify-between px-6 py-4 bg-gray-50 dark:bg-gray-800/40">

                <a href="{{ route('users.index') }}"
                   class="text-sm text-gray-400 hover:text-indigo-500 transition">
                    ← Kembali
                </a>

                <div class="flex gap-2">

                    @can('user.edit')
                    <a href="{{ route('users.edit', $user) }}"
                       class="px-4 py-2 text-sm rounded-lg bg-gray-900 text-white hover:opacity-90 transition">
                        Edit
                    </a>
                    @endcan

                    @can('user.delete')
                    @if($user->id !== auth()->id())
                    <form action="{{ route('users.destroy', $user) }}" method="POST"
                          onsubmit="return confirm('Hapus user ini?')">
                        @csrf @method('DELETE')

                        <button class="px-4 py-2 text-sm rounded-lg bg-rose-600 text-white hover:bg-rose-700 transition">
                            Hapus
                        </button>
                    </form>
                    @endif
                    @endcan

                </div>
            </div>
        </div>

        {{-- FOOTER META --}}
        <div class="mt-5 text-center text-xs text-gray-400">
            Dibuat {{ $user->created_at?->format('d M Y') }} •
            Update {{ $user->updated_at?->format('d M Y H:i') }}
        </div>

    </div>
</div>
@endsection