<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">Master Tahun Ajaran</h2>
                <p class="text-sm text-gray-500 mt-1">Kelola tahun ajaran dan set tahun aktif.</p>
            </div>

            <a href="{{ route('school-years.create') }}">
                <x-ui.button>+ Tambah Tahun Ajaran</x-ui.button>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-ui.flash />

            <x-ui.card title="Daftar Tahun Ajaran" subtitle="Hanya satu tahun ajaran boleh aktif.">
                <x-ui.table>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Nama</th>
                            <th class="px-6 py-4 text-left font-semibold">Periode</th>
                            <th class="px-6 py-4 text-left font-semibold">Status</th>
                            <th class="px-6 py-4 text-right font-semibold">Aksi</th>
                        </tr>
                    </x-slot:head>

                    @forelse($schoolYears as $sy)
                        @php
                            $badgeVariant = $sy->is_active ? 'green' : 'gray';
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $sy->name }}</td>
                            <td class="px-6 py-4 text-gray-700">
                                {{ $sy->start_date ? \Carbon\Carbon::parse($sy->start_date)->format('d-m-Y') : '-' }}
                                s/d
                                {{ $sy->end_date ? \Carbon\Carbon::parse($sy->end_date)->format('d-m-Y') : '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :variant="$badgeVariant">{{ $sy->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                @if (!$sy->is_active)
                                    <form method="POST" action="{{ route('school-years.activate', $sy) }}"
                                        class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="text-indigo-600 hover:text-indigo-800 font-semibold">
                                            Aktifkan
                                        </button>
                                    </form>
                                    <span class="text-gray-300 mx-2">|</span>
                                @endif

                                <a href="{{ route('school-years.edit', $sy) }}"
                                    class="text-gray-700 hover:text-gray-900 font-semibold">Edit</a>
                                <span class="text-gray-300 mx-2">|</span>

                                <form method="POST" action="{{ route('school-years.destroy', $sy) }}" class="inline"
                                    onsubmit="return confirm('Hapus tahun ajaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:text-red-800 font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500">Belum ada data.</td>
                        </tr>
                    @endforelse

                    <x-slot:footer>
                        {{ $schoolYears->links() }}
                    </x-slot:footer>
                </x-ui.table>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
