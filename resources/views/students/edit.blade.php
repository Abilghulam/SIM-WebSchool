{{-- resources/views/students/edit.blade.php --}}
@php
    $statusOptions = [
        'aktif' => 'Aktif',
        'lulus' => 'Lulus',
        'pindah' => 'Pindah',
        'nonaktif' => 'Nonaktif',
    ];

    $enr = $student->activeEnrollment;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">
                    Edit Siswa
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $student->full_name }} • NIS {{ $student->nis }}
                </p>
            </div>

            <a href="{{ route('students.show', $student) }}">
                <x-ui.button variant="secondary">← Kembali</x-ui.button>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Error summary --}}
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <div class="font-semibold">Terdapat kesalahan input:</div>
                    <ul class="list-disc ms-5 mt-2 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('students.update', $student) }}" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- BIODATA --}}
                <x-ui.card title="Biodata" subtitle="Perbarui data utama siswa.">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.input label="NIS" name="nis" required value="{{ old('nis', $student->nis) }}"
                            :error="$errors->first('nis')" />

                        <x-ui.input label="Nama Lengkap" name="full_name" required
                            value="{{ old('full_name', $student->full_name) }}" :error="$errors->first('full_name')" />

                        <x-ui.select label="Jenis Kelamin" name="gender">
                            <option value="">- Pilih -</option>
                            <option value="L" @selected(old('gender', $student->gender) === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('gender', $student->gender) === 'P')>Perempuan</option>
                        </x-ui.select>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.input label="Tempat Lahir" name="birth_place"
                                value="{{ old('birth_place', $student->birth_place) }}" />
                            <x-ui.input label="Tanggal Lahir" name="birth_date" type="date"
                                value="{{ old('birth_date', optional($student->birth_date)->format('Y-m-d')) }}" />
                        </div>

                        <x-ui.input label="Agama" name="religion" value="{{ old('religion', $student->religion) }}" />
                        <x-ui.input label="Telepon" name="phone" value="{{ old('phone', $student->phone) }}" />
                        <x-ui.input label="Email" name="email" type="email"
                            value="{{ old('email', $student->email) }}" />

                        <div class="md:col-span-2">
                            <x-ui.textarea label="Alamat" name="address"
                                rows="3">{{ old('address', $student->address) }}</x-ui.textarea>
                        </div>
                    </div>
                </x-ui.card>

                {{-- ORANG TUA / WALI --}}
                <x-ui.card title="Orang Tua / Wali" subtitle="Perbarui data keluarga.">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.input label="Nama Ayah" name="father_name"
                            value="{{ old('father_name', $student->father_name) }}" />
                        <x-ui.input label="Nama Ibu" name="mother_name"
                            value="{{ old('mother_name', $student->mother_name) }}" />
                        <x-ui.input label="Nama Wali" name="guardian_name"
                            value="{{ old('guardian_name', $student->guardian_name) }}" />
                        <x-ui.input label="Telepon Orang Tua/Wali" name="parent_phone"
                            value="{{ old('parent_phone', $student->parent_phone) }}" />
                    </div>
                </x-ui.card>

                {{-- STATUS + ENROLLMENT AKTIF --}}
                <x-ui.card title="Status & Kelas (Enrollment Aktif)"
                    subtitle="Mengubah tahun ajaran/kelas akan memindahkan enrollment aktif.">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.select label="Status" name="status">
                            @foreach ($statusOptions as $val => $label)
                                <option value="{{ $val }}" @selected(old('status', $student->status ?? 'aktif') === $val)>{{ $label }}
                                </option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.input label="Tahun Masuk" name="entry_year" type="number"
                            value="{{ old('entry_year', $student->entry_year) }}" />

                        <x-ui.select label="Tahun Ajaran" name="school_year_id" :error="$errors->first('school_year_id')">
                            <option value="">- Pilih -</option>
                            @foreach ($schoolYears as $sy)
                                @php
                                    $selectedSyId = old('school_year_id', $enr?->school_year_id);
                                @endphp
                                <option value="{{ $sy->id }}" @selected((string) $selectedSyId === (string) $sy->id)>
                                    {{ $sy->name }} @if ($sy->is_active)
                                        (Aktif)
                                    @endif
                                </option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.select label="Kelas" name="classroom_id" :error="$errors->first('classroom_id')">
                            <option value="">- Pilih -</option>
                            @foreach ($classrooms as $c)
                                @php
                                    $selectedClassId = old('classroom_id', $enr?->classroom_id);
                                @endphp
                                <option value="{{ $c->id }}" @selected((string) $selectedClassId === (string) $c->id)>
                                    {{ $c->name }} {{ $c->major?->name ? '• ' . $c->major->name : '' }}
                                </option>
                            @endforeach
                        </x-ui.select>

                        <div class="md:col-span-2">
                            <x-ui.input label="Catatan Enrollment (opsional)" name="enrollment_note"
                                placeholder="Catatan mutasi/pindahan/dll"
                                value="{{ old('enrollment_note', $enr?->note) }}" :error="$errors->first('enrollment_note')" />
                        </div>
                    </div>
                </x-ui.card>

                {{-- ACTIONS --}}
                <div class="flex items-center gap-2">
                    <x-ui.button type="submit">Simpan Perubahan</x-ui.button>

                    <a href="{{ route('students.show', $student) }}">
                        <x-ui.button variant="secondary">Batal</x-ui.button>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
