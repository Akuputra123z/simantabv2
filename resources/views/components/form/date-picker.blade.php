<div x-data="{}" x-init="flatpickr($refs.input, { dateFormat: 'Y-m-d', defaultDate: '{{ $defaultDate ?? '' }}' })">
    <input 
        x-ref="input"
        type="text" 
        id="{{ $id ?? '' }}" 
        name="{{ $name ?? '' }}" 
        placeholder="{{ $placeholder ?? 'Pilih tanggal' }}"
        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
    />
</div>