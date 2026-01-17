@php
    $user = $account ?? auth()->user();
    $teacher = $teacher ?? null;

    $activeName = $activeSchoolYear?->name ?? null;
    $lastLogin = $user->last_login_at ? $user->last_login_at->format('d-m-Y H:i') : null;

    $isHomeroomMode = (bool) ($isHomeroomMode ?? false);

    // Homeroom stats (safe)
    $className = $classroom?->name ?? null;
    $majorName = data_get($stats ?? [], 'major');
    $studentCount = (int) data_get($stats ?? [], 'students', 0);

    $recentStudents = $recentStudents ?? collect();

    // profile checks (safe)
    $missingPhone = (bool) data_get($profileChecks ?? [], 'missingPhone', false);
    $missingEmail = (bool) data_get($profileChecks ?? [], 'missingEmail', false);
    $missingAddress = (bool) data_get($profileChecks ?? [], 'missingAddress', false);

    $profileIssues = collect([
        $missingPhone ? 'Telepon belum diisi' : null,
        $missingEmail ? 'Email belum diisi' : null,
        $missingAddress ? 'Alamat belum diisi' : null,
    ])
        ->filter()
        ->values();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">
                    {{ $isHomeroomMode ? 'Dashboard Wali Kelas' : 'Dashboard Guru' }}
                </h2>

                <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-gray-500">
                    <span>
                        Selamat datang, <span class="font-semibold text-gray-700">{{ $user->name }}</span>
                    </span>

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

            <div class="flex items-center gap-2"></div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-ui.flash />

            @if ($isHomeroomMode)
                {{-- TOP CARDS (HOMEROOM) --}}
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

                {{-- RINGKASAN + AKSI CEPAT --}}
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
                                    <div class="mt-1 font-semibold text-gray-900">
                                        {{ $lastLogin ?? 'Belum pernah login' }}
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
                                        {{ $teacher?->full_name ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </x-ui.card>
                    </div>

                    <x-ui.card title="Aksi Cepat" subtitle="Shortcut untuk wali kelas.">
                        <div class="grid grid-cols-2 gap-3">
                            @can('viewMyClass')
                                <a href="{{ route('my-class.index') }}">
                                    <x-ui.button class="w-full">Siswa Kelas Saya</x-ui.button>
                                </a>
                            @endcan

                            @if ($user->teacher_id)
                                <a href="{{ route('teachers.show', $user->teacher_id) }}">
                                    <x-ui.button variant="secondary" class="w-full">Profil Saya</x-ui.button>
                                </a>
                            @endif
                        </div>
                    </x-ui.card>
                </div>

                {{-- CHARTS --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <x-ui.card title="Gender Siswa (Kelas Saya)"
                        subtitle="Komposisi gender siswa di kelas yang diampu.">
                        <canvas id="studentsByGender"></canvas>
                    </x-ui.card>

                    <x-ui.card title="Status Siswa (Kelas Saya)"
                        subtitle="Komposisi status siswa di kelas yang diampu.">
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

                    @can('viewMyClass')
                        <div class="mt-4">
                            <a href="{{ route('my-class.index') }}"
                                class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                                Lihat semua siswa kelas saya →
                            </a>
                        </div>
                    @endcan
                </x-ui.card>
            @else
                {{-- FALLBACK MODE GURU BIASA (aman, tanpa 403) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <x-ui.card title="Status Akun">
                        <div class="mt-1">
                            <x-ui.badge :variant="$user->is_active ? 'green' : 'gray'">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </x-ui.badge>
                        </div>
                    </x-ui.card>

                    <x-ui.card title="Login Terakhir">
                        <div class="text-sm font-semibold text-gray-900">{{ $lastLogin ?? 'Belum pernah login' }}</div>
                    </x-ui.card>

                    <x-ui.card title="Wajib Ganti Password">
                        <div class="mt-1">
                            <x-ui.badge :variant="$user->must_change_password ? 'amber' : 'green'">
                                {{ $user->must_change_password ? 'Ya' : 'Tidak' }}
                            </x-ui.badge>
                        </div>
                    </x-ui.card>

                    <x-ui.card title="Akun Terhubung">
                        <div class="text-sm font-semibold text-gray-900">
                            {{ $teacher ? 'Terhubung ke data guru' : 'Belum terhubung (teacher_id kosong)' }}
                        </div>
                        @if (!$teacher)
                            <div class="mt-1 text-xs text-gray-500">Hubungi admin untuk menghubungkan akun ke data guru.
                            </div>
                        @endif
                    </x-ui.card>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2">
                        <x-ui.card title="Profil Saya" subtitle="Ringkasan data guru dan akun.">
                            @if (!$teacher)
                                <div class="text-sm text-gray-600">Data guru belum tersedia untuk akun ini.</div>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div>
                                        <div class="text-xs text-gray-500">Nama</div>
                                        <div class="mt-1 font-semibold text-gray-900">{{ $teacher->full_name }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">NIP</div>
                                        <div class="mt-1 font-semibold text-gray-900">{{ $teacher->nip }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Telepon</div>
                                        <div class="mt-1 font-semibold text-gray-900">{{ $teacher->phone ?? '-' }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Email</div>
                                        <div class="mt-1 font-semibold text-gray-900">{{ $teacher->email ?? '-' }}
                                        </div>
                                    </div>
                                </div>

                                @if ($profileIssues->isNotEmpty())
                                    <div class="mt-5 border border-amber-200 bg-amber-50 rounded-xl p-4">
                                        <div class="text-sm font-semibold text-amber-900">Perlu dilengkapi</div>
                                        <ul class="mt-2 text-sm text-amber-900 list-disc list-inside">
                                            @foreach ($profileIssues as $it)
                                                <li>{{ $it }}</li>
                                            @endforeach
                                        </ul>
                                        <div class="mt-3">
                                            <a href="{{ route('teachers.edit', $teacher) }}">
                                                <x-ui.button size="sm" variant="secondary">Lengkapi
                                                    Profil</x-ui.button>
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <div class="mt-5 text-sm text-gray-600">Profil sudah cukup lengkap. ✅</div>
                                @endif
                            @endif
                        </x-ui.card>
                    </div>

                    <x-ui.card title="Aksi Cepat" subtitle="Shortcut yang aman untuk guru.">
                        <div class="grid grid-cols-1 gap-3">
                            @if ($user->teacher_id)
                                <a href="{{ route('teachers.show', $user->teacher_id) }}">
                                    <x-ui.button class="w-full">Profil Saya</x-ui.button>
                                </a>
                            @endif
                        </div>
                    </x-ui.card>
                </div>
            @endif
        </div>
    </div>

    @if ($isHomeroomMode)
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
    @endif
</x-app-layout>
