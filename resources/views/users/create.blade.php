@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen dark:bg-gray-950 flex justify-center">
    <div class="w-full max-w-2xl">

        <nav class="mb-6 flex items-center gap-2 text-sm text-gray-500 font-medium">
            <a href="{{ route('users.index') }}" class="hover:text-indigo-600 transition-colors">Manajemen User</a>
            <span>/</span>
            <span class="text-gray-900 dark:text-white">Tambah User</span>
        </nav>

        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-white/[0.03] shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Tambah User Baru</h3>
                <p class="text-sm text-gray-500 mt-0.5">Kolom bertanda <span class="text-red-500">*</span> wajib diisi.</p>
            </div>

            <div class="p-6">
                @if($errors->any())
                <div class="mb-5 p-4 rounded-lg bg-red-50 border border-red-200 text-sm text-red-800 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
                    <p class="font-medium mb-1">Mohon perbaiki kesalahan berikut:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('users.store') }}" method="POST" id="form-user">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        {{-- Nama --}}
                        <div class="md:col-span-2">
                            <label class="label-field">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="input-field {{ $errors->has('name') ? 'border-red-500' : '' }}"
                                   placeholder="Nama sesuai identitas">
                            @error('name') <p class="err-msg">{{ $message }}</p> @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="label-field">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   class="input-field {{ $errors->has('email') ? 'border-red-500' : '' }}"
                                   placeholder="email@inspektorat.go.id">
                            @error('email') <p class="err-msg">{{ $message }}</p> @enderror
                        </div>

                        {{-- NIP --}}
                        <div>
                            <label class="label-field">NIP</label>
                            <input type="text" name="nip" value="{{ old('nip') }}"
                                   class="input-field font-mono {{ $errors->has('nip') ? 'border-red-500' : '' }}"
                                   placeholder="18 digit NIP" maxlength="30">
                            @error('nip') <p class="err-msg">{{ $message }}</p> @enderror
                        </div>

                        {{-- Jabatan --}}
                        <div>
                            <label class="label-field">Jabatan</label>
                            <input type="text" name="jabatan" value="{{ old('jabatan') }}"
                                   class="input-field" placeholder="Contoh: Auditor Muda">
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label class="label-field">No. HP</label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                   class="input-field" placeholder="08xx-xxxx-xxxx">
                        </div>

                        {{-- Role --}}
                        <div>
                            <label class="label-field">Role <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select name="role" required class="input-field appearance-none {{ $errors->has('role') ? 'border-red-500' : '' }}">
                                    <option value="">-- Pilih Role --</option>
                                    @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                                        {{ \App\Models\User::ROLES[$role->name] ?? ucfirst(str_replace('_', ' ', $role->name)) }}
                                    </option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </span>
                            </div>
                            @error('role') <p class="err-msg">{{ $message }}</p> @enderror
                        </div>

                        {{-- Password --}}
                        <div>
                            <label class="label-field">Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password" required
                                   class="input-field {{ $errors->has('password') ? 'border-red-500' : '' }}"
                                   placeholder="Min. 8 karakter, huruf + angka">
                            @error('password') <p class="err-msg">{{ $message }}</p> @enderror
                        </div>

                        {{-- Status --}}
                        <div class="md:col-span-2">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <div class="relative">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" id="is_active"
                                           {{ old('is_active', '1') === '1' ? 'checked' : '' }}
                                           class="sr-only peer">
                                    <div class="w-10 h-6 bg-gray-200 peer-checked:bg-indigo-600 rounded-full transition-colors dark:bg-gray-700"></div>
                                    <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Akun Aktif</span>
                            </label>
                        </div>

                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3 pt-5 border-t border-gray-100 dark:border-gray-800">
                        <a href="{{ route('users.index') }}"
                           class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                            Batal
                        </a>
                        <button type="submit" id="btn-submit"
                                class="px-6 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm disabled:opacity-50">
                            <span id="btn-label">Simpan User</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.label-field { display:block; font-size:.8125rem; font-weight:500; color:#374151; margin-bottom:.375rem; }
.dark .label-field { color:#9ca3af; }
.input-field { display:block; width:100%; height:2.75rem; padding:.625rem 1rem; font-size:.875rem; border:1px solid #d1d5db; border-radius:.5rem; background:transparent; transition:border-color .15s; }
.dark .input-field { border-color:#374151; color:#f3f4f6; }
.input-field:focus { outline:none; border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.1); }
.input-field.border-red-500 { border-color:#ef4444; }
.err-msg { margin-top:.25rem; font-size:.75rem; color:#ef4444; }
</style>
<script>
document.getElementById('form-user').addEventListener('submit', function () {
    const btn = document.getElementById('btn-submit');
    btn.disabled = true;
    document.getElementById('btn-label').innerText = 'Menyimpan...';
});
</script>
@endsection