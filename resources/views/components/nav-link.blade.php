@props(['active'])

@php
    $classes =
        $active ?? false
            ? 'inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold text-white bg-white/10 transition'
            : 'inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold text-white/80 hover:text-white hover:bg-white/10 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
