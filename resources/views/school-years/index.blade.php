<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">Tahun Ajaran</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Kelola daftar tahun ajaran. Hanya satu tahun ajaran yang bisa aktif.
                </p>
            </div>

            <a href="{{ route('school-years.create') }}">
                <x-ui.button>+ Tambah Tahun Ajaran</x-ui.button>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-ui.flash />

            <x-ui.card title="Daftar Tahun Ajaran"
                subtitle="Tahun ajaran yang terkunci tidak bisa diubah maupun diaktifkan.">
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

                            $start = optional($sy->start_date)->format('d-m-Y') ?? '-';
                            $end = optional($sy->end_date)->format('d-m-Y') ?? '-';

                            $canActivate = !$sy->is_active && !$sy->is_locked;
                            $canEditDelete = !$sy->is_locked;
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                {{ $sy->name }}
                            </td>

                            <td class="px-6 py-4 text-gray-700">
                                {{ $start }} s/d {{ $end }}
                            </td>

                            <td class="px-6 py-4">
                                <x-ui.badge :variant="$badgeVariant">
                                    {{ $sy->is_active ? 'Aktif' : 'Nonaktif' }}
                                </x-ui.badge>

                                @if ($sy->is_locked)
                                    <x-ui.badge variant="orange" class="ml-2">Terkunci</x-ui.badge>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                {{-- Promote: hanya untuk TA aktif, tidak terkunci, dan ada TA tujuan --}}
                                @can('promoteEnrollment', \App\Models\SchoolYear::class)
                                    @if ($sy->is_active && !$sy->is_locked && ($otherYearsExist ?? false))
                                        <x-ui.button
                                            href="{{ route('enrollments.promote.index', ['from_year_id' => $sy->id]) }}"
                                            class="inline-flex items-center px-3 py-1 text-xs font-semibold text-white bg-red-600 rounded hover:bg-red-700"
                                            type="submit">
                                            Promote
                                        </x-ui.button>
                                        <span class="text-gray-300 mx-2">|</span>
                                    @endif
                                @endcan

                                {{-- Aktifkan: hanya tampil jika nonaktif dan tidak terkunci --}}
                                @if ($canActivate)
                                    <form method="POST" action="{{ route('school-years.activate', $sy) }}"
                                        class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="text-green-600 hover:text-green-800 font-semibold">
                                            Aktifkan
                                        </button>
                                    </form>
                                    <span class="text-gray-300 mx-2">|</span>
                                @elseif(!$sy->is_active && $sy->is_locked)
                                    <span class="text-gray-400 font-semibold"
                                        title="Tahun ajaran terkunci tidak bisa diaktifkan.">
                                        Tidak bisa diaktifkan
                                    </span>
                                    <span class="text-gray-300 mx-2">|</span>
                                @endif

                                {{-- Detail --}}
                                <a href="{{ route('school-years.show', $sy) }}"
                                    class="text-navy-500 hover:text-navy-700 font-semibold">
                                    Detail
                                </a>

                                <span class="text-gray-300 mx-2">|</span>

                                {{-- Edit/Hapus: disembunyikan kalau terkunci --}}
                                @if ($canEditDelete)
                                    <a href="{{ route('school-years.edit', $sy) }}"
                                        class="text-gray-700 hover:text-gray-900 font-semibold">
                                        Edit
                                    </a>

                                    <span class="text-gray-300 mx-2">|</span>

                                    <form method="POST" action="{{ route('school-years.destroy', $sy) }}"
                                        class="inline" onsubmit="return confirm('Hapus tahun ajaran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:text-red-800 font-semibold">
                                            Hapus
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 font-semibold">Terkunci</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                Belum ada data.
                            </td>
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
