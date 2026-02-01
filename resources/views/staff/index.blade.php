{{-- resources/views/staff/index.blade.php --}}
@php
    $user = auth()->user();
    $isAdminOrOperator = in_array($user->role_label, ['admin', 'operator'], true);

    $activeOptions = [
        '' => 'Semua',
        '1' => 'Aktif',
        '0' => 'Nonaktif',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">Data Tenaga Administrasi Sekolah</h2>
                <p class="text-sm text-gray-500 mt-1">Gunakan pencarian dan filter untuk menemukan data Tenaga
                    Administrasi Sekolah.</p>
            </div>

            @if ($isAdminOrOperator)
                <a href="{{ route('staff.create') }}">
                    <x-ui.button>+ Tambah Tenaga Administrasi Sekolah</x-ui.button>
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <x-ui.card title="Filter" subtitle="Cari dan saring data Tenaga Administrasi Sekolah.">
                <form method="GET" action="{{ route('staff.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-5">
                        <label class="block text-sm font-medium text-gray-700">Cari</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau NIP"
                            class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>

                    <div class="md:col-span-2">
                        <x-ui.select label="Status Aktif" name="active">
                            @foreach ($activeOptions as $val => $label)
                                <option value="{{ $val }}" @selected((string) request('active') === (string) $val)>{{ $label }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div class="md:col-span-12 flex items-center gap-2 pt-1">
                        <x-ui.button variant="primary" type="submit">Terapkan</x-ui.button>
                        <a href="{{ route('staff.index') }}">
                            <x-ui.button variant="secondary">Reset</x-ui.button>
                        </a>

                        <div class="text-sm text-gray-500 ms-auto">
                            Total: <span class="font-semibold text-gray-900">{{ $staffs->total() }}</span>
                        </div>
                    </div>
                </form>
            </x-ui.card>

            <x-ui.card title="Daftar Tenaga Administrasi Sekolah" subtitle="Klik Detail untuk melihat profil lengkap.">
                <x-ui.table>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">NIP</th>
                            <th class="px-6 py-4 text-left font-semibold">Nama</th>
                            <th class="px-6 py-4 text-left font-semibold">Kontak</th>
                            <th class="px-6 py-4 text-left font-semibold">Status</th>
                            <th class="px-6 py-4 text-right font-semibold">Aksi</th>
                        </tr>
                    </x-slot:head>

                    @forelse($staffs as $staff)
                        @php
                            $badgeVariant = $staff->is_active ? 'green' : 'gray';
                            $badgeText = $staff->is_active ? 'Aktif' : 'Nonaktif';
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $staff->nip }}</td>

                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $staff->full_name }}</div>
                                @if ($staff->employment_status)
                                    <div class="text-xs text-gray-500 mt-1">{{ $staff->employment_status }}</div>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                <div class="text-gray-700">{{ $staff->phone ?? '-' }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ $staff->email ?? '-' }}</div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-ui.badge :variant="$badgeVariant">{{ $badgeText }}</x-ui.badge>
                            </td>

                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('staff.show', $staff) }}"
                                    class="text-navy-500 hover:text-navy-700 font-semibold">
                                    Detail
                                </a>

                                @can('update', $staff)
                                    <span class="text-gray-300 mx-2">|</span>
                                    <a href="{{ route('staff.edit', $staff) }}"
                                        class="text-gray-700 hover:text-gray-900 font-semibold">
                                        Edit
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                Data tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse

                    <x-slot:footer>
                        {{ $staffs->links() }}
                    </x-slot:footer>
                </x-ui.table>
            </x-ui.card>

        </div>
    </div>
</x-app-layout>
