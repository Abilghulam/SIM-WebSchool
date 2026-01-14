@props([
    'variant' => 'primary', // primary | secondary | danger | ghost
    'size' => 'md', // sm | md
    'type' => 'button',
])

@php
    $base =
        'inline-flex items-center justify-center gap-2 font-semibold rounded-lg transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
    ];

    $variants = [
        'primary' => 'bg-navy-700 text-white hover:bg-navy-600 focus:ring-navy-500',
        'secondary' => 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-gray-400',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'ghost' => 'bg-transparent text-gray-700 hover:bg-gray-100 focus:ring-gray-400',
    ];
@endphp

<button type="{{ $type }}"
    {{ $attributes->merge(['class' => $base . ' ' . $sizes[$size] . ' ' . $variants[$variant]]) }}>
    {{ $slot }}
</button>
