@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen dark:bg-gray-950 flex justify-center">
    <div class="w-full max-w-xl">

        <nav class="mb-6 flex items-center gap-2 text-sm text-gray-500 font-medium">
            <a href="{{ route('users.index') }}" class="hover:text-indigo-600 transition-colors">Manajemen User</a>
            <span>/</span>
            <span class="text-gray-900 dark:text-white">{{ $user->name }}</span>
        </nav>

        @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">{{ session('success') }}</div>
        @endif

        <div class="bg-white dark:bg-white/[0.03] border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden">

            {{-- Header profil --}}
            <div class="px-6 py-8 flex flex-col items-center border-b border-gray-100 dark:border-gray-800">
                <div class="w-16 h-16 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-2xl font-bold text-indigo-700 dark:text-indigo-300 mb-3">
                    {{ $user->initials() }}
                </div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ $user->email }}</p>
                <div class="flex items-center gap-2 mt-3">
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
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $roleCls }}">
                        {{ $user->roleName() }}
                    </span>
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold
                        {{ $user->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>

            {{-- Detail info --}}
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach([
                    'NIP'     => $user->nip ?? '-',
                    'Jabatan' => $user->jabatan ?? '-',
                    'No. HP'  => $user->phone ?? '-',
                ] as $label => $value)
                <div class="px-6 py-4 flex justify-between">
                    <span class="text-sm text-gray-500">{{ $label }}</span>
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200 {{ $label === 'NIP' ? 'font-mono' : '' }}">{{ $value }}</span>
                </div>
                @endforeach

                {{-- Permissions yang dimiliki --}}
                <div class="px-6 py-4">
                    <p class="text-sm text-gray-500 mb-3">Hak Akses</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($user->getAllPermissions()->sortBy('name') as $perm)
                        <span class="inline-flex px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 font-mono">
                            {{ $perm->name }}
                        </span>
                        @endforeach
                    </div>
                </div>

                <div class="px-6 py-3 flex justify-between text-xs text-gray-400">
                    <span>Dibuat: {{ $user->created_at?->format('d M Y') }}</span>
                    <span>Login terakhir: {{ $user->updated_at?->format('d M Y H:i') }}</span>
                </div>
            </div>

            {{-- Actions --}}
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <a href="{{ route('users.index') }}" class="text-sm text-gray-500 hover:text-indigo-600 transition-colors">&larr; Kembali</a>
                <div class="flex gap-2">
                    @can('user.edit')
                    <a href="{{ route('users.edit', $user) }}"
                       class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                        Edit
                    </a>
                    @endcan
                    @can('user.delete')
                    @if($user->id !== auth()->id())
                    <form action="{{ route('users.destroy', $user) }}" method="POST"
                          onsubmit="return confirm('Hapus user {{ $user->name }}?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                            Hapus
                        </button>
                    </form>
                    @endif
                    @endcan
                </div>
            </div>

        </div>
    </div>
</div>
@endsection