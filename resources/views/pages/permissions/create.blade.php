@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen dark:bg-gray-950">
    <div class="max-w-3xl mx-auto">

        <nav class="mb-5 flex items-center gap-2 text-sm text-gray-500 font-medium">
            <a href="{{ route('permissions.index') }}" class="hover:text-indigo-600 transition-colors">Role & Permission</a>
            <span>/</span>
            <span class="text-gray-900 dark:text-white">Tambah Role</span>
        </nav>

        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-white/[0.03] shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Role Baru</h3>
                <p class="text-sm text-gray-500 mt-0.5">Nama role hanya boleh huruf kecil, angka, dan underscore.</p>
            </div>

            <div class="p-6">

                @if($errors->any())
                <div class="mb-5 p-4 rounded-lg bg-red-50 border border-red-200 text-sm text-red-800 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
                    @foreach($errors->all() as $e) <p>{{ $e }}</p> @endforeach
                </div>
                @endif

                <form action="{{ route('permissions.store') }}" method="POST" id="form-create">
                    @csrf

                    {{-- Nama role --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Nama Role <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               pattern="[a-z0-9_]+" placeholder="contoh: koordinator_wilayah"
                               class="block w-full h-11 px-4 text-sm border {{ $errors->has('name') ? 'border-red-500' : 'border-gray-300 dark:border-gray-700' }} rounded-lg bg-transparent dark:text-white focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/20 font-mono">
                        <p class="mt-1 text-xs text-gray-400">Contoh: <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">ketua_tim</code>, <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">koordinator</code></p>
                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Permission (opsional saat create, bisa diatur nanti) --}}
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Permission Awal <span class="text-gray-400 font-normal">(opsional — bisa diatur setelah membuat role)</span>
                            </label>
                            <div class="flex gap-2">
                                <button type="button" onclick="selectAll()"
                                        class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">Pilih semua</button>
                                <span class="text-gray-300">|</span>
                                <button type="button" onclick="clearAll()"
                                        class="text-xs text-gray-500 hover:text-gray-600">Hapus semua</button>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @foreach($permissionGroups as $group => $perms)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                                <div class="flex items-center gap-3 px-4 py-2.5 bg-gray-50 dark:bg-white/[0.02] border-b border-gray-100 dark:border-gray-800">
                                    <input type="checkbox" id="grp-{{ Str::slug($group) }}"
                                           class="w-4 h-4 rounded border-gray-300 text-indigo-600 cursor-pointer"
                                           onchange="toggleGroup('{{ Str::slug($group) }}', this.checked)">
                                    <label for="grp-{{ Str::slug($group) }}"
                                           class="text-sm font-semibold text-gray-700 dark:text-gray-300 cursor-pointer">
                                        {{ $group }}
                                    </label>
                                </div>
                                <div class="px-4 py-3 grid grid-cols-2 sm:grid-cols-3 gap-2">
                                    @foreach($perms as $permission)
                                    @php
                                        $action = explode('.', $permission)[1] ?? $permission;
                                        $actionLabel = match($action) {
                                            'view' => 'Lihat', 'create' => 'Tambah', 'edit' => 'Edit',
                                            'delete' => 'Hapus', 'verifikasi' => 'Verifikasi',
                                            'manage' => 'Kelola', 'export' => 'Export',
                                            'assign-role' => 'Assign Role', default => ucfirst($action),
                                        };
                                    @endphp
                                    <label class="flex items-center gap-2 cursor-pointer p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission }}"
                                               {{ in_array($permission, old('permissions', [])) ? 'checked' : '' }}
                                               class="perm-check w-4 h-4 rounded border-gray-300 text-indigo-600"
                                               data-group="{{ Str::slug($group) }}">
                                        <div>
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $actionLabel }}</span>
                                            <p class="text-xs text-gray-400 font-mono leading-tight">{{ $permission }}</p>
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-5 border-t border-gray-100 dark:border-gray-800">
                        <a href="{{ route('permissions.index') }}"
                           class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                            Batal
                        </a>
                        <button type="submit" id="btn-submit"
                                class="px-6 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm disabled:opacity-50">
                            <span id="btn-label">Buat Role</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function getGroupChecks(g) { return document.querySelectorAll(`.perm-check[data-group="${g}"]`); }
function getAllChecks()     { return document.querySelectorAll('.perm-check'); }

function toggleGroup(g, checked) {
    getGroupChecks(g).forEach(c => c.checked = checked);
}
function selectAll() {
    getAllChecks().forEach(c => c.checked = true);
    document.querySelectorAll('[id^="grp-"]').forEach(m => { m.checked = true; m.indeterminate = false; });
}
function clearAll() {
    getAllChecks().forEach(c => c.checked = false);
    document.querySelectorAll('[id^="grp-"]').forEach(m => { m.checked = false; m.indeterminate = false; });
}

document.getElementById('form-create').addEventListener('submit', function () {
    document.getElementById('btn-submit').disabled = true;
    document.getElementById('btn-label').textContent = 'Membuat...';
});
</script>
@endsection