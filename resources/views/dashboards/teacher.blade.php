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

    $accountStatusText = $user->is_active ? 'Aktif' : 'Nonaktif';
    $accountStatusVariant = $user->is_active ? 'green' : 'gray';

    $mustChangeText = $user->must_change_password ? 'Ya' : 'Tidak';
    $mustChangeVariant = $user->must_change_password ? 'amber' : 'green';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <h2 class="text-xl font-semibold text-gray-900">
                    {{ $isHomeroomMode ? 'Dashboard Wali Kelas' : 'Dashboard Guru' }}
                </h2>

                <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-gray-500">
                    <span class="min-w-0">
                        Selamat datang,
                        <span class="font-semibold text-gray-700">{{ $user->name }}</span>
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

            {{-- Quick header actions (aman untuk semua guru) --}}
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('password.change') }}">
                    <x-ui.button variant="secondary">Ganti Password</x-ui.button>
                </a>

                @if ($user->teacher_id)
                    <a href="{{ route('teachers.show', $user->teacher_id) }}">
                        <x-ui.button>Profil Saya</x-ui.button>
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-ui.flash />

            {{-- =========================
                 SHARED TOP CARDS
                 ========================= --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <x-ui.card title="Status Akun">
                    <div class="mt-1 flex items-center gap-2">
                        <x-ui.badge :variant="$accountStatusVariant">{{ $accountStatusText }}</x-ui.badge>
                        @if (!$user->is_active)
                            <span class="text-xs text-gray-500">Hubungi admin jika ini tidak sesuai.</span>
                        @endif
                    </div>
                </x-ui.card>

                <x-ui.card title="Login Terakhir">
                    <div class="text-sm font-semibold text-gray-900 truncate">
                        {{ $lastLogin ?? 'Belum pernah login' }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500">
                        {{ $lastLogin ? 'Terakhir masuk sistem.' : 'Akun baru / belum login.' }}
                    </div>
                </x-ui.card>

                <x-ui.card title="Wajib Ganti Password">
                    <div class="mt-1">
                        <x-ui.badge :variant="$mustChangeVariant">{{ $mustChangeText }}</x-ui.badge>
                    </div>
                    @if ($user->must_change_password)
                        <div class="mt-1 text-xs text-gray-500">Silakan ganti password untuk membuka akses fitur.</div>
                    @endif
                </x-ui.card>

                <x-ui.card title="Akun Terhubung">
                    <div class="text-sm font-semibold text-gray-900 break-words">
                        {{ $teacher ? 'Terhubung ke data guru' : 'Belum terhubung (teacher_id kosong)' }}
                    </div>
                    @if (!$teacher)
                        <div class="mt-1 text-xs text-gray-500">
                            Minta admin menghubungkan akun ke data guru agar fitur profil aktif.
                        </div>
                    @endif
                </x-ui.card>
            </div>

            {{-- =========================
                 HOMEROOM MODE
                 ========================= --}}
            @if ($isHomeroomMode)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <x-ui.card title="Kelas Diampu">
                        <div class="text-lg font-semibold text-gray-900 truncate">{{ $className ?? '-' }}</div>
                        @if (!$className)
                            <div class="mt-1 text-xs text-gray-500">Belum ada penugasan wali kelas.</div>
                        @endif
                    </x-ui.card>

                    <x-ui.card title="Jurusan">
                        <div class="text-base font-semibold text-gray-900 truncate">{{ $majorName ?? '-' }}</div>
                    </x-ui.card>

                    <x-ui.card title="Total Siswa Aktif">
                        <div class="text-3xl font-bold text-gray-900">{{ $studentCount }}</div>
                        <div class="mt-1 text-xs text-gray-500">Berdasarkan enrollment TA aktif.</div>
                    </x-ui.card>

                    <x-ui.card title="Aksi Cepat">
                        <div class="grid grid-cols-1 gap-2">
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

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2">
                        <x-ui.card title="Ringkasan" subtitle="Info cepat akun dan status wali kelas.">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="min-w-0">
                                    <div class="text-xs text-gray-500">Nama</div>
                                    <div class="mt-1 font-semibold text-gray-900 truncate">
                                        {{ $teacher?->full_name ?? '-' }}</div>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs text-gray-500">NIP</div>
                                    <div class="mt-1 font-semibold text-gray-900 truncate">{{ $teacher?->nip ?? '-' }}
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs text-gray-500">Telepon</div>
                                    <div class="mt-1 font-semibold text-gray-900 truncate">
                                        {{ $teacher?->phone ?? '-' }}</div>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs text-gray-500">Email</div>
                                    <div class="mt-1 font-semibold text-gray-900 truncate">
                                        {{ $teacher?->email ?? '-' }}</div>
                                </div>
                            </div>

                            @if ($profileIssues->isNotEmpty() && $teacher)
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
                            @endif
                        </x-ui.card>
                    </div>

                    <x-ui.card title="Tips" subtitle="Panduan singkat untuk wali kelas.">
                        <div class="space-y-3 text-sm text-gray-600">
                            <div class="flex gap-2">
                                <span class="mt-0.5 text-gray-400">•</span>
                                <div>Pastikan data siswa TA aktif sudah ter-enroll dengan benar.</div>
                            </div>
                            <div class="flex gap-2">
                                <span class="mt-0.5 text-gray-400">•</span>
                                <div>Lengkapi profil guru untuk memudahkan komunikasi dan validasi data.</div>
                            </div>
                            <div class="flex gap-2">
                                <span class="mt-0.5 text-gray-400">•</span>
                                <div>Gunakan menu “Siswa Kelas Saya” untuk melihat detail siswa kelas yang diampu.</div>
                            </div>
                        </div>
                    </x-ui.card>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <x-ui.card title="Gender Siswa (Kelas Saya)"
                        subtitle="Komposisi gender siswa di kelas yang diampu.">
                        <div class="relative">
                            <canvas id="studentsByGender"></canvas>
                        </div>
                    </x-ui.card>

                    <x-ui.card title="Status Siswa (Kelas Saya)"
                        subtitle="Komposisi status siswa di kelas yang diampu.">
                        <div class="relative">
                            <canvas id="studentsByStatus"></canvas>
                        </div>
                    </x-ui.card>
                </div>

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
                                <td class="px-6 py-4 font-semibold text-gray-900">
                                    <div class="truncate max-w-[260px]">{{ $s['name'] }}</div>
                                </td>
                                <td class="px-6 py-4 text-gray-700 whitespace-nowrap">{{ $s['nis'] }}</td>
                                <td class="px-6 py-4 text-gray-700 whitespace-nowrap">{{ $s['classroom'] ?? '-' }}</td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
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
                {{-- =========================
                 TEACHER MODE (NO HOMEROOM)
                 ========================= --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2">
                        <x-ui.card title="Ringkasan Profil" subtitle="Informasi penting tentang akun dan data guru.">
                            @if (!$teacher)
                                <div class="text-sm text-gray-600">
                                    Data guru belum tersedia untuk akun ini.
                                    <div class="mt-2 text-xs text-gray-500">
                                        Admin perlu mengisi <span class="font-semibold">teacher_id</span> pada akun
                                        kamu.
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div class="min-w-0">
                                        <div class="text-xs text-gray-500">Nama</div>
                                        <div class="mt-1 font-semibold text-gray-900 truncate">
                                            {{ $teacher->full_name }}</div>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs text-gray-500">NIP</div>
                                        <div class="mt-1 font-semibold text-gray-900 truncate">{{ $teacher->nip }}
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs text-gray-500">Telepon</div>
                                        <div class="mt-1 font-semibold text-gray-900 truncate">
                                            {{ $teacher->phone ?? '-' }}</div>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs text-gray-500">Email</div>
                                        <div class="mt-1 font-semibold text-gray-900 truncate">
                                            {{ $teacher->email ?? '-' }}</div>
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
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <a href="{{ route('teachers.edit', $teacher) }}">
                                                <x-ui.button size="sm" variant="secondary">Lengkapi
                                                    Profil</x-ui.button>
                                            </a>

                                            <a href="{{ route('password.change') }}">
                                                <x-ui.button size="sm" variant="secondary">Ganti
                                                    Password</x-ui.button>
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <div class="mt-5 text-sm text-gray-600">Profil sudah cukup lengkap.</div>
                                @endif
                            @endif
                        </x-ui.card>
                    </div>

                    <x-ui.card title="Aksi Cepat" subtitle="Shortcut aman untuk guru.">
                        <div class="grid grid-cols-1 gap-3">
                            <a href="{{ route('password.change') }}">
                                <x-ui.button variant="secondary" class="w-full">Ganti Password</x-ui.button>
                            </a>

                            @if ($user->teacher_id)
                                <a href="{{ route('teachers.show', $user->teacher_id) }}">
                                    <x-ui.button class="w-full">Profil Saya</x-ui.button>
                                </a>
                            @endif
                        </div>

                        <div class="mt-4 text-xs text-gray-500 leading-relaxed">
                            Jika kamu nanti ditugaskan sebagai wali kelas pada TA aktif, dashboard ini akan otomatis
                            menampilkan fitur wali kelas.
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
