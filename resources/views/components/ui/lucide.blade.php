@props([
    'name' => null, // user-round | users-round | user-star | school | calendar-check-2 | chevron-right
    'size' => 20,
    'class' => '',
])

@php
    $common = "fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'";
@endphp

@if ($name === 'chevron-right')
    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24"
        {!! $common !!} class="{{ $class }}">
        <path d="m9 18 6-6-6-6" />
    </svg>
@elseif ($name === 'user-round')
    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24"
        {!! $common !!} class="{{ $class }}">
        <path d="M18 21a8 8 0 0 0-16 0" />
        <circle cx="10" cy="8" r="5" />
        <path d="M22 20c0-3.37-2-6.5-4-8a5 5 0 0 0-.45-8.3" />
    </svg>
@elseif ($name === 'users-round')
    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24"
        {!! $common !!} class="{{ $class }}">
        <circle cx="12" cy="8" r="5" />
        <path d="M20 21a8 8 0 0 0-16 0" />
    </svg>
@elseif ($name === 'user-star')
    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $size }}" height="{{ $size }}"
        viewBox="0 0 24 24" {!! $common !!} class="{{ $class }}">
        <path
            d="M16.051 12.616a1 1 0 0 1 1.909.024l.737 1.452a1 1 0 0 0 .737.535l1.634.256a1 1 0 0 1 .588 1.806l-1.172 1.168a1 1 0 0 0-.282.866l.259 1.613a1 1 0 0 1-1.541 1.134l-1.465-.75a1 1 0 0 0-.912 0l-1.465.75a1 1 0 0 1-1.539-1.133l.258-1.613a1 1 0 0 0-.282-.866l-1.156-1.153a1 1 0 0 1 .572-1.822l1.633-.256a1 1 0 0 0 .737-.535z" />
        <path d="M8 15H7a4 4 0 0 0-4 4v2" />
        <circle cx="10" cy="7" r="4" />
    </svg>
@elseif ($name === 'school')
    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $size }}" height="{{ $size }}"
        viewBox="0 0 24 24" {!! $common !!} class="{{ $class }}">
        <path d="M14 21v-3a2 2 0 0 0-4 0v3" />
        <path d="M18 5v16" />
        <path d="m4 6 7.106-3.79a2 2 0 0 1 1.788 0L20 6" />
        <path d="m6 11-3.52 2.147a1 1 0 0 0-.48.854V19a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-5a1 1 0 0 0-.48-.853L18 11" />
        <path d="M6 5v16" />
        <circle cx="12" cy="9" r="2" />
    </svg>
@elseif ($name === 'calendar-check-2')
    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $size }}" height="{{ $size }}"
        viewBox="0 0 24 24" {!! $common !!} class="{{ $class }}">
        <path d="M8 2v4" />
        <path d="M16 2v4" />
        <path d="M21 14V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h8" />
        <path d="M3 10h18" />
        <path d="m16 20 2 2 4-4" />
    </svg>
@endif
