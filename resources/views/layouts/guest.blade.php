<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SMKN 9 Muaro Jambi') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            /* Selaras dengan welcome (dark-institusional) */
            --navy: rgba(222, 235, 248, 0.96);
            --navy-soft: rgba(222, 235, 248, 0.72);
            --muted: rgba(170, 185, 205, 0.92);

            /* biru muda ke abu (lebih terang) */
            --skygray: rgba(140, 178, 205, 0.34);
            --skygray-2: rgba(175, 205, 228, 0.52);

            --line: rgba(222, 235, 248, 0.14);

            /* Body */
            --bg: rgba(6, 14, 28, 1);

            /* Card matte */
            --card: rgba(12, 22, 38, 0.86);
        }

        * {
            -webkit-tap-highlight-color: transparent;
        }
    </style>
</head>

<body class="min-h-screen text-slate-100" style="background: var(--bg);">
    <!-- Background subtle (minimal overlay) -->
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute inset-0"
            style="background:
                radial-gradient(circle at 18% 12%, rgba(175,205,228,.10), transparent 60%),
                radial-gradient(circle at 86% 72%, rgba(140,178,205,.10), transparent 62%);">
        </div>

        <svg class="absolute inset-0 h-full w-full opacity-[0.09]" aria-hidden="true">
            <defs>
                <pattern id="gridDotsGuest" width="56" height="56" patternUnits="userSpaceOnUse">
                    <path d="M56 0H0V56" fill="none" stroke="rgba(222,235,248,0.06)" stroke-width="1" />
                    <circle cx="28" cy="28" r="1.1" fill="rgba(175,205,228,0.08)" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#gridDotsGuest)"></rect>
        </svg>
    </div>

    <!-- Content -->
    <main class="mx-auto flex min-h-screen max-w-6xl items-center px-4 py-10 sm:px-6 lg:px-8">
        <div class="w-full">
            <!-- Card wrapper (semua halaman auth konsisten) -->
            <div class="mx-auto max-w-md rounded-3xl border p-8"
                style="border-color: var(--line); background: var(--card); box-shadow: 0 10px 22px -18px rgba(140,178,205,0.22);">
                <!-- Accent strip -->
                <div class="-mt-2 mb-6 h-[2px] w-full rounded-full"
                    style="background: linear-gradient(90deg, transparent, rgba(175,205,228,.35), rgba(140,178,205,.28), transparent);">
                </div>

                {{ $slot }}
            </div>

            <!-- Footer outside card -->
            <div class="mx-auto mt-6 max-w-md">
                <div class="h-[2px] w-full rounded-full"
                    style="background: linear-gradient(90deg, transparent, rgba(222,235,248,.10), rgba(175,205,228,.22), transparent);">
                </div>

                <div class="mt-4 flex items-center justify-between text-xs" style="color: var(--muted);">
                    <div>© {{ date('Y') }} SMKN 9 Muaro Jambi</div>
                    <div class="hidden sm:block">SIM Sekolah • Institusional</div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>
