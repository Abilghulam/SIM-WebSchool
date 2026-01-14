@props(['active'])

@php
    $classes =
        $active ?? false
            ? 'block w-full px-4 py-2 text-start text-base font-semibold text-white bg-white/10 transition'
            : 'block w-full px-4 py-2 text-start text-base font-medium text-white/80 hover:text-white hover:bg-white/10 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
