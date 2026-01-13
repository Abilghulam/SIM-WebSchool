<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Import Siswa (CSV/XLSX)</h2>
                <p class="text-sm text-gray-500 mt-1">Preview dulu, baru commit. Support Create / Upsert by NIS.</p>
            </div>

            <a href="{{ route('imports.students.template') }}">
                <x-ui.button variant="secondary">Download Template</x-ui.button>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <x-ui.flash />

            <x-ui.card title="Upload File" subtitle="File harus punya header kolom (baris pertama).">
                <form method="POST" action="{{ route('imports.students.preview') }}" enctype="multipart/form-data"
                    class="space-y-5" data-loading-scope>
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">File</label>
                        <input type="file" name="file" required accept=".xlsx,.csv"
                            class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                        @error('file')
                            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                        <div class="text-xs text-gray-500 mt-1">
                            Disarankan XLSX. CSV boleh, tapi pastikan delimiter rapi.
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Mode</label>
                            <select name="mode"
                                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="students_only" @selected(old('mode', 'students_with_enrollment') === 'students_only')>
                                    Students only
                                </option>
                                <option value="students_with_enrollment" @selected(old('mode', 'students_with_enrollment') === 'students_with_enrollment')>
                                    Students + Enrollment
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Strategi</label>
                            <select name="strategy"
                                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="create_only" @selected(old('strategy', 'upsert_by_nis') === 'create_only')>
                                    Create only (skip jika NIS sudah ada)
                                </option>
                                <option value="upsert_by_nis" @selected(old('strategy', 'upsert_by_nis') === 'upsert_by_nis')>
                                    Upsert by NIS (update jika sudah ada)
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Default Tahun Ajaran</label>
                            <select name="default_school_year_id"
                                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">(Opsional)</option>
                                @foreach ($schoolYears as $sy)
                                    <option value="{{ $sy->id }}" @selected((string) old('default_school_year_id') === (string) $sy->id)>
                                        {{ $sy->name }} @if ($sy->is_active)
                                            (Aktif)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Default Kelas</label>
                            <select name="default_classroom_id"
                                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">(Opsional)</option>
                                @foreach ($classrooms as $c)
                                    <option value="{{ $c->id }}" @selected((string) old('default_classroom_id') === (string) $c->id)>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Default Enrollment Aktif</label>
                            <select name="default_enrollment_is_active"
                                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="1" @selected(old('default_enrollment_is_active', '1') === '1')>Ya</option>
                                <option value="0" @selected(old('default_enrollment_is_active', '1') === '0')>Tidak</option>
                            </select>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700">
                        <div class="font-semibold text-gray-900">Panduan cepat</div>
                        <ul class="list-disc pl-5 mt-2 space-y-1">
                            <li>Minimal kolom wajib: <span class="font-semibold">nis</span>, <span
                                    class="font-semibold">full_name</span>.</li>
                            <li>Enrollment opsional: isi pasangan <span class="font-semibold">school_year</span> + <span
                                    class="font-semibold">classroom</span> atau pakai default.</li>
                            <li>Tanggal lahir: <span class="font-semibold">YYYY-MM-DD</span> atau <span
                                    class="font-semibold">DD/MM/YYYY</span>.</li>
                        </ul>
                    </div>

                    <div class="pt-1 flex items-center gap-2">
                        <x-ui.button variant="primary" type="submit" data-loading-text="Membaca file...">
                            Preview Import
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
