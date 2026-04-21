@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen dark:bg-gray-950">
    <div class="max-w-5xl mx-auto">

        {{-- Breadcrumb --}}
        <nav class="mb-5 flex items-center gap-2 text-sm text-gray-500 font-medium">
            <a href="{{ route('permissions.index') }}" class="hover:text-indigo-600 transition-colors">Role & Permission</a>
            <span>/</span>
            <span class="text-gray-900 dark:text-white">
                {{ \App\Models\User::ROLES[$role->name] ?? ucfirst(str_replace('_', ' ', $role->name)) }}
            </span>
        </nav>

        {{-- Info role --}}
        <div class="mb-5 flex flex-wrap items-center gap-4 p-4 bg-white dark:bg-white/[0.03] border border-gray-200 dark:border-gray-800 rounded-xl">
            <div>
                <p class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Role</p>
                <p class="font-bold text-gray-900 dark:text-white mt-0.5">
                    {{ \App\Models\User::ROLES[$role->name] ?? ucfirst(str_replace('_', ' ', $role->name)) }}
                    <span class="font-mono text-xs text-gray-400 font-normal ml-1">({{ $role->name }})</span>
                </p>
            </div>
            <div class="h-8 w-px bg-gray-200 dark:bg-gray-700 hidden sm:block"></div>
            <div>
                <p class="text-xs text-gray-400 uppercase font-semibold tracking-wide">User dengan role ini</p>
                <p class="font-bold text-gray-900 dark:text-white mt-0.5">{{ $userCount }} user</p>
            </div>
            <div class="h-8 w-px bg-gray-200 dark:bg-gray-700 hidden sm:block"></div>
            <div>
                <p class="text-xs text-gray-400 uppercase font-semibold tracking-wide">Permission aktif</p>
                <p class="font-bold text-gray-900 dark:text-white mt-0.5" id="active-count">
                    {{ count($rolePermissions) }}
                </p>
            </div>
            {{-- Tombol select all / clear all --}}
            <div class="ml-auto flex gap-2">
                <button type="button" onclick="selectAll()"
                        class="px-3 py-1.5 text-xs font-medium text-indigo-600 border border-indigo-300 dark:border-indigo-700 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                    Pilih Semua
                </button>
                <button type="button" onclick="clearAll()"
                        class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    Hapus Semua
                </button>
            </div>
        </div>

        <form action="{{ route('permissions.update', $role) }}" method="POST" id="form-permission">
            @csrf @method('PUT')

            {{-- Grid matrix permission per modul --}}
            <div class="space-y-3 mb-6">
                @foreach($permissionGroups as $group => $perms)
                <div class="bg-white dark:bg-white/[0.03] border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden">

                    {{-- Header grup dengan toggle select-all per grup --}}
                    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-white/[0.02]">
                        <div class="flex items-center gap-3">
                            {{-- Checkbox master per grup --}}
                            <input type="checkbox"
                                   id="group-{{ Str::slug($group) }}"
                                   class="group-master w-4 h-4 rounded border-gray-300 text-indigo-600 cursor-pointer"
                                   data-group="{{ Str::slug($group) }}"
                                   onchange="toggleGroup('{{ Str::slug($group) }}', this.checked)">
                            <label for="group-{{ Str::slug($group) }}"
                                   class="text-sm font-semibold text-gray-700 dark:text-gray-300 cursor-pointer">
                                {{ $group }}
                            </label>
                        </div>
                        <span class="text-xs text-gray-400 group-count" id="count-{{ Str::slug($group) }}">
                            {{ collect($perms)->filter(fn($p) => in_array($p, $rolePermissions))->count() }}/{{ count($perms) }}
                        </span>
                    </div>

                    {{-- Checkbox per permission dalam grup --}}
                    <div class="px-5 py-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        @foreach($perms as $permission)
                        @php
                            $checked  = in_array($permission, $rolePermissions);
                            $action   = explode('.', $permission)[1] ?? $permission;
                            $actionLabel = match($action) {
                                'view'       => 'Lihat',
                                'create'     => 'Tambah',
                                'edit'       => 'Edit',
                                'delete'     => 'Hapus',
                                'verifikasi' => 'Verifikasi',
                                'manage'     => 'Kelola',
                                'export'     => 'Export',
                                'assign-role'=> 'Assign Role',
                                default      => ucfirst($action),
                            };
                            $actionColor = match($action) {
                                'delete'     => 'text-red-600 dark:text-red-400',
                                'verifikasi' => 'text-purple-600 dark:text-purple-400',
                                'manage'     => 'text-amber-600 dark:text-amber-400',
                                'export'     => 'text-teal-600 dark:text-teal-400',
                                'assign-role'=> 'text-orange-600 dark:text-orange-400',
                                default      => 'text-gray-700 dark:text-gray-300',
                            };
                        @endphp
                        <label class="flex items-center gap-2.5 cursor-pointer group/item p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                            <input type="checkbox"
                                   name="permissions[]"
                                   value="{{ $permission }}"
                                   {{ $checked ? 'checked' : '' }}
                                   class="perm-check w-4 h-4 rounded border-gray-300 text-indigo-600 cursor-pointer"
                                   data-group="{{ Str::slug($group) }}"
                                   onchange="updateGroupState('{{ Str::slug($group) }}'); updateCount()">
                            <div>
                                <span class="text-sm font-medium {{ $actionColor }}">{{ $actionLabel }}</span>
                                <p class="text-xs text-gray-400 font-mono leading-tight">{{ $permission }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Sticky action bar --}}
            <div class="sticky bottom-4 z-10">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg px-6 py-4 flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        <span id="footer-count" class="font-semibold text-gray-800 dark:text-gray-200">{{ count($rolePermissions) }}</span>
                        permission dipilih
                        @if($userCount > 0)
                        <span class="ml-2 text-xs text-amber-600 dark:text-amber-400">
                            · perubahan langsung berlaku untuk {{ $userCount }} user
                        </span>
                        @endif
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('permissions.index') }}"
                           class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                            Batal
                        </a>
                        <button type="submit" id="btn-save"
                                class="px-6 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm disabled:opacity-50">
                            <span id="btn-label">Simpan Permission</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>

