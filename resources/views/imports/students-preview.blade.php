<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Preview Import Data Siswa</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Preview hasil pengecekan data siswa berdasarkan file yang diunggah
                </p>
            </div>

            <a href="{{ route('imports.students.create') }}">
                <x-ui.button variant="secondary">← Kembali</x-ui.button>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-ui.flash />

            {{-- KPI + Pengaturan --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <x-ui.card title="Total Siswa">
                    <div class="text-3xl font-bold text-gray-900">{{ data_get($result, 'stats.total_rows', 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Baris data siswa yang terbaca</div>
                </x-ui.card>

                <x-ui.card title="Siap Diproses">
                    <div class="text-3xl font-bold text-gray-900">{{ data_get($result, 'stats.valid_rows', 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Baris data siswa yang siap disimpan</div>
                </x-ui.card>

                <x-ui.card title="Perlu Perbaikan">
                    <div class="text-3xl font-bold text-gray-900">{{ data_get($result, 'stats.invalid_rows', 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Baris data siswa yang bermasalah</div>
                </x-ui.card>

                <x-ui.card title="Pengaturan Import">
                    <div class="flex flex-wrap gap-2">
                        <span
                            class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $importSettings['mode_pill'] ?? '' }}">
                            {{ $importSettings['mode_label'] ?? '-' }}
                        </span>
                        <span
                            class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $importSettings['strategy_pill'] ?? '' }}">
                            {{ $importSettings['strategy_label'] ?? '-' }}
                        </span>
                    </div>

                    <div class="mt-3 space-y-2 text-sm">
                        <div
                            class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white px-3 py-2">
                            <div class="text-xs text-gray-500">Tahun Ajaran</div>
                            <div class="text-xs font-semibold text-gray-900 text-right">
                                {{ $importSettings['default_school_year_text'] ?? '-' }}
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white px-3 py-2">
                            <div class="text-xs text-gray-500">Kelas</div>
                            <div class="text-xs font-semibold text-gray-900 text-right">
                                {{ $importSettings['default_classroom_text'] ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 text-xs text-gray-500">
                        <span class="font-semibold text-gray-700">Tips:</span>
                        {{ $importSettings['tips'] ?? '-' }}
                    </div>
                </x-ui.card>
            </div>

            {{-- Preview valid --}}
            <x-ui.card title="Preview Data Siswa" subtitle="Data valid yang siap disimpan (baris error akan dilewati).">
                <div class="overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">NIS</th>
                                <th class="py-2 pr-4">Nama</th>
                                <th class="py-2 pr-4">Gender</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-4">Tahun Ajaran</th>
                                <th class="py-2 pr-4">Kelas</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($previewRows as $r)
                                <tr class="border-t">
                                    <td class="py-2 pr-4">{{ $r['nis'] ?? '-' }}</td>
                                    <td class="py-2 pr-4 font-semibold text-gray-900">{{ $r['full_name'] ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ $r['gender'] ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ $r['status'] ?? '-' }}</td>

                                    <td class="py-2 pr-4">
                                        <span
                                            class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2 py-0.5 text-xs font-semibold text-gray-700">
                                            {{ $r['school_year_text'] ?? '-' }}
                                        </span>
                                    </td>

                                    <td class="py-2 pr-4">
                                        <span
                                            class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2 py-0.5 text-xs font-semibold text-gray-700">
                                            {{ $r['classroom_text'] ?? '-' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-gray-500">
                                        Tidak ada data siswa yang berhasil diproses.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="text-xs text-gray-500 mt-3">
                    Catatan: tabel ini menampilkan data valid hasil baca file. Data yang tidak muncul biasanya masuk
                    kategori error dan tidak siap disimpan.
                </div>
            </x-ui.card>

            {{-- Error preview --}}
            <x-ui.card title="Perlu Perbaikan" subtitle="Daftar baris yang bermasalah beserta alasan singkatnya.">
                @if (empty($errorRows))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                        <div class="font-semibold">Tidak ada error ✅</div>
                        <div class="mt-1">Semua baris data valid dan siap disimpan.</div>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($errorRows as $e)
                            <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-semibold text-red-800">
                                            Baris {{ $e['line'] ?? '-' }} perlu diperbaiki
                                        </div>
                                        <div class="text-xs text-red-700 mt-1">
                                            Periksa data pada baris ini di file import, lalu upload ulang.
                                        </div>
                                    </div>

                                    <span
                                        class="inline-flex items-center rounded-full border border-red-200 bg-white px-3 py-1 text-xs font-semibold text-red-700">
                                        Error
                                    </span>
                                </div>

                                <ul class="mt-3 text-sm text-red-800 list-disc pl-5 space-y-1">
                                    @foreach ($e['messages'] ?? [] as $msg)
                                        <li>{{ $msg }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach

                        @if (data_get($result, 'has_more_errors'))
                            <div class="text-xs text-gray-500">
                                Masih ada error lainnya, tampilan daftar error dipersingkat.
                            </div>
                        @endif
                    </div>
                @endif
            </x-ui.card>

            {{-- Actions --}}
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('imports.students.commit') }}" data-loading-scope>
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <x-ui.button variant="primary" type="submit" :disabled="data_get($result, 'stats.valid_rows', 0) === 0" data-loading-text="Menyimpan...">
                        Simpan Hasil Import
                    </x-ui.button>
                </form>

                @if (data_get($result, 'stats.valid_rows', 0) === 0)
                    <div class="text-sm text-gray-500">
                        Belum ada baris yang bisa disimpan.
                    </div>
                @elseif (data_get($result, 'stats.invalid_rows', 0) > 0)
                    <div class="text-sm text-gray-500 ms-auto">
                        Ada baris yang bermasalah. Baris tersebut akan dilewati saat proses penyimpanan.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
