<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">Tambah Tahun Ajaran</h2>
                <p class="text-sm text-gray-500 mt-1">Tambahkan tahun ajaran baru.</p>
            </div>

            <a href="{{ route('school-years.index') }}">
                <x-ui.button variant="secondary">← Kembali</x-ui.button>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-ui.flash />

            <form method="POST" action="{{ route('school-years.store') }}" class="space-y-6">
                @csrf

                <x-ui.card title="Data Tahun Ajaran" subtitle="Nama wajib, periode opsional.">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.input label="Nama" name="name" required placeholder="2025/2026"
                            value="{{ old('name') }}" :error="$errors->first('name')" />

                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.input label="Tanggal Mulai (opsional)" type="date" name="start_date"
                                value="{{ old('start_date') }}" :error="$errors->first('start_date')" />
                            <x-ui.input label="Tanggal Selesai (opsional)" type="date" name="end_date"
                                value="{{ old('end_date') }}" :error="$errors->first('end_date')" />
                        </div>
                    </div>
                </x-ui.card>

                <div class="flex gap-2">
                    <x-ui.button type="submit">Simpan</x-ui.button>
                    <a href="{{ route('school-years.index') }}"><x-ui.button variant="secondary">Batal</x-ui.button></a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
