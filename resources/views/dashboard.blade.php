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

    $missingActiveSchoolYear = (bool) data_get($alerts, 'missingActiveSchoolYear', false);
    $studentsWithoutActiveEnrollment = (int) data_get($alerts, 'studentsWithoutActiveEnrollment', 0);
    $teachersWithoutAccount = (int) data_get($alerts, 'teachersWithoutAccount', 0);
    $mustChangePasswordCount = (int) data_get($alerts, 'mustChangePasswordCount', 0);
    $homeroomNotAssigned = (int) data_get($alerts, 'homeroomNotAssigned', 0);

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
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Dashboard</h2>
                <div class="mt-1 flex items-center gap-2 text-sm text-gray-500">
                    <span>Hai <span class="font-semibold text-gray-700">{{ $user->name }}</span>, selamat datang di
                        Sistem
                        Informasi Manajemen SMKN 9 Muaro Jambi</span>

                    <span class="text-gray-300">•</span>

                    @if (!empty($stats['activeSchoolYear']))
                        <x-ui.badge variant="green">TA Aktif: {{ $stats['activeSchoolYear'] }}</x-ui.badge>
                    @else
                        <x-ui.badge variant="amber">TA Aktif: -</x-ui.badge>
                    @endif
                </div>
            </div>

            {{-- Quick Actions (ringkas di header) --}}
            <div class="flex items-center gap-2">
                @if ($isAdminOrOperator)
                    <a href="{{ route('students.create') }}">
                        <x-ui.button variant="primary">+ Siswa</x-ui.button>
                    </a>
                    <a href="{{ route('teachers.create') }}">
                        <x-ui.button variant="secondary">+ Guru</x-ui.button>
                    </a>
                @endif

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

            {{-- STAT CARDS (klik-able) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <a href="{{ route('students.index') }}" class="block">
                    <x-ui.card title="Siswa Aktif" subtitle="Klik untuk lihat data siswa">
                        <div class="text-3xl font-bold text-gray-900">{{ (int) ($stats['students'] ?? 0) }}</div>
                    </x-ui.card>
                </a>

                <a href="{{ route('teachers.index') }}" class="block">
                    <x-ui.card title="Guru Aktif" subtitle="Klik untuk lihat data guru">
                        <div class="text-3xl font-bold text-gray-900">{{ (int) ($stats['teachers'] ?? 0) }}</div>
                    </x-ui.card>
                </a>

                <a href="{{ route('classrooms.index') }}" class="block">
                    <x-ui.card title="Jumlah Kelas" subtitle="Struktur kelas sekolah">
                        <div class="text-3xl font-bold text-gray-900">{{ (int) ($stats['classrooms'] ?? 0) }}</div>
                    </x-ui.card>
                </a>

                <a href="{{ route('school-years.index') }}" class="block">
                    <x-ui.card title="Tahun Ajaran Aktif" subtitle="Klik untuk kelola tahun ajaran">
                        <div class="text-lg font-semibold text-gray-900">
                            {{ $stats['activeSchoolYear'] ?? '-' }}
                        </div>
                    </x-ui.card>
                </a>
            </div>

            {{-- ALERTS + QUICK LINKS --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Perlu Perhatian --}}
                <x-ui.card title="Perlu Perhatian" subtitle="Hal-hal yang sebaiknya dicek admin.">
                    @if (empty($perluPerhatianVisible))
                        <div class="text-sm text-gray-600">Tidak ada alert penting. ✅</div>
                    @else
                        <div class="space-y-3">
                            @foreach ($perluPerhatianVisible as $it)
                                <div
                                    class="flex items-start justify-between gap-3 rounded-xl border border-gray-200 p-3">
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

                {{-- Aksi Cepat --}}
                <x-ui.card title="Aksi Cepat" subtitle="Shortcut untuk pekerjaan harian.">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @if ($isAdminOrOperator)
                            <a href="{{ route('students.create') }}"><x-ui.button class="w-full">+ Tambah
                                    Siswa</x-ui.button></a>
                            <a href="{{ route('teachers.create') }}"><x-ui.button variant="secondary" class="w-full">+
                                    Tambah Guru</x-ui.button></a>
                            <a href="{{ route('enrollments.promote.index') }}"><x-ui.button variant="secondary"
                                    class="w-full">Promote Siswa</x-ui.button></a>
                            <a href="{{ route('homeroom-assignments.index') }}"><x-ui.button variant="secondary"
                                    class="w-full">Atur Wali Kelas</x-ui.button></a>
                        @endif

                        @can('viewMyClass')
                            <a href="{{ route('my-class.index') }}">
                                <x-ui.button variant="secondary" class="w-full">Siswa Kelas Saya</x-ui.button>
                            </a>
                        @endcan

                        @if (Role::is($user, 'guru', 'wali_kelas') && $user->teacher_id)
                            <a href="{{ route('teachers.show', $user->teacher_id) }}">
                                <x-ui.button variant="secondary" class="w-full">Profil Saya</x-ui.button>
                            </a>
                        @endif

                        <a href="{{ route('profile.edit') }}">
                            <x-ui.button variant="ghost" class="w-full">Pengaturan Profil</x-ui.button>
                        </a>
                    </div>
                </x-ui.card>

                {{-- Ringkasan TA Aktif --}}
                <x-ui.card title="Ringkasan TA Aktif" subtitle="Snapshot kondisi akademik saat ini.">
                    <div class="space-y-3 text-sm text-gray-700">
                        <div class="flex items-center justify-between">
                            <span>Enrollment aktif (TA aktif)</span>
                            <span class="font-semibold">{{ (int) ($kpi['enrollmentsActive'] ?? 0) }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span>Kelas sudah ada wali</span>
                            <span class="font-semibold">{{ (int) ($kpi['classesWithHomeroom'] ?? 0) }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span>Kelas belum ada wali</span>
                            <span class="font-semibold">{{ $homeroomNotAssigned }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span>Siswa aktif tanpa enrollment aktif</span>
                            <span class="font-semibold">{{ $studentsWithoutActiveEnrollment }}</span>
                        </div>
                    </div>

                    @if ($missingActiveSchoolYear)
                        <div
                            class="mt-4 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-amber-700 text-sm">
                            Tidak ada Tahun Ajaran aktif. Aktifkan dulu agar proses akademik berjalan.
                        </div>
                    @endif
                </x-ui.card>
            </div>

            {{-- TOP KELAS + TERBARU --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <x-ui.card title="Top 5 Kelas" subtitle="Jumlah siswa aktif (TA aktif).">
                    @if ($topClassrooms->isEmpty())
                        <div class="text-sm text-gray-600">Belum ada data (pastikan TA aktif + enrollment aktif ada).
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($topClassrooms as $c)
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 truncate">
                                            {{ $c->name }}
                                        </div>
                                        <div class="text-xs text-gray-500 truncate">
                                            {{ $c->major?->name ?? '-' }}
                                        </div>
                                    </div>
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ (int) ($c->students_count ?? 0) }}
                                    </div>
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Siswa</div>
                            <div class="space-y-2">
                                @forelse ($recentStudents as $s)
                                    <a href="{{ route('students.show', $s) }}"
                                        class="block rounded-lg px-3 py-2 hover:bg-gray-50">
                                        <div class="text-sm font-semibold text-gray-900">{{ $s->full_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $s->nis }}</div>
                                    </a>
                                @empty
                                    <div class="text-sm text-gray-500">Belum ada data.</div>
                                @endforelse
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Guru</div>
                            <div class="space-y-2">
                                @forelse ($recentTeachers as $t)
                                    <a href="{{ route('teachers.show', $t) }}"
                                        class="block rounded-lg px-3 py-2 hover:bg-gray-50">
                                        <div class="text-sm font-semibold text-gray-900">{{ $t->full_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $t->nip }}</div>
                                    </a>
                                @empty
                                    <div class="text-sm text-gray-500">Belum ada data.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            {{-- CHARTS --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <x-ui.card title="Siswa per Jurusan" subtitle="Komposisi siswa berdasarkan jurusan.">
                    <canvas id="studentsByMajor"></canvas>
                </x-ui.card>

                <x-ui.card title="Gender Siswa" subtitle="Komposisi siswa berdasarkan gender.">
                    <canvas id="studentsByGender"></canvas>
                </x-ui.card>

                <x-ui.card title="Status Kepegawaian Guru" subtitle="Komposisi guru berdasarkan status kepegawaian."
                    class="lg:col-span-2">
                    <canvas id="teachersByEmployment"></canvas>
                </x-ui.card>
            </div>

        </div>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const makeChart = (id, type, labels, data) => {
            const el = document.getElementById(id);
            if (!el) return;

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
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
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
    </script>
</x-app-layout>
