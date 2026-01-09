@props([
    'variant' => 'gray', // gray | green | blue | amber | red
])

@php
    $variants = [
        'gray'  => 'bg-gray-50 text-gray-700 border-gray-200',
        'green' => 'bg-green-50 text-green-700 border-green-200',
        'blue'  => 'bg-blue-50 text-blue-700 border-blue-200',
        'amber' => 'bg-amber-50 text-amber-700 border-amber-200',
        'red'   => 'bg-red-50 text-red-700 border-red-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2 py-1 rounded-lg border text-xs font-semibold '.$variants[$variant]]) }}>
    {{ $slot }}
</span>
