<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900">Dashboard</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <x-ui.flash />

            {{-- STAT CARDS --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <x-ui.card title="Siswa Aktif">
                    <div class="text-3xl font-bold">{{ $stats['students'] }}</div>
                </x-ui.card>

                <x-ui.card title="Guru Aktif">
                    <div class="text-3xl font-bold">{{ $stats['teachers'] }}</div>
                </x-ui.card>

                <x-ui.card title="Jumlah Kelas">
                    <div class="text-3xl font-bold">{{ $stats['classrooms'] }}</div>
                </x-ui.card>

                <x-ui.card title="Tahun Ajaran Aktif">
                    <div class="text-lg font-semibold">
                        {{ $stats['activeSchoolYear'] ?? '-' }}
                    </div>
                </x-ui.card>
            </div>

            {{-- CHARTS --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <x-ui.card title="Siswa per Jurusan">
                    <canvas id="studentsByMajor"></canvas>
                </x-ui.card>

                <x-ui.card title="Gender Siswa">
                    <canvas id="studentsByGender"></canvas>
                </x-ui.card>

                <x-ui.card title="Status Kepegawaian Guru" class="lg:col-span-2">
                    <canvas id="teachersByEmployment"></canvas>
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
            'studentsByMajor',
            'bar',
            @json($studentsByMajor->pluck('label')),
            @json($studentsByMajor->pluck('value'))
        );

        makeChart(
            'studentsByGender',
            'doughnut',
            @json($studentsByGender->pluck('label')),
            @json($studentsByGender->pluck('value'))
        );

        makeChart(
            'teachersByEmployment',
            'bar',
            @json($teachersByEmployment->pluck('label')),
            @json($teachersByEmployment->pluck('value'))
        );
    </script>
</x-app-layout>