<script>
// ── Helpers ───────────────────────────────────────────────────────────────────

function getAllChecks() {
    return document.querySelectorAll('.perm-check');
}

function getGroupChecks(groupSlug) {
    return document.querySelectorAll(`.perm-check[data-group="${groupSlug}"]`);
}

// Hitung total yang dicentang dan update counter di badge + footer
function updateCount() {
    const total = [...getAllChecks()].filter(c => c.checked).length;
    document.getElementById('active-count').textContent  = total;
    document.getElementById('footer-count').textContent  = total;
}

// Update state checkbox master (checklist/indeterminate/unchecked) per grup
function updateGroupState(groupSlug) {
    const checks = [...getGroupChecks(groupSlug)];
    const master = document.getElementById('group-' + groupSlug);
    const counter= document.getElementById('count-' + groupSlug);
    if (!master) return;

    const checkedCount = checks.filter(c => c.checked).length;
    master.checked       = checkedCount === checks.length;
    master.indeterminate = checkedCount > 0 && checkedCount < checks.length;
    if (counter) counter.textContent = `${checkedCount}/${checks.length}`;
}

// Toggle semua checkbox dalam satu grup
function toggleGroup(groupSlug, checked) {
    getGroupChecks(groupSlug).forEach(c => { c.checked = checked; });
    updateGroupState(groupSlug);
    updateCount();
}

// Select all / clear all
function selectAll() {
    getAllChecks().forEach(c => { c.checked = true; });
    document.querySelectorAll('.group-master').forEach(m => {
        m.checked = true; m.indeterminate = false;
    });
    document.querySelectorAll('.group-count').forEach(el => {
        const slug = el.id.replace('count-', '');
        const total = getGroupChecks(slug).length;
        el.textContent = `${total}/${total}`;
    });
    updateCount();
}

function clearAll() {
    getAllChecks().forEach(c => { c.checked = false; });
    document.querySelectorAll('.group-master').forEach(m => {
        m.checked = false; m.indeterminate = false;
    });
    document.querySelectorAll('.group-count').forEach(el => {
        const slug = el.id.replace('count-', '');
        const total = getGroupChecks(slug).length;
        el.textContent = `0/${total}`;
    });
    updateCount();
}

// Init semua grup state saat halaman load
document.addEventListener('DOMContentLoaded', () => {
    @foreach($permissionGroups as $group => $perms)
    updateGroupState('{{ Str::slug($group) }}');
    @endforeach

    // Anti double submit
    document.getElementById('form-permission').addEventListener('submit', function () {
        document.getElementById('btn-save').disabled = true;
        document.getElementById('btn-label').textContent = 'Menyimpan...';
    });
});
</script>
@endsection