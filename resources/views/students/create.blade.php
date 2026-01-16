{{-- resources/views/students/create.blade.php --}}
@php
    $statusOptions = [
        'aktif' => 'Aktif',
        'lulus' => 'Lulus',
        'pindah' => 'Pindah',
        'nonaktif' => 'Nonaktif',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">
                    Tambah Siswa
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Isi biodata siswa dan tentukan kelas serta tahun ajaran.
                </p>
            </div>

            <a href="{{ route('students.index') }}">
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

            <form method="POST" action="{{ route('students.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- BIODATA --}}
                <x-ui.card title="Biodata" subtitle="Data utama siswa.">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.input label="NIS" name="nis" required placeholder="Contoh: 20240001"
                            value="{{ old('nis') }}" :error="$errors->first('nis')" />

                        <x-ui.input label="Nama Lengkap" name="full_name" required placeholder="Nama sesuai dokumen"
                            value="{{ old('full_name') }}" :error="$errors->first('full_name')" />

                        <x-ui.select label="Jenis Kelamin" name="gender" :error="$errors->first('gender')">
                            <option value="">- Pilih -</option>
                            <option value="L" @selected(old('gender') === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('gender') === 'P')>Perempuan</option>
                        </x-ui.select>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.input label="Tempat Lahir" name="birth_place" value="{{ old('birth_place') }}"
                                :error="$errors->first('birth_place')" />

                            <x-ui.input label="Tanggal Lahir" name="birth_date" type="date"
                                value="{{ old('birth_date') }}" :error="$errors->first('birth_date')" />
                        </div>

                        <x-ui.input label="Agama" name="religion" placeholder="Contoh: Islam/Kristen/Hindu/..."
                            value="{{ old('religion') }}" :error="$errors->first('religion')" />

                        <x-ui.input label="Telepon" name="phone" value="{{ old('phone') }}" :error="$errors->first('phone')" />

                        <x-ui.input label="Email" name="email" type="email" value="{{ old('email') }}"
                            :error="$errors->first('email')" />

                        <div class="md:col-span-2">
                            <x-ui.textarea label="Alamat" name="address" rows="3"
                                :error="$errors->first('address')">{{ old('address') }}</x-ui.textarea>
                        </div>
                    </div>
                </x-ui.card>

                {{-- ORANG TUA / WALI --}}
                <x-ui.card title="Orang Tua / Wali" subtitle="Kontak keluarga untuk pendataan.">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.input label="Nama Ayah" name="father_name" value="{{ old('father_name') }}"
                            :error="$errors->first('father_name')" />
                        <x-ui.input label="Nama Ibu" name="mother_name" value="{{ old('mother_name') }}"
                            :error="$errors->first('mother_name')" />
                        <x-ui.input label="Nama Wali" name="guardian_name" value="{{ old('guardian_name') }}"
                            :error="$errors->first('guardian_name')" />
                        <x-ui.input label="Telepon Orang Tua/Wali" name="parent_phone"
                            value="{{ old('parent_phone') }}" :error="$errors->first('parent_phone')" />
                    </div>
                </x-ui.card>

                {{-- STATUS + PENEMPATAN --}}
                <x-ui.card title="Status & Penempatan" subtitle="Tentukan kelas dan tahun ajaran (wajib).">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.select label="Status" name="status" :error="$errors->first('status')">
                            @foreach ($statusOptions as $val => $label)
                                <option value="{{ $val }}" @selected(old('status', 'aktif') === $val)>{{ $label }}
                                </option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.input label="Tahun Masuk" name="entry_year" type="number" placeholder="Contoh: 2024"
                            value="{{ old('entry_year') }}" :error="$errors->first('entry_year')" />

                        <x-ui.select label="Tahun Ajaran" name="school_year_id" required :error="$errors->first('school_year_id')">
                            <option value="">- Pilih -</option>
                            @foreach ($schoolYears as $sy)
                                <option value="{{ $sy->id }}" @selected((string) old('school_year_id') === (string) $sy->id)>
                                    {{ $sy->name }} @if ($sy->is_active)
                                        (Aktif)
                                    @endif
                                </option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.select label="Kelas (opsional)" name="classroom_id" :error="$errors->first('classroom_id')">
                            <option value="">- Belum ditentukan -</option>
                            @foreach ($classrooms as $c)
                                <option value="{{ $c->id }}" @selected((string) old('classroom_id') === (string) $c->id)>
                                    {{ $c->name }} {{ $c->major?->name ? '• ' . $c->major->name : '' }}
                                </option>
                            @endforeach
                        </x-ui.select>

                        <div class="md:col-span-2">
                            <x-ui.input label="Catatan Penempatan (opsional)" name="enrollment_note"
                                placeholder="Contoh: Mutasi / pindahan / catatan khusus"
                                value="{{ old('enrollment_note') }}" :error="$errors->first('enrollment_note')" />
                        </div>
                    </div>
                </x-ui.card>

                {{-- DOKUMEN AWAL (OPSIONAL) --}}
                <x-ui.card title="Dokumen Awal (Opsional)" subtitle="Jika ingin upload dokumen saat pendaftaran.">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-4">
                            <x-ui.select label="Jenis Dokumen" name="documents[0][document_type_id]">
                                <option value="">- Pilih -</option>
                                @foreach ($documentTypes as $dt)
                                    <option value="{{ $dt->id }}" @selected((string) old('documents.0.document_type_id') === (string) $dt->id)>
                                        {{ $dt->name }}</option>
                                @endforeach
                            </x-ui.select>
                        </div>

                        <div class="md:col-span-4">
                            <x-ui.input label="Judul (opsional)" name="documents[0][title]"
                                placeholder="Contoh: Kartu Keluarga" value="{{ old('documents.0.title') }}" />
                        </div>

                        <div class="md:col-span-4">
                            <x-ui.field label="File">
                                <input type="file" name="documents[0][file]"
                                    class="block w-full text-sm text-gray-700" />
                            </x-ui.field>
                        </div>
                    </div>

                    <p class="text-xs text-gray-500 mt-3">
                        Kamu bisa tambah input dokumen berikutnya nanti (pakai JS) kalau diperlukan.
                    </p>
                </x-ui.card>

                {{-- ACTIONS --}}
                <div class="flex items-center gap-2">
                    <x-ui.button type="submit">Simpan</x-ui.button>

                    <a href="{{ route('students.index') }}">
                        <x-ui.button variant="secondary">Batal</x-ui.button>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
