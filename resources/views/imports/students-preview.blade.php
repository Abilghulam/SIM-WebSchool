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

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <x-ui.card title="Total Siswa">
                    <div class="text-3xl font-bold">{{ $result['stats']['total_rows'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">Baris data siswa yang terbaca</div>
                </x-ui.card>

                <x-ui.card title="Siap Diproses">
                    <div class="text-3xl font-bold">{{ $result['stats']['valid_rows'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">Baris data siswa yang siap diproses</div>
                </x-ui.card>

                <x-ui.card title="Perlu Perbaikan">
                    <div class="text-3xl font-bold">{{ $result['stats']['invalid_rows'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">Baris data siswa yang perlu perbaikan</div>
                </x-ui.card>

                <x-ui.card title="Pengaturan Import">
                    <div class="text-sm font-semibold text-gray-900">
                        {{ $options['mode'] === 'students_only' ? 'Hanya Data Siswa' : 'Data Siswa + Penempatan' }}
                        •
                        {{ $options['strategy'] === 'create_only' ? 'Lewati jika NIS sudah ada' : 'Perbarui jika NIS sudah ada' }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1 space-y-1">
                        <div>
                            Default Tahun Ajaran:
                            <span class="font-semibold">{{ $options['default_school_year_id'] ?? '-' }}</span>
                        </div>
                        <div>
                            Default Kelas:
                            <span class="font-semibold">{{ $options['default_classroom_id'] ?? '-' }}</span>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            <x-ui.card title="Preview Data Siswa"
                subtitle="Data siswa yang berhasil diproses oleh sistem dan siap disimpan">
                <div class="overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">NIS</th>
                                <th class="py-2 pr-4">Nama</th>
                                <th class="py-2 pr-4">Gender</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-4">Tahun Ajaran (ID)</th>
                                <th class="py-2 pr-4">Kelas (ID)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($result['preview'] as $r)
                                @php
                                    $sy = $r['school_year_id'] ?? ($options['default_school_year_id'] ?? null);
                                    $cl = $r['classroom_id'] ?? ($options['default_classroom_id'] ?? null);
                                @endphp
                                <tr class="border-t">
                                    <td class="py-2 pr-4">{{ $r['nis'] }}</td>
                                    <td class="py-2 pr-4 font-semibold text-gray-900">{{ $r['full_name'] }}</td>
                                    <td class="py-2 pr-4">{{ $r['gender'] ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ $r['status'] ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ $sy ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ $cl ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-gray-500">
                                        Tidak ada data siswa yang berhasil diproses
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="text-xs text-gray-500 mt-3">
                    Catatan: Tabel di atas menampilkan data siswa berdasarkan file yang diunggah, jika data tidak ada di
                    tabel maka data tersebut tidak berhasil diproses
                </div>
            </x-ui.card>

            <x-ui.card title="Preview Error Data Siswa"
                subtitle="Data siswa yang bermasalah dan perlu perbaikan pada file import">
                @if (empty($result['errors']))
                    <div class="text-sm text-gray-700">
                        Error tidak ditemukan, semua data siswa sudah valid dan siap disimpan
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($result['errors'] as $e)
                            <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                                <div class="text-sm font-semibold text-red-700">Baris {{ $e['line'] }}</div>
                                <ul class="mt-2 text-sm text-red-700 list-disc pl-5 space-y-1">
                                    @foreach ($e['errors'] as $msg)
                                        <li>{{ $msg }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach

                        @if ($result['has_more_errors'])
                            <div class="text-xs text-gray-500">
                                Masih ada error lainnya, tampilan daftar error dipersingkat.
                            </div>
                        @endif
                    </div>
                @endif
            </x-ui.card>

            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('imports.students.commit') }}" data-loading-scope>
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <x-ui.button variant="primary" type="submit" :disabled="$result['stats']['valid_rows'] === 0" data-loading-text="Menyimpan...">
                        Simpan Hasil Import
                    </x-ui.button>
                </form>

                @if ($result['stats']['valid_rows'] === 0)
                    <div class="text-sm text-gray-500">
                        Belum ada baris yang bisa disimpan.
                    </div>
                @elseif ($result['stats']['invalid_rows'] > 0)
                    <div class="text-sm text-gray-500 ms-auto">
                        Ada baris yang bermasalah. Baris tersebut akan dilewati saat proses penyimpanan.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
