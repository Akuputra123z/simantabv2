@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen dark:bg-gray-950 flex justify-center py-12">
    <div class="w-full max-w-3xl">

        {{-- Breadcrumb --}}
        <nav class="mb-6 flex items-center gap-2 text-sm font-medium">
            <a href="{{ route('users.index') }}" class="text-gray-500 hover:text-indigo-600 transition-colors">Manajemen User</a>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="9 5l7 7-7 7"/></svg>
            <span class="text-gray-900 dark:text-gray-200">Edit Profil</span>
        </nav>

        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            
            {{-- Header --}}
            <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-800 bg-white dark:bg-transparent">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Informasi Pengguna</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Perbarui detail akun dan kredensial akses di sini.</p>
            </div>

            <div class="p-8">
                {{-- Error Alerts --}}
                @if($errors->any())
                <div class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                        </div>
                        <div class="ml-3">
                            <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-400">
                                @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-8">
                    @csrf @method('PUT')

                    {{-- Section 1: Data Diri --}}
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-4">Data Pribadi</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-input-styled" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email <span class="text-red-500">*</span></label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-input-styled" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No. HP</label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input-styled" placeholder="0812...">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-input-styled">
                                    <option value="">Pilih</option>
                                    <option value="L" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pendidikan Terakhir</label>
                                <input type="text" name="pendidikan_terakhir" value="{{ old('pendidikan_terakhir', $user->pendidikan_terakhir) }}" class="form-input-styled">
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-100 dark:border-gray-800">

                    {{-- Section 2: Kepegawaian --}}
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-4">Detail Kepegawaian</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NIP</label>
                                <input type="text" name="nip" value="{{ old('nip', $user->nip) }}" class="form-input-styled">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pangkat / Golongan</label>
                                <input type="text" name="pangkat_gol" value="{{ old('pangkat_gol', $user->pangkat_gol) }}" class="form-input-styled">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jabatan</label>
                                <input type="text" name="jabatan" value="{{ old('jabatan', $user->jabatan) }}" class="form-input-styled">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unit Kerja</label>
                                <input type="text" name="unit_kerja" value="{{ old('unit_kerja', $user->unit_kerja) }}" class="form-input-styled">
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-100 dark:border-gray-800">

                    {{-- Section 3: Akun & Keamanan --}}
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-4">Kredensial & Hak Akses</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role Akses <span class="text-red-500">*</span></label>
                                <select name="role" required class="form-input-styled">
                                    @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ old('role', $user->getRoleNames()->first()) === $role->name ? 'selected' : '' }}>
                                        {{ \App\Models\User::ROLES[$role->name] ?? $role->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Password Baru 
                                    <span class="text-[10px] text-gray-400 font-normal ml-1">(Opsional)</span>
                                </label>
                                <input type="password" name="password" class="form-input-styled" placeholder="••••••••">
                            </div>

                            <div class="md:col-span-2 flex items-center gap-3 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                    class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                <label for="is_active" class="text-sm font-semibold text-gray-700 dark:text-gray-200 cursor-pointer">
                                    Akun Aktif
                                    <span class="block text-xs font-normal text-gray-500">User dapat login ke sistem jika status aktif.</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-6 flex items-center justify-end gap-3">
                        <a href="{{ route('users.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-all">
                            Batal
                        </a>
                        <button type="submit" class="px-8 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm shadow-indigo-200 dark:shadow-none transition-all transform active:scale-95">
                            Update Data User
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern Input Styling */
.form-input-styled {
    width: 100%;
    padding: 0.625rem 0.875rem;
    font-size: 0.875rem;
    background-color: transparent;
    border: 1px solid #e5e7eb; /* gray-200 */
    border-radius: 0.5rem;
    transition: all 0.2s;
}
.dark .form-input-styled {
    border-color: #374151; /* gray-700 */
    color: #f3f4f6; /* gray-100 */
}
.form-input-styled:focus {
    outline: none;
    border-color: #4f46e5; /* indigo-600 */
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}
</style>
@endsection