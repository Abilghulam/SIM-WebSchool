@php
    $user = auth()->user();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">Master Jurusan</h2>
                <p class="text-sm text-gray-500 mt-1">Kelola jurusan di sekolah.</p>
            </div>

            <a href="{{ route('majors.create') }}">
                <x-ui.button>+ Tambah Jurusan</x-ui.button>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-ui.flash />

            <x-ui.card title="Filter" subtitle="Cari jurusan berdasarkan nama atau kode.">
                <form method="GET" action="{{ route('majors.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-8">
                        <x-ui.input name="search" label="Cari" placeholder="Nama / kode"
                            value="{{ request('search') }}" />
                    </div>
                    <div class="md:col-span-4 flex items-end gap-2">
                        <x-ui.button type="submit">Terapkan</x-ui.button>
                        <a href="{{ route('majors.index') }}">
                            <x-ui.button variant="secondary">Reset</x-ui.button>
                        </a>
                    </div>
                    <div class="md:col-span-12 text-sm text-gray-500">
                        Total: <span class="font-semibold text-gray-900">{{ $majors->total() }}</span>
                    </div>
                </form>
            </x-ui.card>

            <x-ui.card title="Daftar Jurusan" subtitle="Data jurusan yang tersedia.">
                <x-ui.table>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Kode</th>
                            <th class="px-6 py-4 text-left font-semibold">Nama</th>
                            <th class="px-6 py-4 text-right font-semibold">Aksi</th>
                        </tr>
                    </x-slot:head>

                    @forelse($majors as $major)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-700">{{ $major->code ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-900 font-semibold">{{ $major->name }}</td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <a class="text-gray-700 hover:text-gray-900 font-semibold"
                                    href="{{ route('majors.edit', $major) }}">Edit</a>

                                <span class="text-gray-300 mx-2">|</span>

                                <form method="POST" action="{{ route('majors.destroy', $major) }}" class="inline"
                                    onsubmit="return confirm('Hapus jurusan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:text-red-800 font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-gray-500">Belum ada data.</td>
                        </tr>
                    @endforelse

                    <x-slot:footer>
                        {{ $majors->links() }}
                    </x-slot:footer>
                </x-ui.table>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
