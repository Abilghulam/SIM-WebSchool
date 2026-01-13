@php
    $user = $account ?? auth()->user();
    $teacher = $teacher ?? null;

    $activeName = $activeSchoolYear?->name ?? null;
    $lastLogin = $user->last_login_at ? $user->last_login_at->format('d-m-Y H:i') : null;

    $className = $classroom?->name ?? null;
    $majorName = $stats['major'] ?? null;
    $studentCount = (int) ($stats['students'] ?? 0);

    $recentStudents = $recentStudents ?? collect();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Dashboard Wali Kelas</h2>
                <div class="mt-1 flex items-center gap-2 text-sm text-gray-500">
                    <span>Selamat datang, <span class="font-semibold text-gray-700">{{ $user->name }}</span></span>

                    <span class="text-gray-300">•</span>
                    @if ($activeName)
                        <x-ui.badge variant="green">TA Aktif: {{ $activeName }}</x-ui.badge>
                    @else
                        <x-ui.badge variant="amber">TA Aktif: -</x-ui.badge>
                    @endif

                    @if ($user->must_change_password)
                        <span class="text-gray-300">•</span>
                        <x-ui.badge variant="amber">Wajib ganti password</x-ui.badge>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('my-class.index') }}">
                    <x-ui.button variant="primary">Siswa Kelas Saya</x-ui.button>
                </a>

                <a href="{{ route('password.change') }}">
                    <x-ui.button variant="secondary">Ganti Password</x-ui.button>
                </a>

                @if ($user->teacher_id)
                    <a href="{{ route('teachers.show', $user->teacher_id) }}">
                        <x-ui.button variant="ghost">Profil Saya</x-ui.button>
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-ui.flash />

            {{-- TOP CARDS --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <x-ui.card title="Tahun Ajaran Aktif">
                    <div class="text-lg font-semibold text-gray-900">{{ $activeName ?? '-' }}</div>
                </x-ui.card>

                <x-ui.card title="Kelas Diampu">
                    <div class="text-lg font-semibold text-gray-900">{{ $className ?? '-' }}</div>
                    @if (!$className)
                        <div class="mt-1 text-xs text-gray-500">Belum ada penugasan wali kelas.</div>
                    @endif
                </x-ui.card>

                <x-ui.card title="Jurusan">
                    <div class="text-base font-semibold text-gray-900">{{ $majorName ?? '-' }}</div>
                </x-ui.card>

                <x-ui.card title="Total Siswa (Aktif)">
                    <div class="text-3xl font-bold text-gray-900">{{ $studentCount }}</div>
                </x-ui.card>
            </div>

            {{-- PERSONAL PANEL + CLASS SHORTCUT --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <x-ui.card title="Ringkasan Akun" subtitle="Info cepat akun dan wali kelas.">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <div class="text-xs text-gray-500">Status Akun</div>
                                <div class="mt-1">
                                    <x-ui.badge :variant="$user->is_active ? 'green' : 'gray'">
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </x-ui.badge>
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Login Terakhir</div>
                                <div class="mt-1 font-semibold text-gray-900">{{ $lastLogin ?? 'Belum pernah login' }}
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Wajib Ganti Password</div>
                                <div class="mt-1">
                                    <x-ui.badge :variant="$user->must_change_password ? 'amber' : 'green'">
                                        {{ $user->must_change_password ? 'Ya' : 'Tidak' }}
                                    </x-ui.badge>
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Profil Guru</div>
                                <div class="mt-1 font-semibold text-gray-900">
                                    {{ $teacher ? $teacher->full_name ?? '-' : '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <a href="{{ route('my-class.index') }}">
                                <x-ui.button variant="primary">Buka Siswa Kelas Saya</x-ui.button>
                            </a>
                            <a href="{{ route('password.change') }}">
                                <x-ui.button variant="secondary">Ganti Password</x-ui.button>
                            </a>
                            <a href="{{ route('profile.edit') }}">
                                <x-ui.button variant="ghost">Pengaturan Profil</x-ui.button>
                            </a>
                        </div>
                    </x-ui.card>
                </div>

                <x-ui.card title="Aksi Cepat" subtitle="Shortcut untuk wali kelas.">
                    <div class="grid grid-cols-1 gap-3">
                        <a href="{{ route('my-class.index') }}">
                            <x-ui.button class="w-full">Siswa Kelas Saya</x-ui.button>
                        </a>

                        @if ($user->teacher_id)
                            <a href="{{ route('teachers.show', $user->teacher_id) }}">
                                <x-ui.button variant="secondary" class="w-full">Profil Saya</x-ui.button>
                            </a>
                        @endif

                        <a href="{{ route('password.change') }}">
                            <x-ui.button variant="secondary" class="w-full">Ganti Password</x-ui.button>
                        </a>

                        <a href="{{ route('profile.edit') }}">
                            <x-ui.button variant="ghost" class="w-full">Pengaturan Profil</x-ui.button>
                        </a>
                    </div>
                </x-ui.card>
            </div>

            {{-- CHARTS --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <x-ui.card title="Gender Siswa (Kelas Saya)" subtitle="Komposisi gender siswa di kelas yang diampu.">
                    <canvas id="studentsByGender"></canvas>
                </x-ui.card>

                <x-ui.card title="Status Siswa (Kelas Saya)" subtitle="Komposisi status siswa di kelas yang diampu.">
                    <canvas id="studentsByStatus"></canvas>
                </x-ui.card>
            </div>

            {{-- RECENT STUDENTS --}}
            <x-ui.card title="Siswa Terbaru (Kelas Saya)" subtitle="Update terbaru siswa di kelas yang kamu ampu.">
                <x-ui.table>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Nama</th>
                            <th class="px-6 py-4 text-left font-semibold">NIS</th>
                            <th class="px-6 py-4 text-left font-semibold">Kelas</th>
                            <th class="px-6 py-4 text-right font-semibold">Aksi</th>
                        </tr>
                    </x-slot:head>

                    @forelse($recentStudents as $s)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $s['name'] }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $s['nis'] }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $s['classroom'] ?? '-' }}</td>
                            <td class="px-6 py-4 text-right">
                                <a class="text-indigo-600 hover:text-indigo-800 font-semibold"
                                    href="{{ $s['url'] }}">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada data.</td>
                        </tr>
                    @endforelse
                </x-ui.table>

                <div class="mt-4">
                    <a href="{{ route('my-class.index') }}"
                        class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                        Lihat semua siswa kelas saya →
                    </a>
                </div>
            </x-ui.card>
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
            'studentsByGender',
            'doughnut',
            @json(($studentsByGender ?? collect())->pluck('label')),
            @json(($studentsByGender ?? collect())->pluck('value'))
        );

        makeChart(
            'studentsByStatus',
            'bar',
            @json(($studentsByStatus ?? collect())->pluck('label')),
            @json(($studentsByStatus ?? collect())->pluck('value'))
        );
    </script>
</x-app-layout>
