<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Dashboard Wali Kelas</h2>
            <p class="text-sm text-gray-500 mt-1">
                Ringkasan cepat untuk wali kelas.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-ui.flash />

            {{-- TOP SUMMARY CARDS --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <x-ui.card title="Tahun Ajaran Aktif">
                    <div class="text-lg font-semibold">
                        {{ $activeSchoolYear?->name ?? '-' }}
                    </div>
                </x-ui.card>

                <x-ui.card title="Kelas Diampu">
                    <div class="text-lg font-semibold">
                        {{ $classroom?->name ?? '-' }}
                    </div>
                </x-ui.card>

                <x-ui.card title="Jurusan">
                    <div class="text-base font-semibold">
                        {{ $stats['major'] ?? '-' }}
                    </div>
                </x-ui.card>

                <x-ui.card title="Total Siswa (Aktif)">
                    <div class="text-3xl font-bold">
                        {{ $stats['students'] ?? 0 }}
                    </div>
                </x-ui.card>
            </div>

            {{-- QUICK ACTION --}}
            <x-ui.card title="Aksi Cepat" subtitle="Lihat daftar siswa di kelas yang kamu ampu.">
                <a href="{{ route('my-class.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">
                    Lihat Siswa Kelas Saya →
                </a>
            </x-ui.card>

            {{-- CHARTS --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <x-ui.card title="Gender Siswa (Kelas Saya)">
                    <canvas id="studentsByGender"></canvas>
                </x-ui.card>

                <x-ui.card title="Status Siswa (Kelas Saya)">
                    <canvas id="studentsByStatus"></canvas>
                </x-ui.card>
            </div>
        </div>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const makeChart = (id, type, labels, data) => {
            new Chart(document.getElementById(id), {
                type,
                data: {
                    labels,
                    datasets: [{
                        data,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        };

        makeChart(
            'studentsByGender',
            'doughnut',
            @json($studentsByGender->pluck('label')),
            @json($studentsByGender->pluck('value'))
        );

        makeChart(
            'studentsByStatus',
            'bar',
            @json($studentsByStatus->pluck('label')),
            @json($studentsByStatus->pluck('value'))
        );
    </script>
</x-app-layout>
