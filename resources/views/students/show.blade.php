{{-- resources/views/students/show.blade.php --}}
@php
    $user = auth()->user();
    $isAdminOrOperator = in_array($user->role_label, ['admin', 'operator'], true);

    $enr = $student->activeEnrollment;
    $classroom = $enr?->classroom;
    $major = $classroom?->major;
    $schoolYear = $enr?->schoolYear;

    $status = $student->status ?? '-';
    $badgeVariant = match ($status) {
        'aktif' => 'green',
        'lulus' => 'blue',
        'pindah' => 'amber',
        'nonaktif' => 'gray',
        default => 'gray',
    };

    $kipLabel = is_null($student->is_kip) ? '-' : ($student->is_kip ? 'Ya' : 'Tidak');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-semibold text-gray-900 leading-tight">
                        Detail Siswa
                    </h2>

                    <x-ui.badge :variant="$badgeVariant">
                        {{ ucfirst($status) }}
                    </x-ui.badge>
                </div>

                <p class="text-sm text-gray-500 mt-1">
                    {{ $student->full_name }} - <span class="font-semibold text-gray-900"> {{ $student->nis }}</span>
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('students.index') }}">
                    <x-ui.button variant="secondary">← Kembali</x-ui.button>
                </a>

                @if ($isAdminOrOperator)
                    <a href="{{ route('students.edit', $student) }}">
                        <x-ui.button variant="primary">Edit</x-ui.button>
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash message --}}
            <x-ui.flash />

            {{-- Ringkasan kelas aktif --}}
            <x-ui.card title="Ringkasan" subtitle="Informasi kelas dan tahun ajaran aktif siswa">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <div class="text-xs text-gray-500">Tahun Ajaran</div>
                        <div class="mt-1 font-semibold text-gray-900">
                            {{ $schoolYear?->name ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Kelas</div>
                        <div class="mt-1 font-semibold text-gray-900">
                            {{ $classroom?->name ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Jurusan</div>
                        <div class="mt-1 font-semibold text-gray-900">
                            {{ $major?->name ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Tahun Masuk</div>
                        <div class="mt-1 font-semibold text-gray-900">
                            {{ $student->entry_year ?? '-' }}
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Grid utama --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Biodata --}}
                <div class="lg:col-span-2">
                    <x-ui.card title="Biodata" subtitle="Informasi pribadi siswa.">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                            <div>
                                <dt class="text-xs text-gray-500">Nama Lengkap</dt>
                                <dd class="mt-1 font-semibold text-gray-900">{{ $student->full_name }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs text-gray-500">NIS</dt>
                                <dd class="mt-1 font-semibold text-gray-900">{{ $student->nis }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs text-gray-500">NISN</dt>
                                <dd class="mt-1 font-semibold text-gray-900">{{ $student->nisn ?? '-' }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs text-gray-500">NIK</dt>
                                <dd class="mt-1 text-gray-900">{{ $student->nik ?? '-' }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs text-gray-500">Jenis Kelamin</dt>
                                <dd class="mt-1 text-gray-900">
                                    @if ($student->gender === 'L')
                                        Laki-laki
                                    @elseif($student->gender === 'P')
                                        Perempuan
                                    @else
                                        -
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs text-gray-500">Tempat, Tanggal Lahir</dt>
                                <dd class="mt-1 text-gray-900">
                                    {{ $student->birth_place ?? '-' }}
                                    @if ($student->birth_date)
                                        , {{ $student->birth_date->format('d-m-Y') }}
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs text-gray-500">Agama</dt>
                                <dd class="mt-1 text-gray-900">{{ $student->religion ?? '-' }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs text-gray-500">Telepon</dt>
                                <dd class="mt-1 text-gray-900">{{ $student->phone ?? '-' }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs text-gray-500">Email</dt>
                                <dd class="mt-1 text-gray-900">{{ $student->email ?? '-' }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs text-gray-500">Asal Sekolah</dt>
                                <dd class="mt-1 text-gray-900">{{ $student->origin_school ?? '-' }}</dd>
                            </div>

                            <div class="md:col-span-2">
                                <dt class="text-xs text-gray-500">Alamat</dt>
                                <dd class="mt-1 text-gray-900 whitespace-pre-line">{{ $student->address ?? '-' }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs text-gray-500">Status KIP</dt>
                                <dd class="mt-1 text-gray-900">{{ $kipLabel }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs text-gray-500">Nomor KIP</dt>
                                <dd class="mt-1 text-gray-900">{{ $student->kip_number ?? '-' }}</dd>
                            </div>
                        </dl>
                    </x-ui.card>
                </div>

                {{-- Orang Tua --}}
                <div>
                    <x-ui.card title="Orang Tua" subtitle="Data orang tua siswa.">
                        <div class="space-y-4">
                            <div>
                                <div class="text-xs text-gray-500">Nama Ayah</div>
                                <div class="mt-1 text-gray-900">{{ $student->father_name ?? '-' }}</div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">Pekerjaan Ayah</div>
                                <div class="mt-1 text-gray-900">{{ $student->father_job ?? '-' }}</div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">Nama Ibu</div>
                                <div class="mt-1 text-gray-900">{{ $student->mother_name ?? '-' }}</div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">Pekerjaan Ibu</div>
                                <div class="mt-1 text-gray-900">{{ $student->mother_job ?? '-' }}</div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">Telepon Orang Tua</div>
                                <div class="mt-1 text-gray-900">{{ $student->parent_phone ?? '-' }}</div>
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            </div>

            {{-- Riwayat Kelas --}}
            <x-ui.card title="Riwayat Kelas" subtitle="Riwayat kelas siswa per tahun ajaran.">
                <x-ui.table>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Tahun Ajaran</th>
                            <th class="px-6 py-4 text-left font-semibold">Kelas</th>
                            <th class="px-6 py-4 text-left font-semibold">Jurusan</th>
                            <th class="px-6 py-4 text-left font-semibold">Aktif</th>
                            <th class="px-6 py-4 text-left font-semibold">Catatan</th>
                        </tr>
                    </x-slot:head>

                    @forelse($student->enrollments->sortByDesc(fn($x) => $x->schoolYear?->name) as $enrollment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-700 whitespace-nowrap">
                                {{ $enrollment->schoolYear?->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-gray-700 whitespace-nowrap">
                                {{ $enrollment->classroom?->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-gray-700">
                                {{ $enrollment->classroom?->major?->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($enrollment->is_active)
                                    <x-ui.badge variant="green">Ya</x-ui.badge>
                                @else
                                    <x-ui.badge variant="gray">Tidak</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-700">
                                {{ $enrollment->note ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                Belum ada riwayat kelas.
                            </td>
                        </tr>
                    @endforelse
                </x-ui.table>
            </x-ui.card>

            {{-- Dokumen --}}
            @php
                $canManageDocs = $user->can('uploadDocument', $student);
            @endphp

            <x-ui.card title="Dokumen" subtitle="Pendataan dokumen pendukung siswa">
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

                    @forelse($student->documents as $doc)
                        @php
                            $docType = $doc->type?->name ?? '-';
                            $titleOrName = $doc->title ?: $doc->file_name ?? basename($doc->file_path);
                            $sizeKb = $doc->file_size ? number_format($doc->file_size / 1024, 0) . ' KB' : '-';
                            $uploadedAt = $doc->created_at ? $doc->created_at->format('d-m-Y H:i') : '-';
                            $uploaderName = $doc->uploadedBy?->name ?? '-';
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
                                <a href="{{ route('students.documents.show', [$student, $doc]) }}" target="_blank"
                                    class="text-indigo-600 hover:text-indigo-800 font-semibold">
                                    Lihat
                                </a>

                                @if ($canManageDocs)
                                    <span class="text-gray-300 mx-2">|</span>
                                    <a href="{{ route('students.documents.edit', [$student, $doc]) }}"
                                        class="text-amber-600 hover:text-amber-800 font-semibold">
                                        Edit
                                    </a>

                                    <span class="text-gray-300 mx-2">|</span>
                                    <form method="POST"
                                        action="{{ route('students.documents.destroy', [$student, $doc]) }}"
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
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h4 class="text-base font-semibold text-gray-900">Upload Dokumen</h4>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('students.documents.store', $student) }}"
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
                                    placeholder="Contoh: Surat Keterangan Lulus" />
                            </div>

                            <div class="md:col-span-4">
                                <x-ui.field label="File" :error="$errors->first('file')">
                                    <input type="file" name="file" class="block w-full text-sm text-gray-700"
                                        required />
                                </x-ui.field>

                                <p class="text-sm text-gray-500 mt-1">PDF / JPG / PNG maks. 5MB</p>
                            </div>

                            <div class="md:col-span-12 flex items-center gap-2 pt-1">
                                <x-ui.button type="submit" variant="primary">Upload</x-ui.button>
                            </div>
                        </form>
                    </div>
                @endif
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
