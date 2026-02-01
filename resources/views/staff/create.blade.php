{{-- resources/views/staff/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">Tambah TAS</h2>
                <p class="text-sm text-gray-500 mt-1">Isi biodata TAS untuk pendataan.</p>
            </div>

            <a href="{{ route('staff.index') }}">
                <x-ui.button variant="secondary">← Kembali</x-ui.button>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <div class="font-semibold">Terdapat kesalahan input:</div>
                    <ul class="list-disc ms-5 mt-2 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('staff.store') }}" class="space-y-6">
                @csrf

                <x-ui.card title="Biodata" subtitle="Data utama TAS.">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.input label="NIP" name="nip" required placeholder="Contoh: 1987xxxxxx"
                            value="{{ old('nip') }}" :error="$errors->first('nip')" />

                        <x-ui.input label="Nama Lengkap" name="full_name" required value="{{ old('full_name') }}"
                            :error="$errors->first('full_name')" />

                        <x-ui.select label="Jenis Kelamin" name="gender" :error="$errors->first('gender')">
                            <option value="">- Pilih -</option>
                            <option value="L" @selected(old('gender') === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('gender') === 'P')>Perempuan</option>
                        </x-ui.select>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.input label="Tempat Lahir" name="birth_place" value="{{ old('birth_place') }}"
                                :error="$errors->first('birth_place')" />
                            <x-ui.input label="Tanggal Lahir" name="birth_date" type="date"
                                value="{{ old('birth_date') }}" :error="$errors->first('birth_date')" />
                        </div>

                        <x-ui.input label="Telepon" name="phone" value="{{ old('phone') }}" :error="$errors->first('phone')" />
                        <x-ui.input label="Email" name="email" type="email" value="{{ old('email') }}"
                            :error="$errors->first('email')" />

                        <div class="md:col-span-2">
                            <x-ui.textarea label="Alamat" name="address" rows="3"
                                :error="$errors->first('address')">{{ old('address') }}</x-ui.textarea>
                        </div>

                        <x-ui.input label="Status Kepegawaian" name="employment_status"
                            placeholder="Contoh: PNS/Honorer/TAS" value="{{ old('employment_status') }}"
                            :error="$errors->first('employment_status')" />

                        <x-ui.select label="Aktif" name="is_active" :error="$errors->first('is_active')">
                            <option value="1" @selected(old('is_active', '1') === '1')>Ya</option>
                            <option value="0" @selected(old('is_active') === '0')>Tidak</option>
                        </x-ui.select>
                    </div>
                </x-ui.card>

                <div class="flex items-center gap-2">
                    <x-ui.button type="submit">Simpan</x-ui.button>
                    <a href="{{ route('staff.index') }}">
                        <x-ui.button variant="secondary">Batal</x-ui.button>
                    </a>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
