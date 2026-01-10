@props([
    'type' => 'info', // info|success|warning|danger
])

@php
    $variants = [
        'info' => 'bg-blue-50 text-blue-800 border-blue-200',
        'success' => 'bg-green-50 text-green-800 border-green-200',
        'warning' => 'bg-amber-50 text-amber-800 border-amber-200',
        'danger' => 'bg-red-50 text-red-800 border-red-200',
    ];

    $cls = $variants[$type] ?? $variants['info'];
@endphp

<div {{ $attributes->merge(['class' => "border rounded-lg px-4 py-3 text-sm {$cls}"]) }}>
    {{ $slot }}
</div>
