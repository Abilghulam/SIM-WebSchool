@php use App\Support\Role; @endphp

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
                    @if (Role::is(auth()->user(), 'admin', 'operator'))
                        <x-nav-link :href="route('students.index')" :active="request()->routeIs('students.*')">
                            Siswa
                        </x-nav-link>

                        <x-nav-link :href="route('teachers.index')" :active="request()->routeIs('teachers.*')">
                            Guru
                        </x-nav-link>

                        <x-nav-link :href="route('majors.index')" :active="request()->routeIs('majors.*')">
                            Jurusan
                        </x-nav-link>

                        <x-nav-link :href="route('school-years.index')" :active="request()->routeIs('school-years.*')">
                            Tahun Ajaran
                        </x-nav-link>

                        <x-nav-link :href="route('classrooms.index')" :active="request()->routeIs('classrooms.*')">
                            Kelas
                        </x-nav-link>

                        <x-nav-link :href="route('homeroom-assignments.index')" :active="request()->routeIs('homeroom-assignments.*')">
                            Wali Kelas
                        </x-nav-link>
                    @endif

                    {{-- SISWA KELAS SAYA (KHUSUS WALI KELAS) --}}
                    @can('viewMyClass')
                        <x-nav-link :href="route('my-class.index')" :active="request()->routeIs('my-class.*')">
                            Siswa Kelas Saya
                        </x-nav-link>
                    @endcan

                    {{-- MENU GURU (GURU + WALI KELAS) --}}
                    @if (Role::is(auth()->user(), 'guru', 'wali_kelas'))
                        @if (auth()->user()->teacher_id)
                            <x-nav-link :href="route('teachers.show', auth()->user()->teacher_id)" :active="request()->routeIs('teachers.show')">
                                Profil Saya
                            </x-nav-link>
                        @endif
                    @endif
                </div>
            </div>

            {{-- RIGHT DROPDOWN --}}
            <div class="hidden sm:flex sm:items-center sm:ms-6">
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
