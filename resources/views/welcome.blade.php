<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SMKN 9 Muaro Jambi') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/logo.ico') }}">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            body {
                font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;
            }
        </style>
    @endif

    <style>
        :root {
            /* TEXT: tetap terang tapi tidak "pucat" */
            --navy: rgba(222, 235, 248, 0.96);
            --navy-soft: rgba(222, 235, 248, 0.72);
            --muted: rgba(170, 185, 205, 0.92);

            /* ACCENT: biru muda ke abu -> dibuat lebih terang */
            --skygray: rgba(140, 178, 205, 0.34);
            --skygray-2: rgba(175, 205, 228, 0.52);

            /* LINES */
            --line: rgba(222, 235, 248, 0.14);

            /* SURFACES: body tidak terlalu hitam, tetap navy-ish */
            --bg: rgba(6, 14, 28, 1);
            /* navy dark (lebih berkarakter) */
            --header: rgba(10, 22, 40, 0.82);
            /* sedikit lebih terang dari body */
            --card: rgba(12, 22, 38, 0.86);
            /* card jelas, tidak terlalu gelap */
        }

        * {
            -webkit-tap-highlight-color: transparent;
        }
    </style>
</head>

<body class="min-h-screen text-slate-100" style="background: var(--bg);">
    <!-- Header -->
    <header class="sticky top-0 z-30 border-b"
        style="border-color: var(--line); background: var(--header); backdrop-filter: blur(10px);">
        <!-- subtle accent line -->
        <div class="h-[2px] w-full"
            style="background: linear-gradient(90deg, transparent, var(--skygray-2), transparent);"></div>

        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <!-- Brand -->
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 overflow-hidden rounded-2xl border"
                        style="border-color: var(--line); background: rgba(15, 30, 55, 0.55);">
                        <!-- SIMPAN LOGO DI: public/assets/images/logo.png -->
                        <img src="{{ asset('assets/images/logo.png') }}" alt="Logo SMKN 9 Muaro Jambi"
                            class="h-full w-full object-contain p-1"
                            onerror="this.style.display='none'; this.parentElement.classList.add('flex','items-center','justify-center'); this.parentElement.innerHTML='<span class=\'text-[10px] font-semibold text-slate-400\'>LOGO</span>';">
                    </div>

                    <div class="leading-tight">
                        <div class="text-sm font-bold tracking-tight" style="color: var(--navy);">
                            SMKN 9 Muaro Jambi
                        </div>
                        <div class="text-xs" style="color: var(--muted);">
                            Sistem Informasi Manajemen Sekolah
                        </div>
                    </div>
                </div>

                <!-- Auth -->
                @if (Route::has('login'))
                    <nav class="flex items-center gap-2">
                        @auth
                            <a href="{{ url('/dashboard') }}"
                                class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white hover:opacity-95"
                                style="background: linear-gradient(135deg, rgba(35,90,135,.95), rgba(70,130,175,.92)); box-shadow: 0 8px 18px -14px rgba(140,178,205,.35);">
                                Dashboard
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                                class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold hover:opacity-95"
                                style="border-color: var(--line); color: var(--navy); background: rgba(15,30,55,0.32);">
                                Masuk
                            </a>
                        @endauth
                    </nav>
                @endif
            </div>
        </div>
    </header>

    <main class="relative">
        <!-- Background: subtle tint + pattern (lebih terang, tetap minimal) -->
        <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
            <div class="absolute inset-0"
                style="background:
                    radial-gradient(circle at 18% 12%, rgba(175,205,228,.10), transparent 60%),
                    radial-gradient(circle at 86% 72%, rgba(140,178,205,.10), transparent 62%);">
            </div>

            <svg class="absolute inset-0 h-full w-full opacity-[0.09]" aria-hidden="true">
                <defs>
                    <pattern id="gridDots" width="56" height="56" patternUnits="userSpaceOnUse">
                        <path d="M56 0H0V56" fill="none" stroke="rgba(222,235,248,0.06)" stroke-width="1" />
                        <circle cx="28" cy="28" r="1.1" fill="rgba(175,205,228,0.08)" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#gridDots)"></rect>
            </svg>
        </div>

        <!-- Center card + footer outside card -->
        <section class="mx-auto flex min-h-[calc(100vh-4rem)] max-w-6xl items-center px-4 py-10 sm:px-6 lg:px-8">
            <div class="w-full">
                <!-- Card -->
                <div class="mx-auto max-w-3xl rounded-3xl border p-8"
                    style="border-color: var(--line); background: var(--card); box-shadow: 0 10px 22px -18px rgba(140,178,205,0.22);">
                    <!-- Accent strip -->
                    <div class="-mt-2 mb-6 h-[2px] w-full rounded-full"
                        style="background: linear-gradient(90deg, transparent, rgba(175,205,228,.35), rgba(140,178,205,.28), transparent);">
                    </div>

                    <div class="max-w-xl">
                        <div class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold"
                            style="border-color: var(--line); background: rgba(15,30,55,0.28); color: var(--navy-soft);">
                            <span class="h-2 w-2 rounded-full" style="background: rgba(175,205,228,.55);"></span>
                            Portal Resmi
                        </div>

                        <h1 class="mt-4 text-2xl font-bold tracking-tight sm:text-3xl" style="color: var(--navy);">
                            Sistem Informasi Manajemen<br class="hidden sm:block"> SMKN 9 Muaro Jambi
                        </h1>

                        <p class="mt-3 text-sm" style="color: var(--muted);">
                            Akses cepat untuk pengelolaan data akademik, administrasi, dan struktur sekolah dengan
                            tampilan modern dan institusional.
                        </p>

                        <!-- CTA -->
                        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                            @if (Route::has('login'))
                                @auth
                                    <a href="{{ url('/dashboard') }}"
                                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold text-white hover:opacity-95 sm:w-auto"
                                        style="background: linear-gradient(135deg, rgba(35,90,135,.95), rgba(70,130,175,.92)); box-shadow: 0 10px 20px -16px rgba(140,178,205,.30);">
                                        Buka Dashboard
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                            <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </a>
                                @else
                                    <a href="{{ route('login') }}"
                                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold text-white hover:opacity-95 sm:w-auto"
                                        style="background: linear-gradient(135deg, rgba(35,90,135,.95), rgba(70,130,175,.92)); box-shadow: 0 10px 20px -16px rgba(140,178,205,.30);">
                                        Masuk ke Sistem
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                            <path d="M10 17l5-5-5-5" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </a>
                                @endauth
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Footer OUTSIDE card -->
                <div class="mx-auto mt-6 max-w-3xl">
                    <div class="h-[2px] w-full rounded-full"
                        style="background: linear-gradient(90deg, transparent, rgba(222,235,248,.10), rgba(175,205,228,.22), transparent);">
                    </div>

                    <div class="mt-4 flex items-center justify-between text-xs" style="color: var(--muted);">
                        <div>© {{ date('Y') }} SMKN 9 Muaro Jambi</div>
                        <div class="hidden sm:block">All right reserved</div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>

</html>
