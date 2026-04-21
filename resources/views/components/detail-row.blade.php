@props(['label', 'value', 'isMono' => false])

<div class="flex flex-col pb-4 border-b border-gray-50 last:border-0 last:pb-0">
    <dt class="text-xs font-bold text-gray-400 uppercase tracking-wider font-sans">
        {{ $label }}
    </dt>
    <dd class="mt-1 text-sm text-gray-900 {{ $isMono ? 'font-mono font-medium' : 'font-sans' }}">
        {{ $value ?? $slot }}
    </dd>
</div>