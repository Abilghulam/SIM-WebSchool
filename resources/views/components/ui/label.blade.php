@props([
    'value' => null,
    'variant' => 'default', // default | success | warning | danger | info
])

@php
    $variants = [
        'default' => 'text-gray-700',
        'success' => 'text-green-700',
        'warning' => 'text-amber-700',
        'danger' => 'text-red-700',
        'info' => 'text-blue-700',
    ];

    $cls = $variants[$variant] ?? $variants['default'];
@endphp

<label {{ $attributes->merge([
    'class' => "block text-sm font-medium mb-1 {$cls}",
]) }}>
    {{ $value ?? $slot }}
</label>
