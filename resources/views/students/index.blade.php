{{-- resources/views/students/index.blade.php --}}
@php
    $user = auth()->user();
    $isAdminOrOperator = in_array($user->role_label, ['admin', 'operator'], true);

    $statusOptions = [
        '' => 'Semua Status',
        'aktif' => 'Aktif',
        'lulus' => 'Lulus',
        'pindah' => 'Pindah',
        'nonaktif' => 'Nonaktif',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">
                    {{ $isAdminOrOperator ? 'Data Siswa' : 'Siswa Kelas Saya' }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Gunakan pencarian dan filter untuk menemukan data siswa.
                </p>
            </div>

            @if ($isAdminOrOperator)
                <a href="{{ route('students.create') }}">
                    <x-ui.button variant="primary">
                        + Tambah Siswa
                    </x-ui.button>
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash message --}}
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Filter --}}
            <x-ui.card title="Filter" subtitle="Cari dan saring data siswa.">
                <form method="GET" action="{{ route('students.index') }}"
                    class="grid grid-cols-1 md:grid-cols-12 gap-4">

                    {{-- Search (lebih lebar) --}}
                    <div class="md:col-span-5">
                        <label class="block text-sm font-medium text-gray-700">Cari</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau NIS"
                            class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>

                    {{-- Status --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status"
                            class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($statusOptions as $val => $label)
                                <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tahun Ajaran --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                        <select name="school_year_id"
                            class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Semua</option>
                            @foreach ($schoolYears ?? [] as $sy)
                                <option value="{{ $sy->id }}" @selected((string) request('school_year_id') === (string) $sy->id)>
                                    {{ $sy->name }} @if ($sy->is_active)
                                        (Aktif)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Jurusan --}}
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">Jurusan</label>
                        <select name="major_id"
                            class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Semua</option>
                            @foreach ($majors ?? [] as $m)
                                <option value="{{ $m->id }}" @selected((string) request('major_id') === (string) $m->id)>
                                    {{ $m->code ? "{$m->code} - " : '' }}{{ $m->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Kelas --}}
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">Kelas</label>
                        <select name="classroom_id"
                            class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Semua</option>
                            @foreach ($classrooms ?? [] as $c)
                                <option value="{{ $c->id }}" @selected((string) request('classroom_id') === (string) $c->id)>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Buttons --}}
                    <div class="md:col-span-12 flex items-center gap-2 pt-1">
                        <x-ui.button variant="primary" type="submit">Terapkan</x-ui.button>

                        <a href="{{ route('students.index') }}">
                            <x-ui.button variant="secondary">Reset</x-ui.button>
                        </a>

                        <div class="text-sm text-gray-500 ms-auto">
                            Total: <span class="font-semibold text-gray-900">{{ $students->total() }}</span>
                        </div>
                    </div>
                </form>
            </x-ui.card>

            {{-- Table --}}
            <x-ui.card title="Daftar Siswa" subtitle="Klik Detail untuk melihat profil lengkap.">
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
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                {{ $student->nis }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $student->full_name }}</div>
                                @if ($student->phone)
                                    <div class="text-xs text-gray-500 mt-1">{{ $student->phone }}</div>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                {{ $className }}
                            </td>

                            <td class="px-6 py-4 text-gray-700">
                                {{ $majorName }}
                            </td>

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

                                @if ($isAdminOrOperator)
                                    <span class="text-gray-300 mx-2">|</span>
                                    <a href="{{ route('students.edit', $student) }}"
                                        class="text-gray-700 hover:text-gray-900 font-semibold">
                                        Edit
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                Data tidak ditemukan.
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
