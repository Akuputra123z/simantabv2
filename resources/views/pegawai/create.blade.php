@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen dark:bg-gray-950 flex justify-center py-10">
    <div class="w-full max-w-4xl">

        {{-- Breadcrumb --}}
        <nav class="mb-6 flex items-center gap-2 text-sm font-medium">
            @if($type === 'opd')
                <a href="{{ route('pegawai.opd.index') }}" class="text-gray-500 hover:text-indigo-600 transition-colors">Pegawai OPD</a>
            @else
                <a href="{{ route('pegawai.inspektorat.index') }}" class="text-gray-500 hover:text-indigo-600 transition-colors">Pegawai Inspektorat</a>
            @endif
            <span class="text-gray-400">/</span>
            <span class="text-gray-900 dark:text-white">Tambah User Baru</span>
        </nav>

        {{-- Card --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">

            {{-- Header --}}
            <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Registrasi Pengguna</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Silakan isi data di bawah ini. Kolom dengan tanda <span class="text-red-500">*</span> wajib diisi.
                </p>
            </div>

            {{-- Body --}}
            <div class="p-8">

                {{-- Alert Error --}}
                @if($errors->any())
                <div class="mb-8 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/30 text-red-800 dark:text-red-400">
                    <div class="flex items-center gap-2 font-bold mb-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                        <span>Mohon perbaiki kesalahan berikut:</span>
                    </div>
                    <ul class="list-disc list-inside text-sm opacity-90">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('pegawai.' . $type . '.store') }}" method="POST" id="form-user" class="space-y-8">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">

                        {{-- Identitas Utama --}}
                        <div class="md:col-span-2">
                            <h4 class="text-xs font-bold uppercase tracking-widest text-indigo-600 mb-4">Informasi Pribadi</h4>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nama Lengkap *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all @error('name') border-red-500 @enderror"
                                   placeholder="Masukkan nama sesuai identitas">
                            @error('name') <p class="text-xs text-red-500 mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Email *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all @error('email') border-red-500 @enderror"
                                   placeholder="contoh@email.com">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">No. HP</label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all"
                                   placeholder="0812xxxx">
                        </div>

                        {{-- Data Kepegawaian --}}
                        <div class="md:col-span-2 mt-4">
                            <h4 class="text-xs font-bold uppercase tracking-widest text-indigo-600 mb-4">Data Kepegawaian</h4>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">NIP</label>
                            <input type="text" name="nip" value="{{ old('nip') }}"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-mono"
                                   placeholder="19xxxxxxxxxxxxxx">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all cursor-pointer">
                                <option value="">-- Pilih --</option>
                                <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>

                        @if($type !== 'opd')
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Jabatan</label>
                            <select name="jabatan"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all cursor-pointer @error('jabatan') border-red-500 @enderror">
                                <option value="">-- Pilih Jabatan --</option>
                                @foreach($jabatanOptions as $opt)
                                    <option value="{{ $opt }}" {{ old('jabatan') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('jabatan') <p class="text-xs text-red-500 mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Sub Unit Organisasi</label>
                            <select name="unit_kerja"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all cursor-pointer @error('unit_kerja') border-red-500 @enderror">
                                <option value="">-- Pilih Sub Unit --</option>
                                @foreach($unitKerjaOptions as $opt)
                                    <option value="{{ $opt }}" {{ old('unit_kerja') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('unit_kerja') <p class="text-xs text-red-500 mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>
                        @endif

                        {{-- Keamanan --}}
                        <div class="md:col-span-2 mt-4">
                            <h4 class="text-xs font-bold uppercase tracking-widest text-indigo-600 mb-4">Akses & Keamanan</h4>
                        </div>

                        @if($type === 'opd')
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Role Akses</label>
                            <input type="hidden" name="role" value="opd">
                            <div class="px-4 py-2.5 rounded-xl border border-gray-300 bg-gray-50 text-sm font-semibold text-gray-700">
                                OPD
                            </div>
                        </div>
                        @else
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Role Akses *</label>
                            <select name="role" id="role-select" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all cursor-pointer">
                                <option value="">-- Pilih Role --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                        {{ \App\Models\User::ROLES[$role->name] ?? ucfirst($role->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div id="opd-unit-section" class="md:col-span-2 {{ $type === 'opd' ? '' : 'hidden' }}">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Unit OPD <span class="text-xs text-gray-400 font-normal">(pilih unit yang bisa diakses)</span>
                            </label>
                            <div class="relative mb-3">
                                <input type="text" id="opd-unit-search" placeholder="Cari unit OPD..."
                                       class="w-full px-4 py-2 pl-10 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all">
                                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <div id="opd-unit-grid" class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-60 overflow-y-auto p-4 rounded-xl border border-gray-200 bg-gray-50">
                                @forelse($opdUnits as $unit)
                                <label class="flex items-start gap-3 p-2 rounded-lg hover:bg-white transition-colors cursor-pointer">
                                    <input type="checkbox" name="opd_unit_ids[]" value="{{ $unit->id }}"
                                           {{ in_array($unit->id, old('opd_unit_ids', [])) ? 'checked' : '' }}
                                           class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ $unit->nama_unit }}</p>
                                        <p class="text-xs text-gray-400">{{ $unit->nama_kecamatan ? 'Kec. ' . $unit->nama_kecamatan : '-' }}</p>
                                    </div>
                                </label>
                                @empty
                                <p class="text-sm text-gray-400 col-span-2">Belum ada data unit.</p>
                                @endforelse
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Password *</label>
                            <input type="password" name="password" required
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all"
                                   placeholder="Minimal 8 karakter">
                        </div>

                        <div class="md:col-span-2">
                            <label class="flex items-center group cursor-pointer w-fit">
                                <div class="relative">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-indigo-500/20 dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                </div>
                                <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 transition-colors">Aktifkan Akun Sekarang</span>
                            </label>
                        </div>

                    </div>

                    {{-- Action Buttons --}}
                    <div class="mt-10 flex items-center justify-end gap-4 pt-6 border-t border-gray-100 dark:border-gray-800">
                        @if($type === 'opd')
                            <a href="{{ route('pegawai.opd.index') }}"
                               class="px-6 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                                Batal
                            </a>
                        @else
                            <a href="{{ route('pegawai.inspektorat.index') }}"
                               class="px-6 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                                Batal
                            </a>
                        @endif

                        <button type="submit" id="btn-submit"
                                class="flex items-center gap-2 px-8 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-lg shadow-indigo-200 dark:shadow-none transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg id="btn-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span id="btn-label">Simpan Data User</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('form-user').addEventListener('submit', function (e) {
        const btn = document.getElementById('btn-submit');
        const label = document.getElementById('btn-label');
        const icon = document.getElementById('btn-icon');

        btn.disabled = true;
        label.innerText = 'Sedang Menyimpan...';
        icon.classList.add('hidden');
    });

    @if($type !== 'opd')
    const roleSelect = document.getElementById('role-select');
    const opdSection = document.getElementById('opd-unit-section');

    function toggleOpdSection() {
        if (roleSelect.value === 'opd') {
            opdSection.classList.remove('hidden');
            const searchEl = document.getElementById('opd-unit-search');
            if (searchEl) { searchEl.value = ''; searchEl.dispatchEvent(new Event('keyup')); }
        } else {
            opdSection.classList.add('hidden');
        }
    }

    roleSelect.addEventListener('change', toggleOpdSection);
    toggleOpdSection();
    @endif

    const searchInput = document.getElementById('opd-unit-search');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const keyword = this.value.toLowerCase().trim();
            document.querySelectorAll('#opd-unit-grid > label').forEach(function (label) {
                label.style.display = label.textContent.toLowerCase().includes(keyword) ? '' : 'none';
            });
        });
    }
</script>
@endsection
