{{-- resources/views/staff/edit.blade.php --}}
@php
    $user = auth()->user();
    $isAdminOrOperator = in_array($user->role_label, ['admin', 'operator'], true);

    // default: sama seperti guru, non admin/operator jadi readonly
    $readonly = !$isAdminOrOperator;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">Edit TAS</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $staff->full_name }} • NIP {{ $staff->nip }}</p>
            </div>

            <a href="{{ route('staff.show', $staff) }}">
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

            <form method="POST" action="{{ route('staff.update', $staff) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <x-ui.card title="Biodata"
                    subtitle="{{ $readonly ? 'Beberapa field hanya bisa diubah oleh Admin/Operator.' : 'Perbarui data TAS.' }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.input label="NIP" name="nip" required value="{{ old('nip', $staff->nip) }}"
                            :error="$errors->first('nip')" :readonly="$readonly" />

                        <x-ui.input label="Nama Lengkap" name="full_name" required
                            value="{{ old('full_name', $staff->full_name) }}" :error="$errors->first('full_name')" :readonly="$readonly" />

                        <x-ui.select label="Jenis Kelamin" name="gender" :disabled="$readonly">
                            <option value="">- Pilih -</option>
                            <option value="L" @selected(old('gender', $staff->gender) === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('gender', $staff->gender) === 'P')>Perempuan</option>
                        </x-ui.select>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.input label="Tempat Lahir" name="birth_place"
                                value="{{ old('birth_place', $staff->birth_place) }}" :readonly="$readonly" />
                            <x-ui.input label="Tanggal Lahir" name="birth_date" type="date"
                                value="{{ old('birth_date', optional($staff->birth_date)->format('Y-m-d')) }}"
                                :readonly="$readonly" />
                        </div>

                        @php
                            $religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'];
                            $maritals = ['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati'];
                        @endphp

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.select label="Agama" name="religion" :disabled="$readonly" id="religion">
                                <option value="">- Pilih -</option>
                                @foreach ($religions as $r)
                                    <option value="{{ $r }}" @selected(old('religion', $staff->religion) === $r)>
                                        {{ $r }}</option>
                                @endforeach
                            </x-ui.select>

                            <x-ui.input label="Lainnya" name="religion_other"
                                value="{{ old('religion_other', $staff->religion_other) }}" :readonly="$readonly"
                                :error="$errors->first('religion_other')" id="religion_other" placeholder="Ketik agama lainnya.." />
                        </div>

                        <x-ui.select label="Status Kawin" name="marital_status" :disabled="$readonly">
                            <option value="">- Pilih -</option>
                            @foreach ($maritals as $m)
                                <option value="{{ $m }}" @selected(old('marital_status', $staff->marital_status) === $m)>{{ $m }}
                                </option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.input label="Telepon" name="phone" value="{{ old('phone', $staff->phone) }}"
                            :error="$errors->first('phone')" />
                        <x-ui.input label="Email" name="email" type="email"
                            value="{{ old('email', $staff->email) }}" :error="$errors->first('email')" />

                        <div class="md:col-span-2">
                            <x-ui.textarea label="Alamat" name="address" rows="3"
                                :error="$errors->first('address')">{{ old('address', $staff->address) }}</x-ui.textarea>
                        </div>

                        <x-ui.input label="Status Kepegawaian" name="employment_status"
                            value="{{ old('employment_status', $staff->employment_status) }}" :readonly="$readonly" />

                        <x-ui.select label="Aktif" name="is_active" :disabled="$readonly">
                            <option value="1" @selected((string) old('is_active', (int) $staff->is_active) === '1')>Ya</option>
                            <option value="0" @selected((string) old('is_active', (int) $staff->is_active) === '0')>Tidak</option>
                        </x-ui.select>
                    </div>
                </x-ui.card>

                <div class="flex items-center gap-2">
                    <x-ui.button type="submit">Simpan Perubahan</x-ui.button>
                    <a href="{{ route('staff.show', $staff) }}">
                        <x-ui.button variant="secondary">Batal</x-ui.button>
                    </a>
                </div>
            </form>

        </div>
    </div>

    {{-- JS toggle religion_other (respect readonly) --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const religionSelect = document.querySelector('select[name="religion"]');
            const religionOther = document.querySelector('input[name="religion_other"]');

            if (!religionSelect || !religionOther) return;

            // readonly dari server (blade)
            const isReadonly = @json($readonly);

            const syncReligionOther = () => {
                const val = religionSelect.value;

                if (val === 'Lainnya') {
                    religionOther.required = true;

                    // kalau bukan readonly, baru boleh enable
                    if (!isReadonly) {
                        religionOther.disabled = false;
                    }
                } else {
                    religionOther.required = false;

                    // kalau bukan readonly, baru boleh clear+disable
                    if (!isReadonly) {
                        religionOther.value = '';
                        religionOther.disabled = true;
                    }
                }
            };

            syncReligionOther();
            religionSelect.addEventListener('change', syncReligionOther);
        });
    </script>
</x-app-layout>
