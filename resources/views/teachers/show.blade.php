{{-- resources/views/teachers/show.blade.php --}}
@php
    $user = auth()->user();
    $isAdminOrOperator = in_array($user->role_label, ['admin', 'operator'], true);

    $badgeVariant = $teacher->is_active ? 'green' : 'gray';
    $badgeText = $teacher->is_active ? 'Aktif' : 'Nonaktif';

    $account = $teacher->user;

    $religionText = $teacher->religion ?? '-';
    if (($teacher->religion ?? null) === 'Lainnya') {
        $religionText = trim((string) ($teacher->religion_other ?? ''));
        $religionText = $religionText !== '' ? $religionText : 'Lainnya';
    }

    $maritalText = $teacher->marital_status ?? '-';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-semibold text-gray-900 leading-tight">Detail Guru</h2>
                    <x-ui.badge :variant="$badgeVariant">{{ $badgeText }}</x-ui.badge>
                </div>

                <p class="text-sm text-gray-500 mt-1">
                    {{ $teacher->full_name }} - <span class="font-semibold text-gray-900">{{ $teacher->nip }}</span>
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('teachers.index') }}">
                    <x-ui.button variant="secondary">← Kembali</x-ui.button>
                </a>

                @can('update', $teacher)
                    <a href="{{ route('teachers.edit', $teacher) }}">
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
            <x-ui.card title="Ringkasan" subtitle="Ringkasan informasi data guru">
                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <div>
                        <div class="text-xs text-gray-500">NIP</div>
                        <div class="mt-1 font-semibold text-gray-900">{{ $teacher->nip }}</div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Status Kepegawaian</div>
                        <div class="mt-1 font-semibold text-gray-900">{{ $teacher->employment_status ?? '-' }}</div>
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
                        <div class="mt-1 font-semibold text-gray-900">{{ $teacher->phone ?? '-' }}</div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Email</div>
                        <div class="mt-1 font-semibold text-gray-900">{{ $teacher->email ?? '-' }}</div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Akun Login --}}
            <x-ui.card title="Akun Login" subtitle="Informasi akun login guru untuk mengakses sistem">
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
                            Guru login menggunakan <span class="font-semibold">NIP</span>
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
                            <p class="text-xs text-gray-500 mt-1">Kelola aktivasi dan keamanan akun guru</p>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">

                                {{-- Toggle Active --}}
                                <div class="md:col-span-1">
                                    <div class="text-xs text-gray-500 mb-1">Status Akun</div>
                                    <form method="POST"
                                        action="{{ route('teachers.account.toggle-active', $teacher) }}"
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
                                        action="{{ route('teachers.account.force-change-password', $teacher) }}"
                                        onsubmit="return confirm('Paksa guru ganti password saat login berikutnya?')">
                                        @csrf
                                        @method('PATCH')

                                        <x-ui.button variant="secondary" type="submit" class="w-full">
                                            Paksa Ganti Password
                                        </x-ui.button>
                                    </form>

                                    <div class="text-xs text-gray-500 mt-2">
                                        Catatan: jika guru lupa password tapi belum sempat direset
                                    </div>
                                </div>

                                {{-- Reset Password Manual --}}
                                <div class="md:col-span-3">
                                    <div class="text-xs text-gray-500 mb-1">Reset Password</div>

                                    <div class="mt-2 border border-gray-200 rounded-xl p-4 bg-gray-50">
                                        <form method="POST"
                                            action="{{ route('teachers.account.reset-password', $teacher) }}"
                                            class="grid grid-cols-1 md:grid-cols-12 gap-4"
                                            onsubmit="return confirm('Reset password guru ini?')">
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
                            <h4 class="text-sm font-semibold text-gray-900">Template Informasi Akun Guru</h4>
                            <p class="text-xs text-gray-500 mt-1">Gunakan template berikut untuk menginformasikan akun
                                login guru
                            </p>

                            @php
                                $templateFormal =
                                    "Yth. Bapak/Ibu {$teacher->full_name},\n\n" .
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
                                    <textarea class="w-full rounded-lg border-gray-300 text-sm" rows="8" readonly>Halo {{ $teacher->full_name }}, akun SIM sudah dibuat ya.
