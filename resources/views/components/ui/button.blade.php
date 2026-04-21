@props([
    'tag' => 'button',
    'size' => 'md',          
    'variant' => 'primary',
    'startIcon' => null,
    'endIcon' => null,
    'className' => '',
    'disabled' => false,
])

@php
    $base = 'inline-flex items-center justify-center font-medium gap-2 rounded-lg transition';

    $sizeMap = [
        'sm' => 'px-4 py-3 text-sm',
        'md' => 'px-5 py-3.5 text-sm',
    ];
    $sizeClass = $sizeMap[$size] ?? $sizeMap['md'];

    $variantMap = [
        'primary' => 'bg-brand-500 text-white shadow-theme-xs hover:bg-brand-600 disabled:bg-brand-300',
        'outline' => 'bg-white text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50',
    ];
    $variantClass = $variantMap[$variant] ?? $variantMap['primary'];

    $disabledClass = $disabled ? 'cursor-not-allowed opacity-50' : '';
    $classes = trim("{$base} {$sizeClass} {$variantClass} {$className} {$disabledClass}");
@endphp

@if($tag === 'a')
    <a {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button 
        {{ $attributes->merge(['class' => $classes, 'type' => $attributes->get('type', 'button')]) }}
        @if($disabled) disabled @endif
    >
        {{ $slot }}
    </button>
@endif