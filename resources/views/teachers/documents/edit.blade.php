<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900">Edit Dokumen Guru</h2>
            <a href="{{ route('teachers.show', $teacher) }}">
                <x-ui.button variant="secondary">← Kembali</x-ui.button>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash success (auto hide) --}}
            <x-ui.flash />

            {{-- Validation errors --}}
            @if ($errors->any())
                <x-ui.alert type="danger">
                    <div class="font-semibold">Terjadi kesalahan:</div>
                    <ul class="list-disc ms-5 mt-1">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </x-ui.alert>
            @endif

            <x-ui.card title="Edit Dokumen" subtitle="Ubah jenis/judul atau ganti file (opsional).">
                <form method="POST" action="{{ route('teachers.documents.update', [$teacher, $document]) }}"
                    enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    @csrf
                    @method('PUT')

                    <div class="md:col-span-6">
                        <x-ui.select label="Jenis Dokumen" name="document_type_id">
                            <option value="">- Pilih -</option>
                            @foreach ($documentTypes as $dt)
                                <option value="{{ $dt->id }}" @selected((int) old('document_type_id', $document->document_type_id) === (int) $dt->id)>
                                    {{ $dt->name }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div class="md:col-span-6">
                        <x-ui.input label="Judul" name="title" value="{{ old('title', $document->title) }}"
                            :error="$errors->first('title')" />
                    </div>

                    <div class="md:col-span-12">
                        <x-ui.field label="Ganti File (opsional)" :error="$errors->first('file')">
                            <input type="file" name="file" class="block w-full text-sm text-gray-700" />
                        </x-ui.field>

                        <div class="text-xs text-gray-500 mt-2">
                            File saat ini: <span class="font-semibold">{{ $document->file_name }}</span>
                        </div>
                    </div>

                    <div class="md:col-span-12 flex gap-2">
                        <x-ui.button type="submit" variant="primary">Simpan</x-ui.button>
                        <a href="{{ route('teachers.show', $teacher) }}">
                            <x-ui.button variant="secondary">Batal</x-ui.button>
                        </a>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
