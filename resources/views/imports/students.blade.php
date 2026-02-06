<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Import Data Siswa</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Menambahkan data siswa secara otomatis melalui file format XLSX atau CSV
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

            <x-ui.card title="Unggah File"
                subtitle="Gunakan template format yang disediakan untuk menambahkan data siswa">
                <form method="POST" action="{{ route('imports.students.preview') }}" enctype="multipart/form-data"
                    class="space-y-5" data-loading-scope>
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Import File</label>
                        <input type="file" name="file" required accept=".xlsx,.csv"
                            class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                        @error('file')
                            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                        <div class="text-xs text-gray-500 mt-1">
                            Disarankan format <span class="font-semibold">XLSX</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jenis Import</label>
                            <select id="importMode" name="mode"
                                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="students_only" @selected(old('mode', 'students_with_enrollment') === 'students_only')>
                                    Hanya data siswa
                                </option>
                                <option value="students_with_enrollment" @selected(old('mode', 'students_with_enrollment') === 'students_with_enrollment')>
                                    Data siswa & Penempatan tahun ajaran
                                </option>
                            </select>
                            <div class="text-xs text-gray-500 mt-1">
                                Atur <span class="font-semibold">Jenis Import</span> untuk mengatur metode import
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kategori Data</label>
                            <select name="strategy"
                                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="create_only" @selected(old('strategy', 'upsert_by_nis') === 'create_only')>
                                    Tambah data baru
                                </option>
                                <option value="upsert_by_nis" @selected(old('strategy', 'upsert_by_nis') === 'upsert_by_nis')>
                                    Perbarui data
                                </option>
                            </select>
                            <div class="text-xs text-gray-500 mt-1">
                                Atur <span class="font-semibold">Jenis Data</span> untuk mengatur kategorisasi data
                            </div>
                        </div>
                    </div>

                    {{-- Pengaturan Penempatan --}}
                    @php
                        $defaultWrapBase = 'rounded-2xl border border-gray-200 bg-white p-4 transition';
                        $defaultHintBase = 'mt-2 rounded-xl border px-4 py-3 text-sm';
                    @endphp

                    <div id="enrollmentDefaultsWrap" class="{{ $defaultWrapBase }} hidden">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-sm font-semibold text-gray-900">Pengaturan Penempatan</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Atur penempatan tahun ajaran, kelas, dan status siswa jika Anda ingin mengaturnya
                                    otomatis/default
                                </div>
                            </div>

                            <div class="shrink-0">
                                <span id="enrollmentBadge"
                                    class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-semibold text-gray-600">
                                    Penempatan: -
                                </span>
                            </div>
                        </div>

                        <div id="enrollmentDisabledHint"
                            class="{{ $defaultHintBase }} bg-amber-50 border-amber-200 text-amber-800 hidden">
                            <div class="font-semibold">Nonaktif</div>
                            <div class="mt-1 text-sm">
                                Pilih <span class="font-semibold">Data siswa & Penempatan tahun ajaran</span> agar opsi
                                Tahun Ajaran, Kelas, dan Status Penempatan bisa diatur.
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="default-field">
                                <label class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                                <select id="defaultSchoolYear" name="default_school_year_id"
                                    class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">- Pilih -</option>
                                    @foreach ($schoolYears as $sy)
                                        <option value="{{ $sy->id }}" @selected((string) old('default_school_year_id') === (string) $sy->id)>
                                            {{ $sy->name }} @if ($sy->is_active)
                                                (Aktif)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="text-xs text-gray-500 mt-1">
                                    Pilih tahun ajaran yang sedang aktif
                                </div>
                            </div>

                            <div class="default-field">
                                <label class="block text-sm font-medium text-gray-700">Kelas</label>
                                <select id="defaultClassroom" name="default_classroom_id"
                                    class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">- Pilih -</option>
                                    @foreach ($classrooms as $c)
                                        <option value="{{ $c->id }}" @selected((string) old('default_classroom_id') === (string) $c->id)>
                                            {{ $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="text-xs text-gray-500 mt-1">
                                    Pilih kelas untuk data siswa baru
                                </div>
                            </div>

                            <div class="default-field">
                                <label class="block text-sm font-medium text-gray-700">Status Siswa
                                </label>
                                <select id="defaultEnrollmentActive" name="default_enrollment_is_active"
                                    class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="1" @selected(old('default_enrollment_is_active', '1') === '1')>Aktif</option>
                                    <option value="0" @selected(old('default_enrollment_is_active', '1') === '0')>Tidak Aktif</option>
                                </select>
                                <div class="text-xs text-gray-500 mt-1">
                                    Default "Aktif"
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Panduan Singkat --}}
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700">
                        <div class="font-semibold text-gray-900">Panduan Singkat</div>
                        <ul class="list-disc pl-5 mt-2 space-y-1">
                            <li>
                                Pilih <span class="font-semibold">Jenis Import</span> sesuai kebutuhan:
                                <span class="font-semibold">Hanya data siswa</span> atau
                                <span class="font-semibold">Data siswa & Penempatan tahun ajaran</span>.
                            </li>
                            <li>
                                Jika memilih <span class="font-semibold">Hanya data siswa</span>, maka
                                <span class="font-semibold">Pengaturan Penempatan (Tahun Ajaran, Kelas, dan Status
                                    Siswa)</span>
                                akan <span class="font-semibold">nonaktif</span> karena tidak diperlukan.
                            </li>
                            <li>
                                Jika memilih <span class="font-semibold">Data siswa & Penempatan tahun ajaran</span>,
                                maka wajib mengisi <span class="font-semibold">Pengaturan Penempatan (Tahun Ajaran,
                                    Kelas, dan Status Siswa)</span>
                            </li>
                            <li>
                                Setelah mengunggah file dan mengatur penempatan, klik <span
                                    class="font-semibold">Lanjutkan ke Preview</span> untuk
                                mengecek data sebelum disimpan pada halaman preview.
                            </li>
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

    {{-- Interaktif: show/hide & enable/disable --}}
    <script>
        (function() {
            const modeEl = document.getElementById('importMode');

            const wrap = document.getElementById('enrollmentDefaultsWrap');
            const hint = document.getElementById('enrollmentDisabledHint');
            const badge = document.getElementById('enrollmentBadge');

            const sy = document.getElementById('defaultSchoolYear');
            const cl = document.getElementById('defaultClassroom');
            const st = document.getElementById('defaultEnrollmentActive');

            function setFieldsDisabled(disabled) {
                [sy, cl, st].forEach(el => {
                    if (!el) return;
                    el.disabled = !!disabled;
                });
            }

            function setBadge(enabled) {
                if (!badge) return;

                if (enabled) {
                    badge.textContent = 'Penempatan Aktif';
                    badge.className =
                        'inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700';
                } else {
                    badge.textContent = 'Penempatan -';
                    badge.className =
                        'inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-semibold text-gray-600';
                }
            }

            function sync() {
                const mode = modeEl ? modeEl.value : 'students_with_enrollment';
                const enabled = (mode === 'students_with_enrollment');

                if (wrap) {
                    wrap.classList.toggle('hidden', !enabled);
                }

                if (hint) hint.classList.add('hidden');

                setFieldsDisabled(!enabled);

                setBadge(enabled);
            }

            if (modeEl) {
                modeEl.addEventListener('change', sync);
            }

            sync();
        })();
    </script>
</x-app-layout>
