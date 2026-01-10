{{-- resources/views/teachers/show.blade.php --}}
@php
    $user = auth()->user();
    $isAdminOrOperator = in_array($user->role_label, ['admin', 'operator'], true);

    $badgeVariant = $teacher->is_active ? 'green' : 'gray';
    $badgeText = $teacher->is_active ? 'Aktif' : 'Nonaktif';
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
                    {{ $teacher->full_name }} • NIP {{ $teacher->nip }}
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

            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <x-ui.card title="Ringkasan" subtitle="Informasi singkat guru.">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <div class="text-xs text-gray-500">NIP</div>
                        <div class="mt-1 font-semibold text-gray-900">{{ $teacher->nip }}</div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Status Kepegawaian</div>
                        <div class="mt-1 font-semibold text-gray-900">{{ $teacher->employment_status ?? '-' }}</div>
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <x-ui.card title="Biodata" subtitle="Informasi pribadi guru.">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                            <div>
                                <dt class="text-xs text-gray-500">Nama Lengkap</dt>
                                <dd class="mt-1 font-semibold text-gray-900">{{ $teacher->full_name }}</dd>
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
                    <x-ui.card title="Wali Kelas" subtitle="Riwayat penugasan wali kelas (jika ada).">
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

            <x-ui.card title="Dokumen" subtitle="Berkas yang terlampir untuk guru.">
                @if ($isAdminOrOperator)
                    <div class="flex justify-end mb-4">
                        <a href="#dokumen-upload">
                            <x-ui.button>+ Upload Dokumen</x-ui.button>
                        </a>
                    </div>
                @endif

                <x-ui.table>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Jenis</th>
                            <th class="px-6 py-4 text-left font-semibold">Nama File</th>
                            <th class="px-6 py-4 text-left font-semibold">Ukuran</th>
                            <th class="px-6 py-4 text-right font-semibold">Aksi</th>
                        </tr>
                    </x-slot:head>

                    @forelse($teacher->documents as $doc)
                        @php
                            $docType = $doc->type?->name ?? ($doc->title ?? 'Dokumen');
                            $sizeKb = $doc->file_size ? number_format($doc->file_size / 1024, 0) . ' KB' : '-';
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-700">{{ $docType }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $doc->file_name ?? basename($doc->file_path) }}</td>
                            <td class="px-6 py-4 text-gray-700 whitespace-nowrap">{{ $sizeKb }}</td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                                    class="text-indigo-600 hover:text-indigo-800 font-semibold">
                                    Lihat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                Belum ada dokumen.
                            </td>
                        </tr>
                    @endforelse
                </x-ui.table>

                @if ($isAdminOrOperator)
                    <div id="dokumen-upload" class="mt-6 border border-gray-200 rounded-xl bg-gray-50 p-6">
                        <h4 class="text-base font-semibold text-gray-900">Upload Dokumen</h4>
                        <p class="text-sm text-gray-500 mt-1">PDF/JPG/PNG maksimal 5MB.</p>

                        <form method="POST" action="{{ route('teachers.documents.store', $teacher) }}"
                            enctype="multipart/form-data" class="mt-4 grid grid-cols-1 md:grid-cols-12 gap-4">
                            @csrf

                            <div class="md:col-span-4">
                                <x-ui.select label="Jenis Dokumen (opsional)" name="document_type_id">
                                    <option value="">- Pilih -</option>
                                    @foreach ($documentTypes ?? [] as $dt)
                                        <option value="{{ $dt->id }}">{{ $dt->name }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>

                            <div class="md:col-span-4">
                                <x-ui.input label="Judul (opsional)" name="title"
                                    placeholder="Contoh: SK Pengangkatan" />
                            </div>

                            <div class="md:col-span-4">
                                <x-ui.field label="File" :error="$errors->first('file')">
                                    <input type="file" name="file" class="block w-full text-sm text-gray-700"
                                        required />
                                </x-ui.field>
                            </div>

                            <div class="md:col-span-12">
                                <x-ui.button type="submit">Upload</x-ui.button>
                            </div>
                        </form>
                    </div>
                @endif
            </x-ui.card>

        </div>
    </div>
</x-app-layout>
