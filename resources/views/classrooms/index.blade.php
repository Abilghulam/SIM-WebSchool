@php
    $gradeOptions = [
        '' => 'Semua Tingkat',
        '10' => 'Kelas 10',
        '11' => 'Kelas 11',
        '12' => 'Kelas 12',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">Kelas</h2>
                <p class="text-sm text-gray-500 mt-1">Kelola kelas berdasarkan jurusan dan tingkat.</p>
            </div>

            <a href="{{ route('classrooms.create') }}">
                <x-ui.button>+ Tambah Kelas</x-ui.button>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <x-ui.flash />

            <x-ui.card title="Filter" subtitle="Cari dan saring kelas.">
                <form method="GET" action="{{ route('classrooms.index') }}"
                    class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-5">
                        <x-ui.input name="search" label="Cari" placeholder="Nama kelas (mis. X TKJ A)"
                            value="{{ request('search') }}" />
                    </div>

                    <div class="md:col-span-4">
                        <x-ui.select name="major_id" label="Jurusan">
                            <option value="">Semua Jurusan</option>
                            @foreach ($majors as $m)
                                <option value="{{ $m->id }}" @selected((string) request('major_id') === (string) $m->id)>
                                    {{ $m->name }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div class="md:col-span-3">
                        <x-ui.select name="grade_level" label="Tingkat">
                            @foreach ($gradeOptions as $val => $label)
                                <option value="{{ $val }}" @selected((string) request('grade_level') === (string) $val)>{{ $label }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div class="md:col-span-12 flex items-end gap-2">
                        <x-ui.button type="submit">Terapkan</x-ui.button>
                        <a href="{{ route('classrooms.index') }}">
                            <x-ui.button variant="secondary">Reset</x-ui.button>
                        </a>
                        <div class="ms-auto text-sm text-gray-500">
                            <span class="font-semibold text-gray-900">{{ $classrooms->total() }}</span> Kelas
                        </div>
                    </div>
                </form>
            </x-ui.card>

            <x-ui.card title="Daftar Kelas" subtitle="Klik Edit untuk mengubah data kelas.">
                <x-ui.table>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Nama</th>
                            <th class="px-6 py-4 text-left font-semibold">Tingkat</th>
                            <th class="px-6 py-4 text-left font-semibold">Jurusan</th>
                            <th class="px-6 py-4 text-right font-semibold">Aksi</th>
                        </tr>
                    </x-slot:head>

                    @forelse($classrooms as $c)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $c->name }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $c->grade_level ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $c->major?->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('classrooms.edit', $c) }}"
                                    class="text-gray-700 hover:text-gray-900 font-semibold">Edit</a>
                                <span class="text-gray-300 mx-2">|</span>
                                <form method="POST" action="{{ route('classrooms.destroy', $c) }}" class="inline"
                                    onsubmit="return confirm('Hapus kelas ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:text-red-800 font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500">Belum ada data kelas.</td>
                        </tr>
                    @endforelse

                    <x-slot:footer>
                        {{ $classrooms->links() }}
                    </x-slot:footer>
                </x-ui.table>
            </x-ui.card>

        </div>
    </div>
</x-app-layout>
