<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">Tambah Jurusan</h2>
                <p class="text-sm text-gray-500 mt-1">Tambahkan jurusan baru.</p>
            </div>

            <a href="{{ route('majors.index') }}">
                <x-ui.button variant="secondary">← Kembali</x-ui.button>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-ui.flash />

            <form method="POST" action="{{ route('majors.store') }}" class="space-y-6">
                @csrf

                <x-ui.card title="Data Jurusan" subtitle="Isi nama dan kode (opsional).">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.input label="Kode (opsional)" name="code" value="{{ old('code') }}"
                            :error="$errors->first('code')" />
                        <x-ui.input label="Nama Jurusan" name="name" required value="{{ old('name') }}"
                            :error="$errors->first('name')" />
                    </div>
                </x-ui.card>

                <div class="flex gap-2">
                    <x-ui.button type="submit">Simpan</x-ui.button>
                    <a href="{{ route('majors.index') }}"><x-ui.button variant="secondary">Batal</x-ui.button></a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
