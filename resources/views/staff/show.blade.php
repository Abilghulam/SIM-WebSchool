{{-- resources/views/staff/show.blade.php --}}
@php
    $user = auth()->user();
    $isAdminOrOperator = in_array($user->role_label, ['admin', 'operator'], true);

    $badgeVariant = $staff->is_active ? 'green' : 'gray';
    $badgeText = $staff->is_active ? 'Aktif' : 'Nonaktif';

    $account = $staff->user;

    $religionText = $staff->religion ?? '-';
    if (($staff->religion ?? null) === 'Lainnya') {
        $religionText = trim((string) ($staff->religion_other ?? ''));
        $religionText = $religionText !== '' ? $religionText : 'Lainnya';
    }

    $maritalText = $staff->marital_status ?? '-';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-semibold text-gray-900 leading-tight">Detail Tenaga Administrasi Sekolah</h2>
                    <x-ui.badge :variant="$badgeVariant">{{ $badgeText }}</x-ui.badge>
                </div>

                <p class="text-sm text-gray-500 mt-1">
                    {{ $staff->full_name }} - <span class="font-semibold text-gray-900">{{ $staff->nip }}</span>
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('staff.index') }}">
                    <x-ui.button variant="secondary">← Kembali</x-ui.button>
                </a>

                @can('update', $staff)
                    <a href="{{ route('staff.edit', $staff) }}">
                        <x-ui.button>Edit</x-ui.button>
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash --}}
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg">
                    {{ session('warning') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <div class="font-semibold">Terjadi kesalahan:</div>
                    <ul class="list-disc ms-5 mt-1 text-sm">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Ringkasan --}}
            <x-ui.card title="Ringkasan" subtitle="Informasi singkat tenaga administrasi sekolah">
                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <div>
                        <div class="text-xs text-gray-500">NIP</div>
                        <div class="mt-1 font-semibold text-gray-900">{{ $staff->nip }}</div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Status Kepegawaian</div>
                        <div class="mt-1 font-semibold text-gray-900">{{ $staff->employment_status ?? '-' }}</div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Agama</div>
                        <div class="mt-1 font-semibold text-gray-900">{{ $religionText }}</div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Status Kawin</div>
                        <div class="mt-1 font-semibold text-gray-900">{{ $maritalText }}</div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Telepon</div>
                        <div class="mt-1 font-semibold text-gray-900">{{ $staff->phone ?? '-' }}</div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Email</div>
                        <div class="mt-1 font-semibold text-gray-900">{{ $staff->email ?? '-' }}</div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Akun Login --}}
            <x-ui.card title="Akun Login"
                subtitle="Informasi akun login tenaga administrasi sekolah untuk mengakses sistem">
                @if (!$isAdminOrOperator)
                    <div class="text-sm text-gray-600">
                        Hanya admin/operator yang dapat mengelola akun login.
                    </div>
                @else
                    @if ($account)
                        {{-- Ringkasan akun --}}
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <div class="text-xs text-gray-500">Username</div>
                                <div class="mt-1 font-semibold text-gray-900">{{ $account->username }}</div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">Role</div>
                                <div class="mt-1 font-semibold text-gray-900">{{ $account->role_label }}</div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">Status Akun</div>
                                <div class="mt-1">
                                    <x-ui.badge :variant="$account->is_active ? 'green' : 'gray'">
                                        {{ $account->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </x-ui.badge>
                                </div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">Wajib Ganti Password</div>
                                <div class="mt-1">
                                    <x-ui.badge :variant="$account->must_change_password ? 'yellow' : 'green'">
                                        {{ $account->must_change_password ? 'Ya' : 'Tidak' }}
                                    </x-ui.badge>
                                </div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">Login Terakhir</div>
                                <div class="mt-1 font-semibold text-gray-900">
                                    {{ $account->last_login_at ? $account->last_login_at->format('d-m-Y H:i') : 'Belum pernah login' }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-sm text-gray-600">
                            TAS login menggunakan <span class="font-semibold">NIP</span>
                            @if ($account->must_change_password)
                                dan saat ini masih <span class="font-semibold">wajib ganti password</span> saat login
                                berikutnya
                            @else
                                dan saat ini <span class="font-semibold">tidak wajib ganti password</span> saat login
                                berikutnya
                            @endif
                        </div>

                        {{-- Kelola akun --}}
                        <div class="mt-6 border-t border-gray-200 pt-4">
                            <h4 class="text-sm font-semibold text-gray-900">Kelola Akun</h4>
                            <p class="text-xs text-gray-500 mt-1">Kelola aktivasi dan keamanan akun TAS</p>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                {{-- Toggle Active --}}
                                <div class="md:col-span-1">
                                    <div class="text-xs text-gray-500 mb-1">Status Akun</div>
                                    <form method="POST" action="{{ route('staff.account.toggle-active', $staff) }}"
                                        onsubmit="return confirm('Yakin ingin mengubah status akun ini?')">
                                        @csrf
                                        @method('PATCH')

                                        @if ($account->is_active)
                                            <x-ui.button variant="danger" type="submit" class="w-full">
                                                Nonaktifkan Akun
                                            </x-ui.button>
                                        @else
                                            <x-ui.button variant="primary" type="submit" class="w-full">
                                                Aktifkan Akun
                                            </x-ui.button>
                                        @endif
                                    </form>

                                    <div class="text-xs text-gray-500 mt-2">
                                        Catatan: akun nonaktif akan otomatis logout saat mencoba akses
                                    </div>
                                </div>

                                {{-- Force Change Password --}}
                                <div class="md:col-span-1">
                                    <div class="text-xs text-gray-500 mb-1">Keamanan</div>
                                    <form method="POST"
                                        action="{{ route('staff.account.force-change-password', $staff) }}"
                                        onsubmit="return confirm('Paksa TAS ganti password saat login berikutnya?')">
                                        @csrf
                                        @method('PATCH')

                                        <x-ui.button variant="secondary" type="submit" class="w-full">
                                            Paksa Ganti Password
                                        </x-ui.button>
                                    </form>

                                    <div class="text-xs text-gray-500 mt-2">
                                        Catatan: jika TAS lupa password tapi belum sempat direset
                                    </div>
                                </div>

                                {{-- Reset Password Manual --}}
                                <div class="md:col-span-3">
                                    <div class="text-xs text-gray-500 mb-1">Reset Password</div>
                                    <div class="mt-2 border border-gray-200 rounded-xl p-4 bg-gray-50">
                                        <form method="POST"
                                            action="{{ route('staff.account.reset-password', $staff) }}"
                                            class="grid grid-cols-1 md:grid-cols-12 gap-4"
                                            onsubmit="return confirm('Reset password TAS ini?')">
                                            @csrf
                                            @method('PUT')

                                            <div class="md:col-span-5">
                                                <x-ui.input label="Password Baru" name="new_password" type="password"
                                                    required :error="$errors->first('new_password')" />
                                            </div>

                                            <div class="md:col-span-5">
                                                <x-ui.input label="Konfirmasi Password Baru"
                                                    name="new_password_confirmation" type="password" required />
                                            </div>

                                            <div class="md:col-span-2 flex items-end">
                                                <x-ui.button type="submit" variant="primary" class="w-full">
                                                    Reset
                                                </x-ui.button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Template pesan siap copy --}}
                        <div class="mt-6 border-t border-gray-200 pt-4">
                            <h4 class="text-sm font-semibold text-gray-900">Template Informasi Akun TAS</h4>
                            <p class="text-xs text-gray-500 mt-1">
                                Gunakan template berikut untuk menginformasikan akun
                                login TAS
                            </p>

                            @php
                                $templateFormal =
                                    "Yth. Bapak/Ibu {$staff->full_name},\n\n" .
                                    "Akun Sistem Informasi Manajemen sekolah Anda sudah dibuat.\n" .
                                    "Username: {$account->username} (NIP)\n" .
                                    "Silakan login melalui aplikasi, lalu ganti password setelah berhasil masuk.\n\n" .
                                    'Terima kasih.';
                            @endphp

                            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="border border-gray-200 rounded-xl p-4 bg-white">
                                    <div class="text-xs text-gray-500 mb-2">Versi Formal</div>
                                    <textarea class="w-full rounded-lg border-gray-300 text-sm" rows="8" readonly>{{ $templateFormal }}</textarea>
                                    <div class="text-xs text-gray-500 mt-2">
                                        Catatan: untuk keamanan, password jangan dikirimkan lewat chat. Reset manual
                                        dilakukan oleh admin jika diperlukan.
                                    </div>
                                </div>

                                <div class="border border-gray-200 rounded-xl p-4 bg-white">
                                    <div class="text-xs text-gray-500 mb-2">Versi Singkat</div>
                                    <textarea class="w-full rounded-lg border-gray-300 text-sm" rows="8" readonly>Halo {{ $staff->full_name }}, akun SIM sudah dibuat ya.
Username: {{ $account->username }} (NIP)
Silakan login lalu ganti password. Terima kasih.</textarea>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Belum punya akun --}}
                        <div class="text-sm text-gray-600">
                            TAS ini belum memiliki akun login.
                        </div>

                        <form method="POST" action="{{ route('staff.account.create', $staff) }}"
                            class="mt-4 grid grid-cols-1 md:grid-cols-12 gap-4">
                            @csrf

                            <div class="md:col-span-6">
                                <x-ui.input label="Email (opsional)" name="email" placeholder="tas@school.local" />
                            </div>

                            <div class="md:col-span-3">
                                <x-ui.select label="Status Akun" name="is_active">
                                    <option value="1" selected>Aktif</option>
                                    <option value="0">Nonaktif</option>
                                </x-ui.select>
                            </div>

                            <div class="md:col-span-3 flex items-end">
                                <x-ui.button type="submit" class="w-full">
                                    Buat Akun
                                </x-ui.button>
                            </div>

                            <div class="md:col-span-12 text-xs text-gray-500">
                                Username & password awal akan menggunakan NIP
                                <span class="font-semibold">{{ $staff->nip }}</span>
                            </div>
                        </form>
                    @endif
                @endif
            </x-ui.card>

            {{-- Biodata --}}
            <x-ui.card title="Biodata" subtitle="Informasi pribadi TAS.">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-xs text-gray-500">Nama Lengkap</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $staff->full_name }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs text-gray-500">NIP</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $staff->nip }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs text-gray-500">Jenis Kelamin</dt>
                        <dd class="mt-1 text-gray-900">
                            @if ($staff->gender === 'L')
                                Laki-laki
                            @elseif($staff->gender === 'P')
                                Perempuan
                            @else
                                -
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs text-gray-500">Tempat, Tanggal Lahir</dt>
                        <dd class="mt-1 text-gray-900">
                            {{ $staff->birth_place ?? '-' }}
                            @if ($staff->birth_date)
                                , {{ $staff->birth_date->format('d-m-Y') }}
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs text-gray-500">Agama</dt>
                        <dd class="mt-1 text-gray-900">
                            @if (($staff->religion ?? null) === 'Lainnya')
                                {{ $staff->religion_other ?: 'Lainnya' }}
                            @else
                                {{ $staff->religion ?? '-' }}
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs text-gray-500">Status Kawin</dt>
                        <dd class="mt-1 text-gray-900">{{ $staff->marital_status ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs text-gray-500">Status Kepegawaian</dt>
                        <dd class="mt-1 text-gray-900">{{ $staff->employment_status ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs text-gray-500">Telepon</dt>
                        <dd class="mt-1 text-gray-900">{{ $staff->phone ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs text-gray-500">Email</dt>
                        <dd class="mt-1 text-gray-900">{{ $staff->email ?? '-' }}</dd>
                    </div>

                    <div class="md:col-span-2">
                        <dt class="text-xs text-gray-500">Alamat</dt>
                        <dd class="mt-1 text-gray-900 whitespace-pre-line">{{ $staff->address ?? '-' }}</dd>
                    </div>
                </dl>
            </x-ui.card>

        </div>
    </div>
</x-app-layout>
