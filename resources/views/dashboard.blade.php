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

            {{-- STAT CARDS (klik-able, modern hover) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                <a href="{{ route('students.index') }}" class="block group">
                    <x-ui.card title="Siswa Aktif" subtitle="Klik untuk lihat data siswa">
                        <div class="flex items-end justify-between gap-3">
                            <div class="text-3xl font-bold text-gray-900">{{ (int) ($stats['students'] ?? 0) }}</div>
                            <div
                                class="text-xs text-gray-500 group-hover:text-gray-700 transition inline-flex items-center gap-1">
                                <span>Lihat</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right">
                                    <path d="m9 18 6-6-6-6" />
                                </svg>
                            </div>
                        </div>
                    </x-ui.card>
                </a>

                <a href="{{ route('teachers.index') }}" class="block group">
                    <x-ui.card title="Guru Aktif" subtitle="Klik untuk lihat data guru">
                        <div class="flex items-end justify-between gap-3">
                            <div class="text-3xl font-bold text-gray-900">{{ (int) ($stats['teachers'] ?? 0) }}</div>
                            <div
                                class="text-xs text-gray-500 group-hover:text-gray-700 transition inline-flex items-center gap-1">
                                <span>Lihat</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right">
                                    <path d="m9 18 6-6-6-6" />
                                </svg>
                            </div>
                        </div>
                    </x-ui.card>
                </a>

                <a href="{{ route('staff.index') }}" class="block group">
                    <x-ui.card title="TAS Aktif" subtitle="Klik untuk lihat data TAS">
                        <div class="flex items-end justify-between gap-3">
                            <div class="text-3xl font-bold text-gray-900">{{ (int) ($stats['staff'] ?? 0) }}</div>
                            <div
                                class="text-xs text-gray-500 group-hover:text-gray-700 transition inline-flex items-center gap-1">
                                <span>Lihat</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right">
                                    <path d="m9 18 6-6-6-6" />
                                </svg>
                            </div>
                        </div>
                    </x-ui.card>
                </a>

                <a href="{{ route('classrooms.index') }}" class="block group">
                    <x-ui.card title="Jumlah Kelas" subtitle="Struktur kelas sekolah">
                        <div class="flex items-end justify-between gap-3">
                            <div class="text-3xl font-bold text-gray-900">{{ (int) ($stats['classrooms'] ?? 0) }}</div>
                            <div
                                class="text-xs text-gray-500 group-hover:text-gray-700 transition inline-flex items-center gap-1">
                                <span>Lihat</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right">
                                    <path d="m9 18 6-6-6-6" />
                                </svg>
                            </div>
                        </div>
                    </x-ui.card>
                </a>

                <a href="{{ route('school-years.index') }}" class="block group">
                    <x-ui.card title="Tahun Ajaran Aktif" subtitle="Kelola tahun ajaran">
                        <div class="flex items-end justify-between gap-3">
                            <div class="text-lg font-semibold text-gray-900">
                                {{ $stats['activeSchoolYear'] ?? '-' }}
                            </div>
                            <div
                                class="text-xs text-gray-500 group-hover:text-gray-700 transition inline-flex items-center gap-1">
                                <span>Kelola</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right">
                                    <path d="m9 18 6-6-6-6" />
                                </svg>
                            </div>
                        </div>
                    </x-ui.card>
                </a>
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

                    <x-ui.card title="Aksi Cepat" subtitle="Shortcut untuk pekerjaan harian.">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @if ($isAdminOrOperator)
                                <a href="{{ route('students.create') }}">
                                    <x-ui.button class="w-full">+ Tambah Siswa</x-ui.button>
                                </a>

                                <a href="{{ route('teachers.create') }}">
                                    <x-ui.button variant="secondary" class="w-full">+ Tambah Guru</x-ui.button>
                                </a>

                                <a href="{{ route('staff.create') }}">
                                    <x-ui.button variant="secondary" class="w-full">+ Tambah TAS</x-ui.button>
                                </a>

                                <a href="{{ route('enrollments.promote.index') }}">
                                    <x-ui.button variant="secondary" class="w-full">Promote Siswa</x-ui.button>
                                </a>

                                <a href="{{ route('homeroom-assignments.index') }}" class="sm:col-span-2">
                                    <x-ui.button variant="secondary" class="w-full">Atur Wali Kelas</x-ui.button>
                                </a>
                            @endif

                            @can('viewMyClass')
                                <a href="{{ route('my-class.index') }}"
                                    class="{{ $isAdminOrOperator ? '' : 'sm:col-span-2' }}">
                                    <x-ui.button variant="secondary" class="w-full">Siswa Kelas Saya</x-ui.button>
                                </a>
                            @endcan

                            @if (Role::is($user, 'guru', 'wali_kelas') && $user->teacher_id)
                                <a href="{{ route('teachers.show', $user->teacher_id) }}" class="sm:col-span-2">
                                    <x-ui.button variant="secondary" class="w-full">Profil Saya</x-ui.button>
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
