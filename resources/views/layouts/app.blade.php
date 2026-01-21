<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SIM | SMKN 9 Muaro Jambi') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo.ico') }}" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/ui/loading.js'])

    <style>
        :root {
            /* Tema hanya untuk NAV + aksen */
            --navy: rgba(222, 235, 248, 0.96);
            --navy-soft: rgba(222, 235, 248, 0.72);
            --muted: rgba(170, 185, 205, 0.92);

            --skygray: rgba(140, 178, 205, 0.34);
            --skygray-2: rgba(175, 205, 228, 0.52);

            --line: rgba(222, 235, 248, 0.14);

            /* NAV surface */
            --nav-surface: rgba(10, 22, 40, 0.92);
            --nav-surface-soft: rgba(10, 22, 40, 0.82);
        }

        * {
            -webkit-tap-highlight-color: transparent;
        }
    </style>
</head>

<body class="font-sans antialiased text-gray-900 bg-gray-100">
    <div class="min-h-screen">
        @include('layouts.navigation')

        {{-- Page Heading (tetap putih seperti awal) --}}
        @isset($header)
            <header class="bg-white border-b border-gray-200">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        {{-- Page Content --}}
        <main data-loading-scope class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>
    </div>
</body>

</html>
