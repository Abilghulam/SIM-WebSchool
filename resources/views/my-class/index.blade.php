<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">Siswa Kelas Saya</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Data siswa berdasarkan tahun ajaran aktif & penugasan wali kelas.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-ui.flash />

            {{-- INFO CARDS --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <x-ui.card title="Tahun Ajaran Aktif">
                    <div class="text-lg font-semibold">
                        {{ $activeSchoolYear?->name ?? '-' }}
                    </div>
                </x-ui.card>

                <x-ui.card title="Kelas">
                    <div class="text-lg font-semibold">
                        {{ $classroom->name ?? '-' }}
                    </div>
                </x-ui.card>

                <x-ui.card title="Jurusan">
                    <div class="text-lg font-semibold">
                        {{ $classroom->major?->name ?? '-' }}
                    </div>
                    @if ($classroom->major?->code)
                        <div class="text-sm text-gray-500 mt-1">{{ $classroom->major->code }}</div>
                    @endif
                </x-ui.card>
            </div>

            {{-- TABLE --}}
            <x-ui.card title="Daftar Siswa" subtitle="Read-only untuk wali kelas.">
                <x-ui.table>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">NIS</th>
                            <th class="px-6 py-4 text-left font-semibold">Nama</th>
                            <th class="px-6 py-4 text-left font-semibold">Gender</th>
                            <th class="px-6 py-4 text-right font-semibold">Aksi</th>
                        </tr>
                    </x-slot:head>

                    @forelse($students as $s)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-700">{{ $s->nis }}</td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $s->full_name }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-700">
                                @if ($s->gender === 'L')
                                    Laki-laki
                                @elseif($s->gender === 'P')
                                    Perempuan
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('students.show', $s) }}"
                                    class="text-indigo-600 hover:text-indigo-800 font-semibold">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                Belum ada siswa di kelas ini.
                            </td>
                        </tr>
                    @endforelse

                    <x-slot:footer>
                        {{ $students->links() }}
                    </x-slot:footer>
                </x-ui.table>
            </x-ui.card>

        </div>
    </div>
</x-app-layout>