Username: {{ $account->username }} (NIP)
Silakan login lalu ganti password. Terima kasih.</textarea>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Belum punya akun --}}
                        <div class="text-sm text-gray-600">
                            Guru ini belum memiliki akun login.
                        </div>

                        <form method="POST" action="{{ route('teachers.account.create', $teacher) }}"
                            class="mt-4 grid grid-cols-1 md:grid-cols-12 gap-4">
                            @csrf

                            <div class="md:col-span-6">
                                <x-ui.input label="Email (opsional)" name="email"
                                    placeholder="guru@school.local" />
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
                                <span class="font-semibold">{{ $teacher->nip }}</span>
                            </div>
                        </form>
                    @endif
                @endif
            </x-ui.card>

            {{-- Biodata + Wali Kelas --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <x-ui.card title="Biodata" subtitle="Informasi pribadi guru.">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                            <div>
                                <dt class="text-xs text-gray-500">Nama Lengkap</dt>
                                <dd class="mt-1 font-semibold text-gray-900">{{ $teacher->full_name }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs text-gray-500">NIP</dt>
                                <dd class="mt-1 font-semibold text-gray-900">{{ $teacher->nip }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs text-gray-500">Jenis Kelamin</dt>
                                <dd class="mt-1 text-gray-900">
                                    @if ($teacher->gender === 'L')
                                        Laki-laki
                                    @elseif($teacher->gender === 'P')
                                        Perempuan
                                    @else
                                        -
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs text-gray-500">Tempat, Tanggal Lahir</dt>
                                <dd class="mt-1 text-gray-900">
                                    {{ $teacher->birth_place ?? '-' }}
                                    @if ($teacher->birth_date)
                                        , {{ $teacher->birth_date->format('d-m-Y') }}
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs text-gray-500">Agama</dt>
                                <dd class="mt-1 text-gray-900">{{ $religionText }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs text-gray-500">Status Kawin</dt>
                                <dd class="mt-1 text-gray-900">{{ $maritalText }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs text-gray-500">Status Kepegawaian</dt>
                                <dd class="mt-1 text-gray-900">{{ $teacher->employment_status ?? '-' }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs text-gray-500">Telepon</dt>
                                <dd class="mt-1 text-gray-900">{{ $teacher->phone ?? '-' }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs text-gray-500">Email</dt>
                                <dd class="mt-1 text-gray-900">{{ $teacher->email ?? '-' }}</dd>
                            </div>

                            <div class="md:col-span-2">
                                <dt class="text-xs text-gray-500">Alamat</dt>
                                <dd class="mt-1 text-gray-900 whitespace-pre-line">{{ $teacher->address ?? '-' }}</dd>
                            </div>
                        </dl>
                    </x-ui.card>
                </div>

                <div>
                    <x-ui.card title="Wali Kelas" subtitle="Riwayat penugasan wali kelas">
                        <div class="space-y-3 text-sm text-gray-700">
                            @forelse($teacher->homeroomAssignments as $ha)
                                <div class="border border-gray-200 rounded-lg p-3">
                                    <div class="font-semibold text-gray-900">{{ $ha->classroom?->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Tahun Ajaran: {{ $ha->schoolYear?->name ?? '-' }}
                                    </div>
                                </div>
                            @empty
                                <div class="text-gray-500">Belum ada penugasan.</div>
                            @endforelse
                        </div>
                    </x-ui.card>
                </div>
            </div>

            {{-- Dokumen --}}
            @php
                $canManageDocs = $user->can('uploadDocument', $teacher);
            @endphp

            <x-ui.card title="Dokumen" subtitle="Pendataan dokumen pendukung guru">
                <x-ui.table>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Jenis</th>
                            <th class="px-6 py-4 text-left font-semibold">Judul / File</th>
                            <th class="px-6 py-4 text-left font-semibold">Uploader</th>
                            <th class="px-6 py-4 text-left font-semibold">Waktu</th>
                            <th class="px-6 py-4 text-left font-semibold">Ukuran</th>
                            <th class="px-6 py-4 text-right font-semibold">Aksi</th>
                        </tr>
                    </x-slot:head>

                    @forelse($teacher->documents as $doc)
                        @php
                            $docType = $doc->type?->name ?? '-';
                            $titleOrName = $doc->title ?: $doc->file_name ?? basename($doc->file_path);
                            $sizeKb = $doc->file_size ? number_format($doc->file_size / 1024, 0) . ' KB' : '-';
                            $uploadedAt = $doc->created_at ? $doc->created_at->format('d-m-Y H:i') : '-';
                            $uploaderName = $doc->uploadedBy?->name ?? '-';
                            $fileUrl = $doc->file_path ? asset('storage/' . $doc->file_path) : null;
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-700">
                                <x-ui.badge variant="gray">{{ $docType }}</x-ui.badge>
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $titleOrName }}</div>
                                @if ($doc->file_name && $doc->title)
                                    <div class="text-xs text-gray-500 mt-1">{{ $doc->file_name }}</div>
                                @endif
                                <div class="text-xs text-gray-500 mt-1">{{ $doc->mime_type ?? '-' }}</div>
                            </td>

                            <td class="px-6 py-4 text-gray-700">
                                {{ $uploaderName }}
                            </td>

                            <td class="px-6 py-4 text-gray-700 whitespace-nowrap">
                                {{ $uploadedAt }}
                            </td>

                            <td class="px-6 py-4 text-gray-700 whitespace-nowrap">
                                {{ $sizeKb }}
                            </td>

                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                @if ($fileUrl)
                                    <a href="{{ route('teachers.documents.show', [$teacher, $doc]) }}"
                                        target="_blank" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                                        Lihat
                                    </a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif

                                @if ($canManageDocs)
                                    <span class="text-gray-300 mx-2">|</span>
                                    <a href="{{ route('teachers.documents.edit', [$teacher, $doc]) }}"
                                        class="text-amber-600 hover:text-amber-800 font-semibold">
                                        Edit
                                    </a>

                                    <span class="text-gray-300 mx-2">|</span>
                                    <form method="POST"
                                        action="{{ route('teachers.documents.destroy', [$teacher, $doc]) }}"
                                        class="inline"
                                        onsubmit="return confirm('Hapus dokumen ini? File akan ikut terhapus.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-semibold">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                Belum ada dokumen.
                            </td>
                        </tr>
                    @endforelse
                </x-ui.table>

                @if ($canManageDocs)
                    <div id="dokumen-upload" class="mt-6 border border-gray-200 rounded-xl bg-gray-50 p-6">
                        <h4 class="text-base font-semibold text-gray-900">Upload Dokumen</h4>

                        <form method="POST" action="{{ route('teachers.documents.store', $teacher) }}"
                            enctype="multipart/form-data" class="mt-4 grid grid-cols-1 md:grid-cols-12 gap-4">
                            @csrf

                            <div class="md:col-span-4">
                                <x-ui.select label="Jenis Dokumen" name="document_type_id">
                                    <option value="">- Pilih -</option>
                                    @foreach ($documentTypes ?? [] as $dt)
                                        <option value="{{ $dt->id }}">{{ $dt->name }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>

                            <div class="md:col-span-4">
                                <x-ui.input label="Judul Dokumen" name="title"
                                    placeholder="Contoh: SK Pengangkatan" />
                            </div>

                            <div class="md:col-span-4">
                                <x-ui.field label="File" :error="$errors->first('file')">
                                    <input type="file" name="file" class="block w-full text-sm text-gray-700"
                                        required />
                                </x-ui.field>
                                <p class="text-sm text-gray-500 mt-1">PDF / JPG / PNG maks. 5MB</p>
                            </div>

                            <div class="md:col-span-12">
                                <x-ui.button type="submit" variant="primary">Upload</x-ui.button>
                            </div>
                        </form>
                    </div>
                @endif
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
