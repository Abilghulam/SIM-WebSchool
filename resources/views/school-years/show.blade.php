<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">
                    Detail Tahun Ajaran: {{ $schoolYear->name }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Ringkasan status tahun ajaran dan data enrollment per kelas.
                </p>
            </div>

            <a href="{{ route('school-years.index') }}">
                <x-ui.button variant="secondary">Kembali</x-ui.button>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <x-ui.card title="Informasi Tahun Ajaran">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <div class="text-sm text-gray-500">Periode</div>
                        <div class="font-semibold text-gray-900">
                            {{ $schoolYear->start_date ? $schoolYear->start_date->format('d-m-Y') : '-' }}
                            s/d
                            {{ $schoolYear->end_date ? $schoolYear->end_date->format('d-m-Y') : '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Status</div>
                        <div class="flex items-center gap-2 mt-1">
                            <x-ui.badge :variant="$schoolYear->is_active ? 'green' : 'gray'">
                                {{ $schoolYear->is_active ? 'Aktif' : 'Nonaktif' }}
                            </x-ui.badge>

                            @if ($schoolYear->is_locked)
                                <x-ui.badge variant="orange">Locked</x-ui.badge>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Catatan</div>
                        <div class="text-gray-700">
                            @if ($schoolYear->is_locked)
                                TA ini sudah terkunci (hasil promote) dan tidak bisa diubah.
                            @else
                                TA masih bisa diubah.
                            @endif
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Enrollment per Kelas"
                subtitle="Total enrollment dan jumlah yang aktif pada tahun ajaran ini.">
                @if ($classroomStats->isEmpty())
                    <div class="text-gray-500 py-6">Belum ada enrollment pada tahun ajaran ini.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kelas
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                                        Jurusan</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Aktif
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach ($classroomStats as $row)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 font-semibold text-gray-900">
                                            {{ optional($row->classroom)->name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ optional(optional($row->classroom)->major)->name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-gray-900">
                                            {{ (int) $row->active_enrollments }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-gray-700">
                                            {{ (int) $row->total_enrollments }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-ui.card>

        </div>
    </div>
</x-app-layout>
