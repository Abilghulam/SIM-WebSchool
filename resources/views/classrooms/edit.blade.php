<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">Edit Kelas</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $classroom->name }}</p>
            </div>

            <a href="{{ route('classrooms.index') }}">
                <x-ui.button variant="secondary">← Kembali</x-ui.button>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-ui.flash />

            <form method="POST" action="{{ route('classrooms.update', $classroom) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <x-ui.card title="Data Kelas" subtitle="Perbarui jurusan, tingkat, dan nama kelas.">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.select name="major_id" label="Jurusan (opsional)" :error="$errors->first('major_id')">
                            <option value="">- Pilih -</option>
                            @foreach ($majors as $m)
                                <option value="{{ $m->id }}" @selected((string) old('major_id', $classroom->major_id) === (string) $m->id)>
                                    {{ $m->name }}
                                </option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.select name="grade_level" label="Tingkat (opsional)" :error="$errors->first('grade_level')">
                            <option value="">- Pilih -</option>
                            @foreach ([10, 11, 12] as $g)
                                <option value="{{ $g }}" @selected((string) old('grade_level', $classroom->grade_level) === (string) $g)>{{ $g }}
                                </option>
                            @endforeach
                        </x-ui.select>

                        <div class="md:col-span-2">
                            <x-ui.input name="name" label="Nama Kelas" required
                                value="{{ old('name', $classroom->name) }}" :error="$errors->first('name')" />
                        </div>
                    </div>
                </x-ui.card>

                <div class="flex gap-2">
                    <x-ui.button type="submit">Simpan</x-ui.button>
                    <a href="{{ route('classrooms.index') }}"><x-ui.button variant="secondary">Batal</x-ui.button></a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
