@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-950 flex justify-center py-12 px-4">
    <div class="w-full max-w-xl">

        <nav class="mb-6 flex items-center gap-2 text-sm">
            <a href="{{ route('pegawai.opd.index') }}" class="text-gray-400 hover:text-indigo-500 transition">
                Pegawai OPD
            </a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-900 dark:text-white font-semibold truncate">
                {{ $user->name }}
            </span>
        </nav>

        <div class="rounded-2xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-200 dark:ring-gray-800 overflow-hidden">

            <div class="p-6 flex items-center gap-4">
                <div class="w-16 h-16 rounded-xl bg-emerald-600 flex items-center justify-center text-lg font-semibold text-white">
                    {{ $user->initials }}
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $user->name }}
                        </h2>
                        <span class="text-xs px-2 py-0.5 rounded-md font-medium
                            {{ $user->is_active ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }}">
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 truncate">{{ $user->email }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $user->role_name }}</p>
                </div>
            </div>

            <div class="px-6 pb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-400 text-xs mb-1">NIP</p>
                        <p class="font-medium text-gray-800 dark:text-gray-200 font-mono">
                            {{ $user->nip ?: '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs mb-1">No. HP</p>
                        <p class="font-medium text-gray-800 dark:text-gray-200">
                            {{ $user->phone ?: '—' }}
                        </p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-gray-400 text-xs mb-1">Unit OPD yang Di-assign</p>
                        <div class="flex flex-wrap gap-2 mt-1">
                            @forelse($user->opdUnits as $unit)
                                <span class="inline-flex items-center px-3 py-1.5 text-sm bg-blue-50 text-blue-700 rounded-lg border border-blue-100">
                                    {{ $unit->nama_unit }}
                                </span>
                            @empty
                                <span class="text-sm text-gray-400">—</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <p class="text-xs text-gray-400 mb-2">Permissions</p>
                    <div class="flex flex-wrap gap-2">
                        @forelse($user->getAllPermissions()->sortBy('name') as $perm)
                            <span class="text-xs px-2 py-1 rounded-md bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                                {{ str_replace('.', ':', $perm->name) }}
                            </span>
                        @empty
                            <span class="text-xs text-gray-400 italic">Tidak ada permissions</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between px-6 py-4 bg-gray-50 dark:bg-gray-800/40">
                <a href="{{ route('pegawai.opd.index') }}"
                   class="text-sm text-gray-400 hover:text-indigo-500 transition">
                    ← Kembali
                </a>
                <div class="flex gap-2">
                    @can('user.edit')
                    <a href="{{ route('pegawai.opd.edit', $user) }}"
                       class="px-4 py-2 text-sm rounded-lg bg-gray-900 text-white hover:opacity-90 transition">
                        Edit
                    </a>
                    @endcan
                    @can('user.delete')
                    @if($user->id !== auth()->id())
                    <form action="{{ route('pegawai.opd.destroy', $user) }}" method="POST"
                          onsubmit="return confirm('Hapus pegawai ini?')">
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

        <div class="mt-5 text-center text-xs text-gray-400">
            Dibuat {{ $user->created_at?->format('d M Y') }} •
            Update {{ $user->updated_at?->format('d M Y H:i') }}
        </div>

    </div>
</div>
@endsection
