@php
    use App\Support\Role;

    $user = auth()->user();
    $isAdminOrOperator = Role::is($user, 'admin', 'operator');

    // Notification (safety)
    $navUnreadCount = $navUnreadCount ?? 0;
    $navNotifications = $navNotifications ?? collect();

    $navNotifItems = $navNotifications
        ->map(function ($n) {
            return [
                'id' => $n->id,
                'read_at' => $n->read_at,
                'data' => $n->data,
                'created_at' => optional($n->created_at)->format('d-m-Y H:i'),
            ];
        })
        ->values();
@endphp

<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b"
    style="border-color: rgba(255,255,255,0.08); background: var(--nav-surface); backdrop-filter: blur(10px);">

    {{-- Accent line --}}
    <div class="h-[2px] w-full" style="background: linear-gradient(90deg, transparent, var(--skygray-2), transparent);">
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            {{-- LEFT --}}
            <div class="flex items-center">
                {{-- Brand (logo only) --}}
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center">
                        <div class="h-10 w-10 overflow-hidden rounded-2xl" style="background: rgba(255,255,255,0.06);">
                            <img src="{{ asset('assets/images/logo.png') }}" alt="Logo SMKN 9 Muaro Jambi"
                                class="h-full w-full object-contain p-1"
                                onerror="this.style.display='none'; this.parentElement.classList.add('flex','items-center','justify-center'); this.parentElement.innerHTML='<span class=\'text-[10px] font-semibold text-slate-300\'>LOGO</span>';">
                        </div>
                    </a>
                </div>

                {{-- DESKTOP MENU --}}
                <div class="hidden sm:flex sm:ms-10 sm:items-center sm:gap-1">
                    {{-- Dashboard --}}
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>

                    @if ($isAdminOrOperator)
                        {{-- Dropdown: Akademik --}}
                        <x-dropdown align="left" width="56" contentClasses="py-1 bg-white">
                            <x-slot name="trigger">
                                <button type="button"
                                    class="inline-flex items-center px-3 py-2 text-sm font-semibold rounded-lg hover:opacity-95"
                                    style="color: var(--navy-soft);">
                                    <div>Data Akademik</div>
                                    <svg class="ml-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('students.index')">Siswa</x-dropdown-link>
                                <x-dropdown-link :href="route('teachers.index')">Guru</x-dropdown-link>
                                <x-dropdown-link :href="route('staff.index')">TAS</x-dropdown-link>
                                <x-dropdown-link :href="route('imports.students.create')">Import Siswa</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>

                        {{-- Dropdown: Struktur Sekolah --}}
                        <x-dropdown align="left" width="64" contentClasses="py-1 bg-white">
                            <x-slot name="trigger">
                                <button type="button"
                                    class="inline-flex items-center px-3 py-2 text-sm font-semibold rounded-lg hover:opacity-95"
                                    style="color: var(--navy-soft);">
                                    <div>Struktur Sekolah</div>
                                    <svg class="ml-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('school-years.index')">Tahun Ajaran</x-dropdown-link>
                                <x-dropdown-link :href="route('majors.index')">Jurusan</x-dropdown-link>
                                <x-dropdown-link :href="route('classrooms.index')">Kelas</x-dropdown-link>
                                <x-dropdown-link :href="route('homeroom-assignments.index')">Wali Kelas</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>

                        {{-- Dropdown: Proses Akademik --}}
                        <x-dropdown align="left" width="64" contentClasses="py-1 bg-white">
                            <x-slot name="trigger">
                                <button type="button"
                                    class="inline-flex items-center px-3 py-2 text-sm font-semibold rounded-lg hover:opacity-95"
                                    style="color: var(--navy-soft);">
                                    <div>Proses Akademik</div>
                                    <svg class="ml-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('enrollments.promote.index')">Promote Siswa</x-dropdown-link>
                                <x-dropdown-link :href="route('enrollments.promotions.index')">Log Promote</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    @endif

                    @can('viewMyClass')
                        <x-nav-link :href="route('my-class.index')" :active="request()->routeIs('my-class.*')">
                            Siswa Kelas Saya
                        </x-nav-link>
                    @endcan

                    @if (Role::is($user, 'guru', 'wali_kelas') && $user->teacher_id)
                        <x-nav-link :href="route('teachers.show', $user->teacher_id)" :active="request()->routeIs('teachers.show')">
                            Profil Saya
                        </x-nav-link>
                    @endif
                </div>
            </div>

            {{-- RIGHT --}}
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-2">
                {{-- GLOBAL QUICK SEARCH --}}
                <div class="hidden sm:flex sm:items-center sm:me-2" x-data="globalSearch()"
                    @keydown.escape.window="open=false">
                    <div class="relative w-80">
                        <input type="text" x-model="q" @input.debounce.300ms="fetch()" @focus="open=true"
                            @keydown.enter.prevent="goToSearchPage()"
                            placeholder="Cari siswa/guru/TAS (Nama, NIS/NIP)..."
                            class="w-full rounded-lg text-sm focus:ring-0"
                            style="background: rgba(255,255,255,0.10); border: 1px solid rgba(255,255,255,0.12); color: rgba(255,255,255,0.92);" />

                        {{-- Dropdown --}}
                        <div x-show="open" x-transition @click.outside="open=false"
                            class="absolute z-50 mt-2 w-full bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                            <template x-if="loading">
                                <div class="px-4 py-3 text-sm text-gray-500">Mencari...</div>
                            </template>

                            <template x-if="!loading && q.length < 2">
                                <div class="px-4 py-3 text-sm text-gray-500">Ketik minimal 2 karakter.</div>
                            </template>

                            <template x-if="!loading && q.length >= 2 && resultsEmpty()">
                                <div class="px-4 py-3 text-sm text-gray-500">Tidak ada hasil.</div>
                            </template>

                            {{-- Students --}}
                            <template x-if="students.length">
                                <div class="border-t border-gray-100">
                                    <div class="px-4 pt-3 pb-2 text-xs font-semibold text-gray-500 uppercase">
                                        Siswa
                                    </div>

                                    <template x-for="item in students" :key="'s' + item.id">
                                        <div>
                                            <!-- clickable -->
                                            <template x-if="item.url">
                                                <a :href="item.url" class="block px-4 py-2 hover:bg-gray-50">
                                                    <div class="text-sm font-semibold text-gray-900"
                                                        x-text="item.title"></div>
                                                    <div class="text-xs text-gray-500">
                                                        <span x-text="item.code"></span>
                                                        <template x-if="item.classroom">
                                                            <span> • <span x-text="item.classroom"></span></span>
                                                        </template>
                                                        <template x-if="item.major">
                                                            <span> • <span x-text="item.major"></span></span>
                                                        </template>
                                                    </div>
                                                </a>
                                            </template>

                                            <!-- non-clickable -->
                                            <template x-if="!item.url">
                                                <div
                                                    class="block px-4 py-2 bg-gray-50/40 cursor-not-allowed opacity-70">
                                                    <div class="text-sm font-semibold text-gray-900"
                                                        x-text="item.title"></div>
                                                    <div class="text-xs text-gray-500">
                                                        <span x-text="item.code"></span>
                                                        <span class="text-gray-400"> • Tidak punya akses detail</span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- Teachers --}}
                            <template x-if="teachers.length">
                                <div class="border-t border-gray-100">
                                    <div class="px-4 pt-3 pb-2 text-xs font-semibold text-gray-500 uppercase">
                                        Guru
                                    </div>

                                    <template x-for="item in teachers" :key="'t' + item.id">
                                        <div>
                                            <!-- clickable -->
                                            <template x-if="item.url">
                                                <a :href="item.url" class="block px-4 py-2 hover:bg-gray-50">
                                                    <div class="text-sm font-semibold text-gray-900"
                                                        x-text="item.title"></div>
                                                    <div class="text-xs text-gray-500">
                                                        <span x-text="item.code"></span>
                                                        <template x-if="item.homeroom">
                                                            <span> • Wali: <span x-text="item.homeroom"></span></span>
                                                        </template>
                                                    </div>
                                                </a>
                                            </template>

                                            <!-- non-clickable -->
                                            <template x-if="!item.url">
                                                <div
                                                    class="block px-4 py-2 bg-gray-50/40 cursor-not-allowed opacity-70">
                                                    <div class="text-sm font-semibold text-gray-900"
                                                        x-text="item.title"></div>
                                                    <div class="text-xs text-gray-500">
                                                        <span x-text="item.code"></span>
                                                        <span class="text-gray-400"> • Tidak punya akses detail</span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- Staff/TAS --}}
                            <template x-if="staff.length">
                                <div class="border-t border-gray-100">
                                    <div class="px-4 pt-3 pb-2 text-xs font-semibold text-gray-500 uppercase">
                                        Tenaga Administrasi Sekolah
                                    </div>

                                    <template x-for="item in staff" :key="'st' + item.id">
                                        <div>
                                            <template x-if="item.url">
                                                <a :href="item.url" class="block px-4 py-2 hover:bg-gray-50">
                                                    <div class="text-sm font-semibold text-gray-900"
                                                        x-text="item.title"></div>
                                                    <div class="text-xs text-gray-500">
                                                        <span x-text="item.code"></span>
                                                    </div>
                                                </a>
                                            </template>

                                            <template x-if="!item.url">
                                                <div
                                                    class="block px-4 py-2 bg-gray-50/40 cursor-not-allowed opacity-70">
                                                    <div class="text-sm font-semibold text-gray-900"
                                                        x-text="item.title"></div>
                                                    <div class="text-xs text-gray-500">
                                                        <span x-text="item.code"></span>
                                                        <span class="text-gray-400"> • Tidak punya akses detail</span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <div
                                class="border-t border-gray-200 bg-gray-50 px-4 py-2 flex items-center justify-between">
                                <div class="text-xs text-gray-500">Enter untuk lihat semua</div>
                                <a :href="searchUrl()"
                                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                                    Lihat semua →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- NOTIFICATION CENTER --}}
                <div class="me-1" x-data="notificationBell({ unread: {{ (int) $navUnreadCount }}, items: @js($navNotifItems) })" x-init="init()">
                    <x-dropdown align="right" width="72" contentClasses="py-1 bg-white">
                        <x-slot name="trigger">
                            <button type="button"
                                class="relative inline-flex items-center p-2 rounded-lg hover:bg-white/10"
                                style="color: rgba(255,255,255,0.88);">
                                <svg class="w-[20px] h-[20px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 5.365V3m0 2.365a5.338 5.338 0 0 1 5.133 5.368v1.8c0 2.386 1.867 2.982 1.867 4.175 0 .593 0 1.292-.538 1.292H5.538C5 18 5 17.301 5 16.708c0-1.193 1.867-1.789 1.867-4.175v-1.8A5.338 5.338 0 0 1 12 5.365ZM8.733 18c.094.852.306 1.54.944 2.112a3.48 3.48 0 0 0 4.646 0c.638-.572 1.236-1.26 1.33-2.112h-6.92Z" />
                                </svg>

                                <template x-if="unreadCount > 0">
                                    <span
                                        class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[11px] font-semibold flex items-center justify-center"
                                        x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
                                </template>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-4 py-2 flex items-center justify-between">
                                <div class="text-sm font-semibold text-gray-900">Notifikasi</div>
                                <a href="{{ route('notifications.index') }}"
                                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                                    Lihat semua →
                                </a>
                            </div>

                            <div class="border-t border-gray-100"></div>

                            <template x-if="items.length === 0">
                                <div class="px-4 py-3 text-sm text-gray-500">Belum ada notifikasi.</div>
                            </template>

                            <template x-if="items.length > 0">
                                <div class="max-h-96 overflow-auto">
                                    <template x-for="n in items" :key="n.id">
                                        <a :href="readUrl(n.id)" class="block px-4 py-3 hover:bg-gray-50"
                                            @click="markOneOptimistic(n)">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded-lg border text-xs font-semibold"
                                                        :class="badgeClass(n.data?.level || 'gray')"
                                                        x-text="n.data?.group || 'Info'"></span>

                                                    <template x-if="!n.read_at">
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded-lg border text-xs font-semibold bg-amber-50 text-amber-700 border-amber-200">
                                                            Baru
                                                        </span>
                                                    </template>
                                                </div>

                                                <div class="mt-1 text-sm font-semibold text-gray-900 truncate"
                                                    x-text="n.data?.title || 'Notifikasi'"></div>

                                                <div class="mt-1 text-xs text-gray-500 line-clamp-2"
                                                    x-text="n.data?.message || ''"></div>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </template>

                            <div class="border-t border-gray-100"></div>

                            <form method="POST" action="{{ route('notifications.mark-all-read') }}"
                                class="px-4 py-2" @submit="markAllOptimistic()">
                                @csrf
                                <x-ui.button type="submit" variant="secondary" class="w-full">Tandai semua
                                    dibaca</x-ui.button>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>

                {{-- USER DROPDOWN --}}
                @php
                    $u = auth()->user();
                    $photoUrl = $u?->profilePhotoUrl();

                    // fallback warna inline (anti Tailwind purge untuk class dinamis)
                    $seed = (string) ($u->email ?? ($u->username ?? ($u->id ?? 'user')));
                    $hash = crc32($seed);
                    $palette = [
                        '#475569',
                        '#4b5563',
                        '#52525b',
                        '#57534e',
                        '#dc2626',
                        '#ea580c',
                        '#d97706',
                        '#ca8a04',
                        '#65a30d',
                        '#16a34a',
                        '#059669',
                        '#0d9488',
                        '#0891b2',
                        '#0284c7',
                        '#2563eb',
                        '#4f46e5',
                        '#7c3aed',
                        '#9333ea',
                        '#c026d3',
                        '#db2777',
                        '#e11d48',
                    ];
                    $bg = $palette[$hash % count($palette)];
                @endphp

                <x-dropdown align="right" width="48" contentClasses="py-1 bg-white">
                    <x-slot name="trigger">
                        <button type="button" class="inline-flex items-center px-2 py-2 rounded-lg hover:bg-white/10"
                            style="color: rgba(255,255,255,0.88);">
                            <div class="flex items-center gap-2">
                                @if ($photoUrl)
                                    <img src="{{ $photoUrl }}" alt="Foto Profil"
                                        class="h-8 w-8 rounded-full object-cover ring-1 ring-white/20 shrink-0">
                                @else
                                    <div class="h-8 w-8 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0 leading-none"
                                        style="background-color: {{ $bg }};" title="{{ $u->name }}">
                                        {{ $u->avatarInitials() }}
                                    </div>
                                @endif

                                {{-- caret --}}
                                <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"
                                    aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>

                        @if (auth()->user()?->isAdmin())
                            <x-dropdown-link :href="route('activity-logs.index')">Activity Log</x-dropdown-link>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Logout
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- HAMBURGER --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = !open"
                    class="inline-flex items-center justify-center p-2 rounded-md hover:bg-white/10"
                    style="color: rgba(255,255,255,0.85);">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- RESPONSIVE MENU --}}
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden border-t"
        style="border-color: rgba(255,255,255,0.08); background: var(--nav-surface);">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Dashboard
            </x-responsive-nav-link>

            @if ($isAdminOrOperator)
                <div class="px-4 pt-2 text-xs font-semibold uppercase" style="color: rgba(255,255,255,0.70);">
                    Akademik
                </div>
                <x-responsive-nav-link :href="route('students.index')" :active="request()->routeIs('students.*')">
                    Siswa
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('teachers.index')" :active="request()->routeIs('teachers.*')">
                    Guru
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('staff.index')" :active="request()->routeIs('staff.*')">
                    TAS
                </x-responsive-nav-link>

                <div class="px-4 pt-4 text-xs font-semibold uppercase" style="color: rgba(255,255,255,0.70);">
                    Struktur Sekolah
                </div>
                <x-responsive-nav-link :href="route('school-years.index')" :active="request()->routeIs('school-years.*')">
                    Tahun Ajaran
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('majors.index')" :active="request()->routeIs('majors.*')">
                    Jurusan
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('classrooms.index')" :active="request()->routeIs('classrooms.*')">
                    Kelas
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('homeroom-assignments.index')" :active="request()->routeIs('homeroom-assignments.*')">
                    Wali Kelas
                </x-responsive-nav-link>

                <div class="px-4 pt-4 text-xs font-semibold uppercase" style="color: rgba(255,255,255,0.70);">
                    Proses Akademik
                </div>
                <x-responsive-nav-link :href="route('enrollments.promote.index')" :active="request()->routeIs('enrollments.promote.*')">
                    Promote Siswa
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('enrollments.promotions.index')" :active="request()->routeIs('enrollments.promotions.*')">
                    Log Promote
                </x-responsive-nav-link>
            @endif

            @can('viewMyClass')
                <x-responsive-nav-link :href="route('my-class.index')" :active="request()->routeIs('my-class.*')">
                    Siswa Kelas Saya
                </x-responsive-nav-link>
            @endcan

            @if (Role::is($user, 'guru', 'wali_kelas') && $user->teacher_id)
                <x-responsive-nav-link :href="route('teachers.show', $user->teacher_id)" :active="request()->routeIs('teachers.show')">
                    Profil Saya
                </x-responsive-nav-link>
            @endif
        </div>

        <div class="pt-4 pb-1 border-t" style="border-color: rgba(255,255,255,0.08);">
            <div class="px-4">
                <div class="font-medium text-base text-white">{{ $user->name }}</div>
                <div class="font-medium text-sm" style="color: rgba(255,255,255,0.70);">{{ $user->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    Profil
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        Logout
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script>
        // tetap sama persis (punyamu)
        function globalSearch() {
            return {
                q: '',
                open: false,
                loading: false,
                students: [],
                teachers: [],
                staff: [],

                async fetch() {
                    this.open = true;
                    const query = (this.q || '').trim();

                    if (query.length < 2) {
                        this.students = [];
                        this.teachers = [];
                        this.staff = [];
                        return;
                    }

                    this.loading = true;

                    try {
                        const res = await fetch(
                            `{{ route('global-search.suggest') }}?q=${encodeURIComponent(query)}`, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                        const data = await res.json();
                        this.students = data.students || [];
                        this.teachers = data.teachers || [];
                        this.staff = data.staff || [];
                    } catch (e) {
                        this.students = [];
                        this.teachers = [];
                        this.staff = [];
                    } finally {
                        this.loading = false;
                    }
                },

                resultsEmpty() {
                    return this.students.length === 0 && this.teachers.length === 0 && this.staff.length === 0;
                },

                searchUrl() {
                    const query = (this.q || '').trim();
                    return `{{ route('global-search.index') }}?q=${encodeURIComponent(query)}`;
                },

                goToSearchPage() {
                    window.location.href = this.searchUrl();
                }
            }
        }

        function notificationBell({
            unread,
            items
        }) {
            return {
                unreadCount: unread || 0,
                items: items || [],

                init() {
                    if (window.Echo) {
                        window.Echo.private(`App.Models.User.{{ auth()->id() }}`)
                            .notification((data) => {
                                const id = data.id || ('rt-' + Date.now());

                                const newItem = {
                                    id: id,
                                    read_at: null,
                                    data: data.data ? data.data : data,
                                    created_at: null,
                                };

                                this.items = [newItem, ...this.items].slice(0, 8);
                                this.unreadCount = this.unreadCount + 1;
                            });
                    }
                },

                readUrl(id) {
                    return `{{ url('/notifications') }}/${id}/read`;
                },

                markOneOptimistic(n) {
                    if (!n.read_at && this.unreadCount > 0) {
                        n.read_at = 'now';
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    }
                },

                markAllOptimistic() {
                    this.unreadCount = 0;
                    this.items = this.items.map(i => ({
                        ...i,
                        read_at: i.read_at ?? 'now'
                    }));
                },

                badgeClass(level) {
                    const map = {
                        gray: 'bg-gray-50 text-gray-700 border-gray-200',
                        green: 'bg-green-50 text-green-700 border-green-200',
                        blue: 'bg-blue-50 text-blue-700 border-blue-200',
                        amber: 'bg-amber-50 text-amber-700 border-amber-200',
                        red: 'bg-red-50 text-red-700 border-red-200',
                        yellow: 'bg-yellow-50 text-yellow-700 border-yellow-200',
                        orange: 'bg-orange-50 text-orange-700 border-orange-200',
                        info: 'bg-gray-50 text-gray-700 border-gray-200',
                    };
                    return map[level] || map.gray;
                },
            }
        }
    </script>
</nav>
