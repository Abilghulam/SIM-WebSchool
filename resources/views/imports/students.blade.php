<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Import Data Siswa (CSV/XLSX)</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Unggah file, periksa dulu di halaman preview, lalu simpan jika sudah sesuai.
                </p>
            </div>

            <a href="{{ route('imports.students.template') }}">
                <x-ui.button variant="secondary">Download Template</x-ui.button>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <x-ui.flash />

            <x-ui.card title="Unggah File" subtitle="Pastikan baris pertama berisi judul kolom (header).">
                <form method="POST" action="{{ route('imports.students.preview') }}" enctype="multipart/form-data"
                    class="space-y-5" data-loading-scope>
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">File Import</label>
                        <input type="file" name="file" required accept=".xlsx,.csv"
                            class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                        @error('file')
                            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                        <div class="text-xs text-gray-500 mt-1">
                            Disarankan format <span class="font-semibold">XLSX</span>. Jika memakai CSV, pastikan
                            pemisah kolom rapi.
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Isi yang Diimpor</label>
                            <select name="mode"
                                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="students_only" @selected(old('mode', 'students_with_enrollment') === 'students_only')>
                                    Hanya Data Siswa
                                </option>
                                <option value="students_with_enrollment" @selected(old('mode', 'students_with_enrollment') === 'students_with_enrollment')>
                                    Data Siswa + Penempatan Tahun Ajaran / Kelas
                                </option>
                            </select>
                            <div class="text-xs text-gray-500 mt-1">
                                Pilih opsi kedua jika ingin sekaligus mencatat penempatan siswa pada tahun ajaran
                                tertentu.
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jika NIS Sudah Ada</label>
                            <select name="strategy"
                                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="create_only" @selected(old('strategy', 'upsert_by_nis') === 'create_only')>
                                    Lewati (tidak diubah)
                                </option>
                                <option value="upsert_by_nis" @selected(old('strategy', 'upsert_by_nis') === 'upsert_by_nis')>
                                    Perbarui Data (update)
                                </option>
                            </select>
                            <div class="text-xs text-gray-500 mt-1">
                                Jika memilih “Perbarui Data”, biodata siswa dengan NIS yang sama akan diperbarui.
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tahun Ajaran (Default)</label>
                            <select name="default_school_year_id"
                                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">(Kosongkan jika tidak diperlukan)</option>
                                @foreach ($schoolYears as $sy)
                                    <option value="{{ $sy->id }}" @selected((string) old('default_school_year_id') === (string) $sy->id)>
                                        {{ $sy->name }} @if ($sy->is_active)
                                            (Aktif)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="text-xs text-gray-500 mt-1">
                                Dipakai jika kamu memilih mode “Penempatan”, tetapi kolom tahun ajaran di file kosong.
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kelas (Default)</label>
                            <select name="default_classroom_id"
                                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">(Kosongkan jika belum ada pembagian kelas)</option>
                                @foreach ($classrooms as $c)
                                    <option value="{{ $c->id }}" @selected((string) old('default_classroom_id') === (string) $c->id)>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="text-xs text-gray-500 mt-1">
                                Kelas boleh kosong. Cocok untuk data awal saat siswa baru terdaftar.
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status Penempatan (Default)</label>
                            <select name="default_enrollment_is_active"
                                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="1" @selected(old('default_enrollment_is_active', '1') === '1')>Aktif</option>
                                <option value="0" @selected(old('default_enrollment_is_active', '1') === '0')>Tidak Aktif</option>
                            </select>
                            <div class="text-xs text-gray-500 mt-1">
                                Berlaku jika kamu memilih mode “Penempatan”. Umumnya pilih “Aktif”.
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700">
                        <div class="font-semibold text-gray-900">Panduan Singkat</div>
                        <ul class="list-disc pl-5 mt-2 space-y-1">
                            <li>Kolom wajib di file: <span class="font-semibold">nis</span> dan <span
                                    class="font-semibold">full_name</span>.</li>
                            <li>
                                Jika memilih <span class="font-semibold">Data Siswa + Penempatan</span>:
                                <span class="font-semibold">Tahun Ajaran wajib</span> (isi di file atau gunakan “Tahun
                                Ajaran (Default)”).
                                <span class="font-semibold">Kelas boleh kosong</span> jika belum ada pembagian kelas.
                            </li>
                            <li>Tanggal lahir bisa ditulis <span class="font-semibold">YYYY-MM-DD</span> atau <span
                                    class="font-semibold">DD/MM/YYYY</span>.</li>
                        </ul>
                    </div>

                    <div class="pt-1 flex items-center gap-2">
                        <x-ui.button variant="primary" type="submit" data-loading-text="Membaca file...">
                            Lanjutkan ke Preview
                        </x-ui.button>

                        <a href="{{ route('students.index') }}">
                            <x-ui.button variant="secondary">Batal</x-ui.button>
                        </a>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
