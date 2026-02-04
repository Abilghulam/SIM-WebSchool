{{-- resources/views/dashboard.blade.php --}}
@php
    use App\Support\Role;

    $user = auth()->user();
    $isAdminOrOperator = Role::is($user, 'admin', 'operator');

    // safety
    $stats = $stats ?? [];
    $alerts = $alerts ?? [];
    $kpi = $kpi ?? [
        'activeYearId' => null,
        'enrollmentsActive' => 0,
        'classesWithHomeroom' => 0,
    ];

    $topClassrooms = $topClassrooms ?? collect();
    $recentStudents = $recentStudents ?? collect();
    $recentTeachers = $recentTeachers ?? collect();
    $recentStaff = $recentStaff ?? collect();

    $missingActiveSchoolYear = (bool) data_get($alerts, 'missingActiveSchoolYear', false);
    $studentsWithoutActiveEnrollment = (int) data_get($alerts, 'studentsWithoutActiveEnrollment', 0);
    $teachersWithoutAccount = (int) data_get($alerts, 'teachersWithoutAccount', 0);
    $staffWithoutAccount = (int) data_get($alerts, 'staffWithoutAccount', 0);
    $mustChangePasswordCount = (int) data_get($alerts, 'mustChangePasswordCount', 0);
    $homeroomNotAssigned = (int) data_get($alerts, 'homeroomNotAssigned', 0);

    // CTA Alerts
    $perluPerhatianItems = [
        [
            'show' => $missingActiveSchoolYear,
            'text' => 'Belum ada Tahun Ajaran yang aktif.',
            'href' => route('school-years.index'),
            'variant' => 'amber',
            'action' => 'Atur TA',
        ],
        [
            'show' => $studentsWithoutActiveEnrollment > 0,
            'text' => $studentsWithoutActiveEnrollment . ' siswa aktif belum punya enrollment aktif.',
            'href' => route('students.index', ['status' => 'aktif']),
            'variant' => 'amber',
            'action' => 'Cek Siswa',
        ],
        [
            'show' => $teachersWithoutAccount > 0,
            'text' => $teachersWithoutAccount . ' guru aktif belum punya akun login.',
            'href' => route('teachers.index'),
            'variant' => 'blue',
            'action' => 'Cek Guru',
        ],
        [
            'show' => $staffWithoutAccount > 0,
            'text' => $staffWithoutAccount . ' TAS aktif belum punya akun login.',
            'href' => route('staff.index'),
            'variant' => 'blue',
            'action' => 'Cek TAS',
        ],
        [
            'show' => $mustChangePasswordCount > 0,
            'text' => $mustChangePasswordCount . ' akun masih wajib ganti password.',
            'href' => route('teachers.index'),
            'variant' => 'gray',
            'action' => 'Review',
        ],
        [
            'show' => $homeroomNotAssigned > 0,
            'text' => $homeroomNotAssigned . ' kelas TA aktif belum punya wali kelas.',
            'href' => route('homeroom-assignments.index'),
            'variant' => 'amber',
            'action' => 'Atur Wali',
        ],
    ];

    $perluPerhatianVisible = array_values(array_filter($perluPerhatianItems, fn($i) => $i['show']));

    // Greeting helper (UI kecil biar terasa modern)
    $hour = (int) now()->format('H');
    $greet =
        $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));

    $activeSchoolYearText = $stats['activeSchoolYear'] ?? null;

    // Defaults chart
    $studentsByMajor = $studentsByMajor ?? collect();
    $studentsByGender = $studentsByGender ?? collect();
    $teachersByEmployment = $teachersByEmployment ?? collect();
    $staffByEmployment = $staffByEmployment ?? collect();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <h2 class="text-xl font-semibold text-gray-900">Dashboard</h2>

                <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-gray-500">
                    <span class="truncate">
                        {{ $greet }}, <span class="font-semibold text-gray-700">{{ $user->name }}</span>.
                        Selamat datang di Sistem Informasi Manajemen SMKN 9 Muaro Jambi
                    </span>

                    <span class="text-gray-300">•</span>

                    @if ($activeSchoolYearText)
                        <x-ui.badge variant="green">TA Aktif: {{ $activeSchoolYearText }}</x-ui.badge>
                    @else
                        <x-ui.badge variant="amber">TA Aktif: -</x-ui.badge>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @can('viewMyClass')
                    <a href="{{ route('my-class.index') }}">
                        <x-ui.button variant="secondary">Siswa Kelas Saya</x-ui.button>
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <x-ui.flash />

            {{-- STAT CARDS (ukuran konsisten + chevron sejajar value) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">

                @php
                    // kalau class navy belum ada, ganti ke indigo-* (smooth)
                    $iconWrap = 'rounded-xl bg-navy-50 text-navy-600 ring-1 ring-navy-100';
                    $subClass = 'text-[11px] leading-4 text-gray-500 line-clamp-2'; // dipaksa max 2 baris
                    $cardClass = 'min-h-[150px]'; // tweak: 140-170 sesuai selera
                    $chevClass = 'text-gray-400 group-hover:text-navy-600 transition';
                @endphp

                @php
                    $cards = [
                        [
                            'href' => route('students.index'),
                            'title' => 'Siswa Aktif',
                            'subtitle' => 'Klik untuk lihat semua data siswa',
                            'value' => (int) ($stats['students'] ?? 0),
                            'icon' => 'user-round',
                            'valueClass' => 'text-3xl',
                        ],
                        [
                            'href' => route('teachers.index'),
                            'title' => 'Guru Aktif',
                            'subtitle' => 'Klik untuk lihat semua data guru',
                            'value' => (int) ($stats['teachers'] ?? 0),
                            'icon' => 'users-round',
                            'valueClass' => 'text-3xl',
                        ],
                        [
                            'href' => route('staff.index'),
                            'title' => 'TAS Aktif',
                            'subtitle' => 'Klik untuk lihat semua data TAS',
                            'value' => (int) ($stats['staff'] ?? 0),
                            'icon' => 'user-star',
                            'valueClass' => 'text-3xl',
                        ],
                        [
                            'href' => route('classrooms.index'),
                            'title' => 'Jumlah Kelas',
                            'subtitle' => 'Klik untuk lihat struktur kelas sekolah',
                            'value' => (int) ($stats['classrooms'] ?? 0),
                            'icon' => 'school',
                            'valueClass' => 'text-3xl',
                        ],
                        [
                            'href' => route('school-years.index'),
                            'title' => 'Periode Aktif',
                            'subtitle' => 'Klik untuk kelola tahun ajaran',
                            'value' => (string) ($stats['activeSchoolYear'] ?? '-'),
                            'icon' => 'calendar-check-2',
                            'valueClass' => 'text-lg',
                        ],
                    ];
                @endphp

                @foreach ($cards as $c)
                    <a href="{{ $c['href'] }}" class="block group">
                        <x-ui.card class="{{ $cardClass }}">
                            <div class="h-full flex flex-col justify-between gap-4">

                                {{-- Header (kunci konsistensi tinggi) --}}
                                <div class="flex items-start gap-3">
                                    <span
                                        class="shrink-0 inline-flex items-center justify-center p-2 {{ $iconWrap }}">
                                        <x-ui.lucide :name="$c['icon']" :size="18" />
                                    </span>

                                    <div class="min-w-0">
                                        <div class="font-semibold text-gray-900 leading-5">
                                            {{ $c['title'] }}
                                        </div>
                                        <div class="{{ $subClass }}">
                                            {{ $c['subtitle'] }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Footer: value + chevron sejajar --}}
                                <div class="flex items-end justify-between">
                                    <div class="{{ $c['valueClass'] }} font-bold text-gray-900">
                                        {{ $c['value'] }}
                                    </div>

                                    <span
                                        class="inline-flex items-center justify-center rounded-lg border border-transparent p-1 group-hover:border-navy-100 group-hover:bg-navy-50 transition">
                                        <x-ui.lucide name="chevron-right" :size="22"
                                            class="{{ $chevClass }}" />
                                    </span>
                                </div>

                            </div>
                        </x-ui.card>
                    </a>
                @endforeach

            </div>

            {{-- HERO STRIP: KPI mini + CTA cepat (Ringkasan & Aksi Cepat vertikal) --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- KIRI: 2 card stack (atas-bawah) --}}
                <div class="lg:col-span-2 space-y-6">
                    <x-ui.card title="Ringkasan Hari Ini" subtitle="Snapshot kondisi sistem & akademik.">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="rounded-xl border border-gray-200 p-3">
                                <div class="text-xs text-gray-500">Enrollment aktif</div>
                                <div class="mt-1 text-2xl font-bold text-gray-900">
                                    {{ (int) ($kpi['enrollmentsActive'] ?? 0) }}
                                </div>
                            </div>

                            <div class="rounded-xl border border-gray-200 p-3">
                                <div class="text-xs text-gray-500">Kelas sudah wali</div>
                                <div class="mt-1 text-2xl font-bold text-gray-900">
                                    {{ (int) ($kpi['classesWithHomeroom'] ?? 0) }}
                                </div>
                            </div>

                            <div class="rounded-xl border border-gray-200 p-3">
                                <div class="text-xs text-gray-500">Kelas belum wali</div>
                                <div class="mt-1 text-2xl font-bold text-gray-900">
                                    {{ (int) $homeroomNotAssigned }}
                                </div>
                            </div>

                            <div class="rounded-xl border border-gray-200 p-3">
                                <div class="text-xs text-gray-500">Akun wajib ganti password</div>
                                <div class="mt-1 text-2xl font-bold text-gray-900">
                                    {{ (int) $mustChangePasswordCount }}
                                </div>
                            </div>
                        </div>

                        @if ($missingActiveSchoolYear)
                            <div
                                class="mt-4 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-amber-700 text-sm">
                                Tidak ada Tahun Ajaran aktif. Aktifkan dulu agar proses akademik berjalan.
                            </div>
                        @endif
                    </x-ui.card>

                    {{-- AKSI UTAMA (Icon Tiles) --}}
                    <x-ui.card title="Aksi Utama" subtitle="Pintasan kerja yang paling sering dipakai.">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @if ($isAdminOrOperator)
                                {{-- Tambah Siswa --}}
                                <a href="{{ route('students.create') }}"
                                    class="group rounded-2xl border border-gray-200 bg-white p-4 hover:bg-gray-50 transition
                       hover:shadow-sm hover:-translate-y-0.5">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="rounded-xl bg-navy-50 text-navy-600 border border-navy-100 p-2">
                                            {{-- lucide user-round --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-user-round">
                                                <circle cx="12" cy="8" r="5" />
                                                <path d="M20 21a8 8 0 0 0-16 0" />
                                            </svg>
                                        </div>

                                        <div class="text-gray-400 group-hover:text-navy-600 transition">
                                            {{-- chevron-right --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-chevron-right">
                                                <path d="m9 18 6-6-6-6" />
                                            </svg>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <div class="font-semibold text-gray-900 leading-tight">Tambah Siswa</div>
                                        <div class="text-[11px] text-gray-500 mt-1">Input data siswa baru</div>
                                    </div>
                                </a>

                                {{-- Tambah Guru --}}
                                <a href="{{ route('teachers.create') }}"
                                    class="group rounded-2xl border border-gray-200 bg-white p-4 hover:bg-gray-50 transition
                       hover:shadow-sm hover:-translate-y-0.5">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="rounded-xl bg-navy-50 text-navy-600 border border-navy-100 p-2">
                                            {{-- lucide users-round --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-users-round">
                                                <path d="M18 21a8 8 0 0 0-16 0" />
                                                <circle cx="10" cy="8" r="5" />
                                                <path d="M22 20c0-3.37-2-6.5-4-8a5 5 0 0 0-.45-8.3" />
                                            </svg>
                                        </div>

                                        <div class="text-gray-400 group-hover:text-navy-600 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-chevron-right">
                                                <path d="m9 18 6-6-6-6" />
                                            </svg>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <div class="font-semibold text-gray-900 leading-tight">Tambah Guru</div>
                                        <div class="text-[11px] text-gray-500 mt-1">Input data guru baru</div>
                                    </div>
                                </a>

                                {{-- Tambah TAS --}}
                                <a href="{{ route('staff.create') }}"
                                    class="group rounded-2xl border border-gray-200 bg-white p-4 hover:bg-gray-50 transition
                       hover:shadow-sm hover:-translate-y-0.5">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="rounded-xl bg-navy-50 text-navy-600 border border-navy-100 p-2">
                                            {{-- lucide user-star --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-user-star">
                                                <path
                                                    d="M16.051 12.616a1 1 0 0 1 1.909.024l.737 1.452a1 1 0 0 0 .737.535l1.634.256a1 1 0 0 1 .588 1.806l-1.172 1.168a1 1 0 0 0-.282.866l.259 1.613a1 1 0 0 1-1.541 1.134l-1.465-.75a1 1 0 0 0-.912 0l-1.465.75a1 1 0 0 1-1.539-1.133l.258-1.613a1 1 0 0 0-.282-.866l-1.156-1.153a1 1 0 0 1 .572-1.822l1.633-.256a1 1 0 0 0 .737-.535z" />
                                                <path d="M8 15H7a4 4 0 0 0-4 4v2" />
                                                <circle cx="10" cy="7" r="4" />
                                            </svg>
                                        </div>

                                        <div class="text-gray-400 group-hover:text-navy-600 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-chevron-right">
                                                <path d="m9 18 6-6-6-6" />
                                            </svg>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <div class="font-semibold text-gray-900 leading-tight">Tambah TAS</div>
                                        <div class="text-[11px] text-gray-500 mt-1">Input tenaga administrasi sekolah
                                        </div>
                                    </div>
                                </a>

                                {{-- Promote Siswa --}}
                                <a href="{{ route('enrollments.promote.index') }}"
                                    class="group rounded-2xl border border-gray-200 bg-white p-4 hover:bg-gray-50 transition
                       hover:shadow-sm hover:-translate-y-0.5">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="rounded-xl bg-navy-50 text-navy-600 border border-navy-100 p-2">
                                            {{-- lucide school (dipakai utk akademik/promote) --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-school">
                                                <path d="M14 21v-3a2 2 0 0 0-4 0v3" />
                                                <path d="M18 5v16" />
                                                <path d="m4 6 7.106-3.79a2 2 0 0 1 1.788 0L20 6" />
                                                <path
                                                    d="m6 11-3.52 2.147a1 1 0 0 0-.48.854V19a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-5a1 1 0 0 0-.48-.853L18 11" />
                                                <path d="M6 5v16" />
                                                <circle cx="12" cy="9" r="2" />
                                            </svg>
                                        </div>

                                        <div class="text-gray-400 group-hover:text-navy-600 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-chevron-right">
                                                <path d="m9 18 6-6-6-6" />
                                            </svg>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <div class="font-semibold text-gray-900 leading-tight">Promote Siswa</div>
                                        <div class="text-[11px] text-gray-500 mt-1">Naik kelas / pindah kelas</div>
                                    </div>
                                </a>

                                {{-- Atur Wali Kelas --}}
                                <a href="{{ route('homeroom-assignments.index') }}"
                                    class="group rounded-2xl border border-gray-200 bg-white p-4 hover:bg-gray-50 transition
                       hover:shadow-sm hover:-translate-y-0.5">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="rounded-xl bg-navy-50 text-navy-600 border border-navy-100 p-2">
                                            {{-- lucide users-round (dipakai utk penugasan) --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-users-round">
                                                <path d="M18 21a8 8 0 0 0-16 0" />
                                                <circle cx="10" cy="8" r="5" />
                                                <path d="M22 20c0-3.37-2-6.5-4-8a5 5 0 0 0-.45-8.3" />
                                            </svg>
                                        </div>

                                        <div class="text-gray-400 group-hover:text-navy-600 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-chevron-right">
                                                <path d="m9 18 6-6-6-6" />
                                            </svg>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <div class="font-semibold text-gray-900 leading-tight">Atur Wali Kelas</div>
                                        <div class="text-[11px] text-gray-500 mt-1">Penugasan wali per TA</div>
                                    </div>
                                </a>
                            @endif

                            @can('viewMyClass')
                                {{-- Siswa Kelas Saya --}}
                                <a href="{{ route('my-class.index') }}"
                                    class="group rounded-2xl border border-gray-200 bg-white p-4 hover:bg-gray-50 transition
                       hover:shadow-sm hover:-translate-y-0.5 {{ $isAdminOrOperator ? '' : 'sm:col-span-3' }}">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="rounded-xl bg-navy-50 text-navy-600 border border-navy-100 p-2">
                                            {{-- lucide school --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-school">
                                                <path d="M14 21v-3a2 2 0 0 0-4 0v3" />
                                                <path d="M18 5v16" />
                                                <path d="m4 6 7.106-3.79a2 2 0 0 1 1.788 0L20 6" />
                                                <path
                                                    d="m6 11-3.52 2.147a1 1 0 0 0-.48.854V19a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-5a1 1 0 0 0-.48-.853L18 11" />
                                                <path d="M6 5v16" />
                                                <circle cx="12" cy="9" r="2" />
                                            </svg>
                                        </div>

                                        <div class="text-gray-400 group-hover:text-navy-600 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-chevron-right">
                                                <path d="m9 18 6-6-6-6" />
                                            </svg>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <div class="font-semibold text-gray-900 leading-tight">Siswa Kelas Saya</div>
                                        <div class="text-[11px] text-gray-500 mt-1">Lihat data siswa yang kamu ampu</div>
                                    </div>
                                </a>
                            @endcan

                            @if (Role::is($user, 'guru', 'wali_kelas') && $user->teacher_id)
                                {{-- Profil Saya --}}
                                <a href="{{ route('teachers.show', $user->teacher_id) }}"
                                    class="group rounded-2xl border border-gray-200 bg-white p-4 hover:bg-gray-50 transition
                       hover:shadow-sm hover:-translate-y-0.5 sm:col-span-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="rounded-xl bg-navy-50 text-navy-600 border border-navy-100 p-2">
                                            {{-- lucide user-round --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-user-round">
                                                <circle cx="12" cy="8" r="5" />
                                                <path d="M20 21a8 8 0 0 0-16 0" />
                                            </svg>
                                        </div>

                                        <div class="text-gray-400 group-hover:text-navy-600 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-chevron-right">
                                                <path d="m9 18 6-6-6-6" />
                                            </svg>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <div class="font-semibold text-gray-900 leading-tight">Profil Saya</div>
                                        <div class="text-[11px] text-gray-500 mt-1">Lihat biodata & akun login</div>
                                    </div>
                                </a>
                            @endif
                        </div>
                    </x-ui.card>
                </div>

                {{-- KANAN: Perlu Perhatian (biarkan seperti itu) --}}
                <x-ui.card title="Perlu Perhatian" subtitle="Hal-hal yang sebaiknya dicek admin.">
                    @if (empty($perluPerhatianVisible))
                        <div class="text-sm text-gray-600">Tidak ada alert penting. ✅</div>
                    @else
                        <div class="space-y-3">
                            @foreach ($perluPerhatianVisible as $it)
                                <div
                                    class="flex items-start justify-between gap-3 rounded-xl border border-gray-200 p-3 hover:bg-gray-50 transition">
                                    <div class="min-w-0">
                                        <x-ui.badge :variant="$it['variant']">Info</x-ui.badge>
                                        <div class="mt-2 text-sm font-medium text-gray-900">
                                            {{ $it['text'] }}
                                        </div>
                                    </div>

                                    <a href="{{ $it['href'] }}" class="shrink-0">
                                        <x-ui.button size="sm"
                                            variant="secondary">{{ $it['action'] }}</x-ui.button>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-ui.card>
            </div>

            {{-- TOP KELAS + TERBARU (dengan tab interaktif) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <x-ui.card title="Top 5 Kelas" subtitle="Jumlah siswa aktif (TA aktif).">
                    @if ($topClassrooms->isEmpty())
                        <div class="text-sm text-gray-600">
                            Belum ada data (pastikan TA aktif + enrollment aktif ada).
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($topClassrooms as $c)
                                <div
                                    class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 px-4 py-3 hover:bg-gray-50 transition">
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 truncate">{{ $c->name }}
                                        </div>
                                        <div class="text-xs text-gray-500 truncate">{{ $c->major?->name ?? '-' }}
                                        </div>
                                    </div>
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ (int) ($c->students_count ?? 0) }}</div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('classrooms.index') }}"
                                class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                                Lihat semua kelas →
                            </a>
                        </div>
                    @endif
                </x-ui.card>

                <x-ui.card title="Terbaru" subtitle="Entry yang baru ditambahkan ke sistem.">
                    {{-- Tabs --}}
                    <div class="flex flex-wrap gap-2 mb-4">
                        <button type="button" data-tab="tab-students"
                            class="dash-tab px-3 py-1.5 rounded-lg text-sm font-semibold border border-gray-200 bg-gray-900 text-white">
                            Siswa
                        </button>
                        <button type="button" data-tab="tab-teachers"
                            class="dash-tab px-3 py-1.5 rounded-lg text-sm font-semibold border border-gray-200 bg-white text-gray-700 hover:bg-gray-50">
                            Guru
                        </button>
                        <button type="button" data-tab="tab-staff"
                            class="dash-tab px-3 py-1.5 rounded-lg text-sm font-semibold border border-gray-200 bg-white text-gray-700 hover:bg-gray-50">
                            TAS
                        </button>
                    </div>

                    <div id="tab-students" class="dash-tabpanel">
                        <div class="space-y-2">
                            @forelse ($recentStudents as $s)
                                <a href="{{ route('students.show', $s) }}"
                                    class="block rounded-xl border border-gray-200 px-4 py-3 hover:bg-gray-50 transition">
                                    <div class="text-sm font-semibold text-gray-900">{{ $s->full_name }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ $s->nis }}</div>
                                </a>
                            @empty
                                <div class="text-sm text-gray-500">Belum ada data.</div>
                            @endforelse
                        </div>
                    </div>

                    <div id="tab-teachers" class="dash-tabpanel hidden">
                        <div class="space-y-2">
                            @forelse ($recentTeachers as $t)
                                <a href="{{ route('teachers.show', $t) }}"
                                    class="block rounded-xl border border-gray-200 px-4 py-3 hover:bg-gray-50 transition">
                                    <div class="text-sm font-semibold text-gray-900">{{ $t->full_name }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ $t->nip }}</div>
                                </a>
                            @empty
                                <div class="text-sm text-gray-500">Belum ada data.</div>
                            @endforelse
                        </div>
                    </div>

                    <div id="tab-staff" class="dash-tabpanel hidden">
                        <div class="space-y-2">
                            @forelse ($recentStaff as $st)
                                <a href="{{ route('staff.show', $st) }}"
                                    class="block rounded-xl border border-gray-200 px-4 py-3 hover:bg-gray-50 transition">
                                    <div class="text-sm font-semibold text-gray-900">{{ $st->full_name }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ $st->nip }}</div>
                                </a>
                            @empty
                                <div class="text-sm text-gray-500">Belum ada data.</div>
                            @endforelse
                        </div>
                    </div>
                </x-ui.card>
            </div>

            {{-- CHARTS --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <x-ui.card title="Siswa per Jurusan" subtitle="Komposisi siswa berdasarkan jurusan.">
                    <div class="h-[320px]">
                        <canvas id="studentsByMajor"></canvas>
                    </div>
                </x-ui.card>

                <x-ui.card title="Gender Siswa" subtitle="Komposisi siswa berdasarkan gender.">
                    <div class="h-[320px]">
                        <canvas id="studentsByGender"></canvas>
                    </div>
                </x-ui.card>

                <x-ui.card title="Status Kepegawaian Guru" subtitle="Komposisi guru berdasarkan status kepegawaian."
                    class="lg:col-span-2">
                    <div class="h-[320px]">
                        <canvas id="teachersByEmployment"></canvas>
                    </div>
                </x-ui.card>

                <x-ui.card title="Status Kepegawaian TAS" subtitle="Komposisi TAS berdasarkan status kepegawaian."
                    class="lg:col-span-2">
                    <div class="h-[320px]">
                        <canvas id="staffByEmployment"></canvas>
                    </div>
                </x-ui.card>
            </div>

        </div>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // ---------- Tabs (Terbaru) ----------
        (function() {
            const tabs = document.querySelectorAll('.dash-tab');
            const panels = document.querySelectorAll('.dash-tabpanel');

            const setActive = (id) => {
                panels.forEach(p => p.classList.toggle('hidden', p.id !== id));

                tabs.forEach(t => {
                    const active = t.getAttribute('data-tab') === id;
                    t.classList.toggle('bg-gray-900', active);
                    t.classList.toggle('text-white', active);

                    t.classList.toggle('bg-white', !active);
                    t.classList.toggle('text-gray-700', !active);
                });
            };

            tabs.forEach(t => t.addEventListener('click', () => setActive(t.getAttribute('data-tab'))));
        })();

        // ---------- Charts ----------
        const makeChart = (id, type, labels, data) => {
            const el = document.getElementById(id);
            if (!el) return;

            // prevent empty chart crash
            labels = labels || [];
            data = data || [];

            new Chart(el, {
                type,
                data: {
                    labels,
                    datasets: [{
                        data,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            enabled: true
                        }
                    },
                    scales: (type === 'bar') ? {
                        y: {
                            beginAtZero: true
                        }
                    } : {}
                }
            });
        };

        makeChart(
            'studentsByMajor',
            'bar',
            @json(($studentsByMajor ?? collect())->pluck('label')),
            @json(($studentsByMajor ?? collect())->pluck('value'))
        );

        makeChart(
            'studentsByGender',
            'doughnut',
            @json(($studentsByGender ?? collect())->pluck('label')),
            @json(($studentsByGender ?? collect())->pluck('value'))
        );

        makeChart(
            'teachersByEmployment',
            'bar',
            @json(($teachersByEmployment ?? collect())->pluck('label')),
            @json(($teachersByEmployment ?? collect())->pluck('value'))
        );

        makeChart(
            'staffByEmployment',
            'bar',
            @json(($staffByEmployment ?? collect())->pluck('label')),
            @json(($staffByEmployment ?? collect())->pluck('value'))
        );
    </script>
</x-app-layout>
