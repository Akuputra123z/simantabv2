@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-wrap items-end justify-between gap-6">
        <div class="space-y-1">
            <nav class="flex items-center gap-2 text-sm font-medium text-gray-400">
                <span class="text-gray-900">Profil Saya</span>
            </nav>
            <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                Profil Saya
            </h1>
            <p class="text-sm text-gray-500">Kelola data diri dan informasi akun Anda.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl bg-green-50 border border-green-200 px-5 py-4 text-sm font-semibold text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-xl bg-red-50 border border-red-200 px-5 py-4 text-sm font-semibold text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('opd.profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Avatar --}}
        <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-xs font-bold uppercase tracking-widest text-gray-900">Foto Profil</h2>
            <div class="flex items-center gap-5">
                <div class="shrink-0">
                    @if($user->avatar)
                        <img class="h-20 w-20 rounded-full object-cover border-2 border-gray-100"
                             src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar">
                    @else
                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 text-2xl font-bold text-gray-500">
                            {{ $user->initials }}
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <label class="mb-1 block text-sm font-bold text-gray-700">Ganti Foto</label>
                    <input type="file" name="avatar" accept="image/*"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    @error('avatar')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400">Format: JPG, PNG, WEBP. Maks 2MB.</p>
                </div>
            </div>
        </div>

        {{-- Data Diri --}}
        <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-xs font-bold uppercase tracking-widest text-gray-900">Data Diri</h2>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-gray-700">Nama Lengkap <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="block w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-gray-700">Email <span class="text-red-400">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="block w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-gray-700">NIP</label>
                    <input type="text" name="nip" value="{{ old('nip', $user->nip) }}"
                           class="block w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('nip')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-gray-700">Pangkat / Golongan</label>
                    <input type="text" name="pangkat_gol" value="{{ old('pangkat_gol', $user->pangkat_gol) }}"
                           class="block w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('pangkat_gol')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-gray-700">Jabatan</label>
                    <input type="text" name="jabatan" value="{{ old('jabatan', $user->jabatan) }}"
                           class="block w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('jabatan')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-gray-700">Unit Kerja</label>
                    <input type="text" name="unit_kerja" value="{{ old('unit_kerja', $user->unit_kerja) }}"
                           class="block w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('unit_kerja')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-gray-700">Pendidikan Terakhir</label>
                    <input type="text" name="pendidikan_terakhir" value="{{ old('pendidikan_terakhir', $user->pendidikan_terakhir) }}"
                           class="block w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('pendidikan_terakhir')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-gray-700">Jenis Kelamin</label>
                    <select name="jenis_kelamin"
                            class="block w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="">-- Pilih --</option>
                        <option value="L" @selected(old('jenis_kelamin', $user->jenis_kelamin) === 'L')>Laki-laki</option>
                        <option value="P" @selected(old('jenis_kelamin', $user->jenis_kelamin) === 'P')>Perempuan</option>
                    </select>
                    @error('jenis_kelamin')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-gray-700">No. Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="block w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('phone')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Tombol --}}
        <div class="flex items-center justify-end gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
