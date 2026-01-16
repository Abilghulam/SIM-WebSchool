<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Preview Import Siswa</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Berikut ringkasan data, contoh baris yang akan diproses, dan daftar error (jika ada).
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
                <x-ui.card title="Total Baris di File">
                    <div class="text-3xl font-bold">{{ $result['stats']['total_rows'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">Jumlah baris data yang terbaca (tanpa header).</div>
                </x-ui.card>

                <x-ui.card title="Siap Diproses">
                    <div class="text-3xl font-bold">{{ $result['stats']['valid_rows'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">Baris yang lolos pengecekan dan bisa disimpan.</div>
                </x-ui.card>

                <x-ui.card title="Perlu Diperbaiki">
                    <div class="text-3xl font-bold">{{ $result['stats']['invalid_rows'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">Baris yang bermasalah dan akan dilewati saat commit.</div>
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

            <x-ui.card title="Contoh Data (maks. 20 baris)"
                subtitle="Contoh di bawah membantu memastikan format file sudah benar.">
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
                                        Tidak ada contoh data yang bisa ditampilkan (mungkin semua baris bermasalah).
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="text-xs text-gray-500 mt-3">
                    Catatan: Kelas boleh kosong untuk data awal (misalnya siswa baru terdaftar dan belum dibagi kelas).
                </div>
            </x-ui.card>

            <x-ui.card title="Daftar Error (maks. 50)"
                subtitle="Jika ada error, kamu bisa perbaiki file lalu upload ulang.">
                @if (empty($result['errors']))
                    <div class="text-sm text-gray-700">
                        Tidak ada error. ✅ Data siap disimpan.
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
