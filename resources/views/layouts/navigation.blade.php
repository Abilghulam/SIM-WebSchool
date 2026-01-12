@php
    use App\Support\Role;

    $user = auth()->user();
    $isAdminOrOperator = Role::is($user, 'admin', 'operator');
@endphp

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            {{-- LEFT --}}
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                {{-- DESKTOP MENU --}}
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

                    {{-- Dashboard (SEMUA ROLE) --}}
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>

                    {{-- ADMIN / OPERATOR --}}
                    @if ($isAdminOrOperator)
                        {{-- Dropdown: Akademik --}}
                        <x-dropdown align="left" width="56">
                            <x-slot name="trigger">
                                <button type="button"
                                    class="inline-flex items-center h-16 px-3 text-sm font-medium bg-white hover:text-gray-700
                    {{ request()->routeIs('students.*') || request()->routeIs('teachers.*') ? 'text-gray-900' : 'text-gray-500' }}">
                                    <div>Akademik</div>
                                    <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('students.index')" :active="request()->routeIs('students.*')">
                                    Siswa
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('teachers.index')" :active="request()->routeIs('teachers.*')">
                                    Guru
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>

                        {{-- Dropdown: Struktur Sekolah --}}
                        <x-dropdown align="left" width="64">
                            <x-slot name="trigger">
                                <button type="button"
                                    class="inline-flex items-center h-16 px-3 text-sm font-medium bg-white hover:text-gray-700
                    {{ request()->routeIs('school-years.*') ||
                    request()->routeIs('majors.*') ||
                    request()->routeIs('classrooms.*') ||
                    request()->routeIs('homeroom-assignments.*')
                        ? 'text-gray-900'
                        : 'text-gray-500' }}">
                                    <div>Struktur Sekolah</div>
                                    <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('school-years.index')" :active="request()->routeIs('school-years.*')">
                                    Tahun Ajaran
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('majors.index')" :active="request()->routeIs('majors.*')">
                                    Jurusan
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('classrooms.index')" :active="request()->routeIs('classrooms.*')">
                                    Kelas
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('homeroom-assignments.index')" :active="request()->routeIs('homeroom-assignments.*')">
                                    Wali Kelas
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>

                        {{-- Dropdown: Proses Akademik --}}
                        <x-dropdown align="left" width="64">
                            <x-slot name="trigger">
                                <button type="button"
                                    class="inline-flex items-center h-16 px-3 text-sm font-medium bg-white hover:text-gray-700
                    {{ request()->routeIs('enrollments.promote.*') || request()->routeIs('enrollments.promotions.*')
                        ? 'text-gray-900'
                        : 'text-gray-500' }}">
                                    <div>Proses Akademik</div>
                                    <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('enrollments.promote.index')" :active="request()->routeIs('enrollments.promote.*')">
                                    Promote Siswa
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('enrollments.promotions.index')" :active="request()->routeIs('enrollments.promotions.*')">
                                    Log Promote
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    @endif

                    {{-- SISWA KELAS SAYA (KHUSUS WALI KELAS) --}}
                    @can('viewMyClass')
                        <x-nav-link :href="route('my-class.index')" :active="request()->routeIs('my-class.*')">
                            Siswa Kelas Saya
                        </x-nav-link>
                    @endcan

                    {{-- MENU GURU (GURU + WALI KELAS) --}}
                    @if (Role::is($user, 'guru', 'wali_kelas'))
                        @if ($user->teacher_id)
                            <x-nav-link :href="route('teachers.show', $user->teacher_id)" :active="request()->routeIs('teachers.show')">
                                Profil Saya
                            </x-nav-link>
                        @endif
                    @endif
                </div>
            </div>

            {{-- RIGHT --}}
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                {{-- GLOBAL QUICK SEARCH --}}
                <div class="hidden sm:flex sm:items-center sm:me-4" x-data="globalSearch()"
                    @keydown.escape.window="open=false">
                    <div class="relative w-80">
                        <input type="text" x-model="q" @input.debounce.300ms="fetch()" @focus="open=true"
                            @keydown.enter.prevent="goToSearchPage()" placeholder="Cari siswa/guru (Nama, NIS/NIP)..."
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm" />

                        {{-- Dropdown --}}
                        <div x-show="open" x-transition @click.outside="open=false"
                            class="absolute z-50 mt-2 w-full bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                            <template x-if="loading">
                                <div class="px-4 py-3 text-sm text-gray-500">Mencari...</div>
                            </template>

                            <template x-if="!loading && q.length < 2">
                                <div class="px-4 py-3 text-sm text-gray-500">
                                    Ketik minimal 2 karakter.
                                </div>
                            </template>

                            <template x-if="!loading && q.length >= 2 && resultsEmpty()">
                                <div class="px-4 py-3 text-sm text-gray-500">
                                    Tidak ada hasil.
                                </div>
                            </template>

                            {{-- Students --}}
                            <template x-if="students.length">
                                <div class="border-t border-gray-100">
                                    <div class="px-4 pt-3 pb-2 text-xs font-semibold text-gray-500 uppercase">
                                        Siswa
                                    </div>
                                    <template x-for="item in students" :key="'s' + item.id">
                                        <a :href="item.url" class="block px-4 py-2 hover:bg-gray-50">
                                            <div class="flex items-start justify-between gap-2">
                                                <div>
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
                                                </div>
                                            </div>
                                        </a>
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
                                        <a :href="item.url" class="block px-4 py-2 hover:bg-gray-50">
                                            <div class="text-sm font-semibold text-gray-900" x-text="item.title">
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                <span x-text="item.code"></span>
                                                <template x-if="item.homeroom">
                                                    <span> • Wali: <span x-text="item.homeroom"></span></span>
                                                </template>
                                            </div>
                                        </a>
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

                <script>
                    function globalSearch() {
                        return {
                            q: '',
                            open: false,
                            loading: false,
                            students: [],
                            teachers: [],

                            async fetch() {
                                this.open = true;
                                const query = (this.q || '').trim();

                                if (query.length < 2) {
                                    this.students = [];
                                    this.teachers = [];
                                    return;
                                }

                                this.loading = true;

                                try {
                                    const res = await fetch(
                                        `{{ route('global-search.suggest') }}?q=${encodeURIComponent(query)}`, {
                                            headers: {
                                                'X-Requested-With': 'XMLHttpRequest'
                                            }
                                        }
                                    );
                                    const data = await res.json();
                                    this.students = data.students || [];
                                    this.teachers = data.teachers || [];
                                } catch (e) {
                                    this.students = [];
                                    this.teachers = [];
                                } finally {
                                    this.loading = false;
                                }
                            },

                            resultsEmpty() {
                                return this.students.length === 0 && this.teachers.length === 0;
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
                </script>

                {{-- USER DROPDOWN --}}
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white hover:text-gray-700">
                            <div>{{ Auth::user()->name }}</div>
                            <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            Profil
                        </x-dropdown-link>

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
                <button @click="open = ! open" class="p-2 rounded-md text-gray-400">
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
</nav>
