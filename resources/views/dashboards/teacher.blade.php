@php
    $user = $account ?? auth()->user();
    $teacher = $teacher ?? null;

    $activeName = $activeSchoolYear?->name ?? null;

    $lastLogin = $user->last_login_at ? $user->last_login_at->format('d-m-Y H:i') : null;

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
                <h2 class="text-xl font-semibold text-gray-900">Dashboard Guru</h2>
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
                <a href="{{ route('password.change') }}">
                    <x-ui.button variant="secondary">Ganti Password</x-ui.button>
                </a>

                <a href="{{ route('profile.edit') }}">
                    <x-ui.button variant="ghost">Profil</x-ui.button>
                </a>

                @if ($user->teacher_id)
                    <a href="{{ route('teachers.show', $user->teacher_id) }}">
                        <x-ui.button variant="primary">Profil Saya</x-ui.button>
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-ui.flash />

            {{-- STAT CARDS --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <x-ui.card title="Status Akun">
                    <div class="mt-1">
                        <x-ui.badge :variant="$user->is_active ? 'green' : 'gray'">
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </x-ui.badge>
                    </div>
                </x-ui.card>

                <x-ui.card title="Login Terakhir">
                    <div class="text-sm font-semibold text-gray-900">
                        {{ $lastLogin ?? 'Belum pernah login' }}
                    </div>
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
                        <div class="mt-1 text-xs text-gray-500">
                            Hubungi admin untuk menghubungkan akun ke data guru.
                        </div>
                    @endif
                </x-ui.card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Profil Ringkas --}}
                <div class="lg:col-span-2">
                    <x-ui.card title="Profil Saya" subtitle="Ringkasan data guru dan akun.">
                        @if (!$teacher)
                            <div class="text-sm text-gray-600">
                                Data guru belum tersedia untuk akun ini.
                            </div>
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
                                    <div class="mt-1 font-semibold text-gray-900">{{ $teacher->phone ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Email</div>
                                    <div class="mt-1 font-semibold text-gray-900">{{ $teacher->email ?? '-' }}</div>
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
                                <div class="mt-5 text-sm text-gray-600">
                                    Profil sudah cukup lengkap. ✅
                                </div>
                            @endif
                        @endif
                    </x-ui.card>
                </div>

                {{-- Aksi Cepat --}}
                <x-ui.card title="Aksi Cepat" subtitle="Shortcut yang aman untuk guru.">
                    <div class="grid grid-cols-1 gap-3">
                        @if ($user->teacher_id)
                            <a href="{{ route('teachers.show', $user->teacher_id) }}">
                                <x-ui.button class="w-full">Profil Saya</x-ui.button>
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
        </div>
    </div>
</x-app-layout>
