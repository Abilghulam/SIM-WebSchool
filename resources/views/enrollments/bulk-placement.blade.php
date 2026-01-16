@php
    $user = auth()->user();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Penempatan Massal Siswa</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Halaman ini menampilkan siswa berstatus <span class="font-semibold">Aktif</span> yang
                    <span class="font-semibold">belum tercatat</span> pada Tahun Ajaran aktif, atau
                    <span class="font-semibold">kelasnya masih kosong</span> pada Tahun Ajaran aktif <span
                        class="font-semibold">{{ $activeYear?->name }}</span>.
                </p>
            </div>

            <a href="{{ route('students.index') }}">
                <x-ui.button variant="secondary">← Kembali</x-ui.button>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <x-ui.flash />

            {{-- Filter --}}
            <x-ui.card title="Filter Pencarian"
                subtitle="Gunakan filter untuk mempercepat menemukan siswa yang perlu ditempatkan.">
                <form method="GET" action="{{ route('enrollments.bulk-placement.index') }}"
                    class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-6">
                        <label class="block text-sm font-medium text-gray-700">Cari Siswa</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Ketik Nama atau NIS"
                            class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">Tahun Masuk</label>
                        <input type="number" name="entry_year" value="{{ request('entry_year') }}"
                            placeholder="Contoh: 2024"
                            class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>

                    <div class="md:col-span-3 flex items-end gap-2">
                        <x-ui.button variant="primary" type="submit">Terapkan</x-ui.button>
                        <a href="{{ route('enrollments.bulk-placement.index') }}">
                            <x-ui.button variant="secondary">Reset</x-ui.button>
                        </a>
                    </div>
                </form>
            </x-ui.card>

            {{-- Form penempatan --}}
            <form method="POST" action="{{ route('enrollments.bulk-placement.store') }}" class="space-y-6"
                data-loading-scope>
                @csrf

                <x-ui.card title="Tujuan Penempatan"
                    subtitle="Kamu boleh mengosongkan Kelas jika pembagian kelas belum dilakukan.">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-4">
                            <label class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                            <input type="text" value="{{ $activeYear?->name }}" disabled
                                class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 text-gray-700" />
                            <div class="text-xs text-gray-500 mt-1">
                                Sistem selalu mengikuti Tahun Ajaran yang sedang aktif.
                            </div>
                        </div>

                        <div class="md:col-span-4">
                            <label class="block text-sm font-medium text-gray-700">Kelas (Opsional)</label>
                            <select name="classroom_id"
                                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">- Belum ditentukan -</option>
                                @foreach ($classrooms as $c)
                                    <option value="{{ $c->id }}" @selected((string) old('classroom_id') === (string) $c->id)>
                                        {{ $c->name }} {{ $c->major?->name ? '• ' . $c->major->name : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('classroom_id')
                                <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                            <div class="text-xs text-gray-500 mt-1">
                                Jika kelas dikosongkan, siswa tetap akan tercatat pada Tahun Ajaran aktif.
                            </div>
                        </div>

                        <div class="md:col-span-4">
                            <label class="block text-sm font-medium text-gray-700">Catatan (Opsional)</label>
                            <input type="text" name="note" value="{{ old('note') }}"
                                placeholder="Contoh: Siswa baru, menunggu pembagian kelas"
                                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                            @error('note')
                                <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card title="Pilih Siswa"
                    subtitle="Centang siswa yang akan dicatat pada Tahun Ajaran aktif (dan kelas jika kamu isi).">
                    <x-ui.table>
                        <x-slot:head>
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold">
                                    <input id="check-all" type="checkbox" class="rounded border-gray-300">
                                </th>
                                <th class="px-6 py-4 text-left font-semibold">NIS</th>
                                <th class="px-6 py-4 text-left font-semibold">Nama</th>
                                <th class="px-6 py-4 text-left font-semibold">Tahun Masuk</th>
                            </tr>
                        </x-slot:head>

                        @forelse($students as $s)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <input type="checkbox" name="student_ids[]" value="{{ $s->id }}"
                                        class="student-check rounded border-gray-300">
                                </td>
                                <td class="px-6 py-4 text-gray-700">{{ $s->nis }}</td>
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $s->full_name }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $s->entry_year ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                    Tidak ada siswa yang perlu ditempatkan.
                                </td>
                            </tr>
                        @endforelse

                        <x-slot:footer>
                            <div class="flex items-center gap-2">
                                <x-ui.button variant="primary" type="submit" data-loading-text="Memproses...">
                                    Proses Penempatan
                                </x-ui.button>

                                <div class="text-sm text-gray-500 ms-auto">
                                    Total kandidat: <span
                                        class="font-semibold text-gray-900">{{ $students->total() }}</span>
                                </div>
                            </div>

                            <div class="mt-4">
                                {{ $students->links() }}
                            </div>

                            @error('student_ids')
                                <div class="text-sm text-red-600 mt-2">{{ $message }}</div>
                            @enderror

                            <div class="text-xs text-gray-500 mt-3">
                                Catatan: Siswa yang sudah memiliki kelas pada Tahun Ajaran aktif tidak akan tampil di
                                halaman ini.
                            </div>
                        </x-slot:footer>
                    </x-ui.table>
                </x-ui.card>
            </form>

            <script>
                (function() {
                    const checkAll = document.getElementById('check-all');
                    const checks = () => Array.from(document.querySelectorAll('.student-check'));

                    if (checkAll) {
                        checkAll.addEventListener('change', function() {
                            checks().forEach(cb => cb.checked = checkAll.checked);
                        });
                    }
                })();
            </script>

        </div>
    </div>
</x-app-layout>
