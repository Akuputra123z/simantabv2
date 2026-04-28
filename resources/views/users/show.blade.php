@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen dark:bg-gray-950 flex justify-center py-12">
    <div class="w-full max-w-xl"> {{-- Ukuran ideal: Tidak kekecilan, tidak kebesaran --}}

        {{-- Breadcrumb: Lebih bersih tanpa background --}}
        <nav class="mb-6 flex items-center gap-2 text-sm font-medium tracking-tight">
            <a href="{{ route('users.index') }}" class="text-gray-400 hover:text-indigo-600 transition-colors">Manajemen User</a>
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span class="text-gray-900 dark:text-white font-bold">{{ $user->name }}</span>
        </nav>

        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl shadow-sm overflow-hidden">

            {{-- Header: Layout Sejajar (Side-by-side) yang elegan --}}
            <div class="px-8 py-8 flex items-center gap-6 border-b border-gray-50 dark:border-gray-800">
                <div class="flex-shrink-0 w-20 h-20 rounded-2xl bg-indigo-600 flex items-center justify-center text-2xl font-bold text-white shadow-lg shadow-indigo-100 dark:shadow-none">
                   {{ $user->initials }}
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-1">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white truncate tracking-tight">{{ $user->name }}</h2>
                        <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-widest {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate mb-3">{{ $user->email }}</p>
                    
                    <span class="inline-flex px-3 py-1 rounded-lg text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                        {{ $user->role_name }}
                    </span>
                </div>
            </div>

            {{-- Info Content --}}
            <div class="px-8 py-6">
                <div class="space-y-4">
                    @php
                        $details = [
                            ['label' => 'Nomor Induk / NIP', 'value' => $user->nip, 'mono' => true],
                            ['label' => 'Jabatan & Golongan', 'value' => $user->jabatan . ' (' . ($user->pangkat_gol ?: '-') . ')'],
                            ['label' => 'Unit Kerja', 'value' => $user->unit_kerja],
                            ['label' => 'Pendidikan Terakhir', 'value' => $user->pendidikan_terakhir],
                            ['label' => 'Kontak', 'value' => $user->phone],
                        ];
                    @endphp

                    @foreach($details as $item)
                    <div class="flex flex-col">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1">{{ $item['label'] }}</span>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200 {{ ($item['mono'] ?? false) ? 'font-mono tracking-wider text-indigo-600 dark:text-indigo-400' : '' }}">
                            {{ $item['value'] ?: '—' }}
                        </span>
                    </div>
                    @endforeach
                </div>

                {{-- Permissions: Lebih visual --}}
                <div class="mt-8 pt-6 border-t border-gray-50 dark:border-gray-800">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3 text-center">Sistem Hak Akses</p>
                    <div class="flex flex-wrap justify-center gap-2">
                        @forelse($user->getAllPermissions()->sortBy('name') as $perm)
                        <span class="px-2.5 py-1 rounded-md bg-gray-50 dark:bg-gray-800/50 text-[11px] font-medium text-gray-600 dark:text-gray-400 border border-gray-100 dark:border-gray-800">
                            {{ str_replace('.', ':', $perm->name) }}
                        </span>
                        @empty
                        <span class="text-xs text-gray-400 italic">No specific permissions granted.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Action Buttons: Bersih dan Terfokus --}}
            <div class="px-8 py-6 bg-gray-50/50 dark:bg-gray-800/30 flex items-center justify-between">
                <a href="{{ route('users.index') }}" class="group flex items-center gap-2 text-sm font-bold text-gray-400 hover:text-indigo-600 transition-colors">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Kembali
                </a>

                <div class="flex items-center gap-3">
                    @can('user.edit')
                    <a href="{{ route('users.edit', $user) }}"
                       class="px-5 py-2 text-sm font-bold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:shadow-md transition-all">
                        Edit Profil
                    </a>
                    @endcan

                    @can('user.delete')
                    @if($user->id !== auth()->id())
                    <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus user ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-5 py-2 text-sm font-bold text-white bg-rose-600 rounded-xl hover:bg-rose-700 hover:shadow-lg hover:shadow-rose-100 transition-all transform active:scale-95">
                            Hapus
                        </button>
                    </form>
                    @endif
                    @endcan
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-center gap-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">
            <span>Dibuat: {{ $user->created_at?->format('d/m/Y') }}</span>
            <span class="text-gray-200 dark:text-gray-800">|</span>
            <span>Update: {{ $user->updated_at?->format('d/m/Y H:i') }}</span>
        </div>
    </div>
</div>
@endsection