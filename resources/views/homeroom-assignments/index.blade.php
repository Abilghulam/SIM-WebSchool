<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">Wali Kelas</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Penugasan wali kelas untuk tahun ajaran aktif.
                </p>
            </div>

            <a href="{{ route('school-years.index') }}">
                <x-ui.button variant="secondary">Kelola Tahun Ajaran</x-ui.button>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <x-ui.flash />

            <x-ui.card title="Tahun Ajaran Aktif" subtitle="Penugasan wali kelas hanya berlaku pada tahun ajaran aktif.">
                @if (!$activeSchoolYear)
                    <div class="text-sm text-red-700 bg-red-50 border border-red-200 px-4 py-3 rounded-lg">
                        Belum ada Tahun Ajaran aktif. Silakan aktifkan di menu Master Tahun Ajaran.
                    </div>
                @else
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs text-gray-500">Aktif</div>
                            <div class="mt-1 text-lg font-semibold text-gray-900">{{ $activeSchoolYear->name }}</div>
                        </div>
                        <x-ui.badge variant="green">Aktif</x-ui.badge>
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card title="Assign Wali Kelas" subtitle="Pilih kelas dan guru untuk tahun ajaran aktif.">
                <form method="POST" action="{{ route('homeroom-assignments.store') }}"
                    class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    @csrf

                    <div class="md:col-span-5">
                        <x-ui.select name="classroom_id" label="Kelas" :error="$errors->first('classroom_id')" required>
                            <option value="">- Pilih -</option>
                            @foreach ($classrooms as $c)
                                <option value="{{ $c->id }}" @selected(old('classroom_id') == $c->id)>
                                    {{ $c->name }} {{ $c->grade_level ? "(Kelas {$c->grade_level})" : '' }} —
                                    {{ $c->major?->name ?? 'Tanpa Jurusan' }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div class="md:col-span-5">
                        <x-ui.select name="teacher_id" label="Guru" :error="$errors->first('teacher_id')" required>
                            <option value="">- Pilih -</option>
                            @foreach ($teachers as $t)
                                <option value="{{ $t->id }}" @selected(old('teacher_id') == $t->id)>
                                    {{ $t->full_name }} ({{ $t->nip }})
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div class="md:col-span-2 flex items-end">
                        <x-ui.button type="submit" class="w-full">Simpan</x-ui.button>
                    </div>

                    <div class="md:col-span-12 text-sm text-gray-500">
                        Catatan: Jika kelas sudah punya wali kelas, maka akan otomatis diganti (update).
                    </div>
                </form>
            </x-ui.card>

            <x-ui.card title="Daftar Wali Kelas" subtitle="Data penugasan pada tahun ajaran aktif.">
                <x-ui.table>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Kelas</th>
                            <th class="px-6 py-4 text-left font-semibold">Jurusan</th>
                            <th class="px-6 py-4 text-left font-semibold">Wali Kelas</th>
                            <th class="px-6 py-4 text-right font-semibold">Aksi</th>
                        </tr>
                    </x-slot:head>

                    @forelse($assignments as $a)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $a->classroom?->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $a->classroom?->major?->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-700">
                                {{ $a->teacher?->full_name ?? '-' }}
                                <div class="text-xs text-gray-500 mt-1">{{ $a->teacher?->nip ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <form method="POST" action="{{ route('homeroom-assignments.destroy', $a) }}"
                                    class="inline" onsubmit="return confirm('Hapus penugasan wali kelas ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:text-red-800 font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                Belum ada penugasan wali kelas (atau belum ada tahun ajaran aktif).
                            </td>
                        </tr>
                    @endforelse
                </x-ui.table>
            </x-ui.card>

        </div>
    </div>
</x-app-layout>
