{{-- resources/views/search/index.blade.php --}}
@php
    $q = $q ?? request('q');

    $studentCount = $students?->total() ?? 0;
    $teacherCount = $teachers?->total() ?? 0;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">Hasil Pencarian</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Cari berdasarkan <span class="font-semibold">Nama</span> dan <span
                        class="font-semibold">NIS/NIP</span>.
                </p>
            </div>

            <a href="{{ route('dashboard') }}">
                <x-ui.button variant="secondary">Kembali</x-ui.button>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Search Box --}}
            <x-ui.card title="Cari Cepat" subtitle="Ketik minimal 2 karakter untuk hasil yang lebih akurat.">
                <form method="GET" action="{{ route('global-search.index') }}"
                    class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-9">
                        <x-ui.input label="Kata kunci" name="q" value="{{ $q }}"
                            placeholder="Contoh: Andi, 12345 (NIS), 1987... (NIP)" />
                    </div>

                    <div class="md:col-span-3 flex items-end gap-2">
                        <x-ui.button type="submit" class="w-full">Cari</x-ui.button>
                    </div>

                    <div class="md:col-span-12 text-sm text-gray-500">
                        Menampilkan:
                        <span class="font-semibold text-gray-900">{{ $studentCount }}</span> siswa,
                        <span class="font-semibold text-gray-900">{{ $teacherCount }}</span> guru
                        @if ($q)
                            untuk kata kunci: <span class="font-semibold text-gray-900">"{{ $q }}"</span>
                        @endif
                    </div>
                </form>
            </x-ui.card>

            {{-- STUDENTS --}}
            <x-ui.card title="Siswa" subtitle="Hasil siswa yang sesuai dengan pencarian.">
                <x-ui.table>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">NIS</th>
                            <th class="px-6 py-4 text-left font-semibold">Nama</th>
                            <th class="px-6 py-4 text-left font-semibold">Kelas</th>
                            <th class="px-6 py-4 text-left font-semibold">Jurusan</th>
                            <th class="px-6 py-4 text-left font-semibold">Status</th>
                            <th class="px-6 py-4 text-right font-semibold">Aksi</th>
                        </tr>
                    </x-slot:head>

                    @forelse($students as $student)
                        @php
                            $enr = $student->activeEnrollment;
                            $className = $enr?->classroom?->name ?? '-';
                            $majorName = $enr?->classroom?->major?->name ?? '-';
                            $status = $student->status ?? '-';

                            $badgeVariant = match ($status) {
                                'aktif' => 'green',
                                'lulus' => 'blue',
                                'pindah' => 'amber',
                                'nonaktif' => 'gray',
                                default => 'gray',
                            };
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $student->nis }}</td>

                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $student->full_name }}</div>
                                @if ($student->phone)
                                    <div class="text-xs text-gray-500 mt-1">{{ $student->phone }}</div>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $className }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $majorName }}</td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-ui.badge :variant="$badgeVariant">
                                    {{ ucfirst($status) }}
                                </x-ui.badge>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="{{ route('students.show', $student) }}"
                                    class="text-indigo-600 hover:text-indigo-800 font-semibold">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                Tidak ada hasil siswa.
                            </td>
                        </tr>
                    @endforelse

                    <x-slot:footer>
                        {{-- penting: pagination siswa pakai pageName = students_page --}}
                        {{ $students->appends(['q' => $q])->links() }}
                    </x-slot:footer>
                </x-ui.table>
            </x-ui.card>

            {{-- TEACHERS --}}
            <x-ui.card title="Guru" subtitle="Hasil guru yang sesuai dengan pencarian.">
                <x-ui.table>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">NIP</th>
                            <th class="px-6 py-4 text-left font-semibold">Nama</th>
                            <th class="px-6 py-4 text-left font-semibold">Wali Kelas (TA Aktif)</th>
                            <th class="px-6 py-4 text-left font-semibold">Status</th>
                            <th class="px-6 py-4 text-right font-semibold">Aksi</th>
                        </tr>
                    </x-slot:head>

                    @forelse($teachers as $teacher)
                        @php
                            $badgeVariant = $teacher->is_active ? 'green' : 'gray';
                            $badgeText = $teacher->is_active ? 'Aktif' : 'Nonaktif';

                            // homeroomAssignments sudah di-load di controller suggest,
                            // tapi untuk halaman index ini kita belum load khusus.
                            // Aman pakai relasi lazy (atau nanti kita bisa eager load juga).
                            $classes = $teacher->homeroomAssignments
                                ?->pluck('classroom.name')
                                ->filter()
                                ->unique()
                                ->values()
                                ->all();

                            $homeroomText = !empty($classes) ? implode(', ', $classes) : '-';
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $teacher->nip }}</td>

                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $teacher->full_name }}</div>
                                @if ($teacher->phone)
                                    <div class="text-xs text-gray-500 mt-1">{{ $teacher->phone }}</div>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-gray-700">
                                {{ $homeroomText }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-ui.badge :variant="$badgeVariant">{{ $badgeText }}</x-ui.badge>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="{{ route('teachers.show', $teacher) }}"
                                    class="text-indigo-600 hover:text-indigo-800 font-semibold">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                Tidak ada hasil guru.
                            </td>
                        </tr>
                    @endforelse

                    <x-slot:footer>
                        {{-- penting: pagination guru pakai pageName = teachers_page --}}
                        {{ $teachers->appends(['q' => $q])->links() }}
                    </x-slot:footer>
                </x-ui.table>
            </x-ui.card>

        </div>
    </div>
</x-app-layout>
