@php
    $statusBadge = function (?string $status) {
        return match ($status) {
            'success' => 'green',
            'failed' => 'red',
            default => 'gray',
        };
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">Aktivitas Promosi</h2>
                <p class="text-sm text-gray-500 mt-1">Riwayat eksekusi promote siswa (audit trail).</p>
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

            @if (session('warning'))
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg">
                    {{ session('warning') }}
                </div>
            @endif

            {{-- Filter --}}
            <x-ui.card title="Filter" subtitle="Cari dan saring aktivitas promosi siswa">
                <form method="GET" action="{{ route('enrollments.promotions.index') }}"
                    class="grid grid-cols-1 md:grid-cols-12 gap-4">

                    <div class="md:col-span-4">
                        <x-ui.input label="Cari" name="search" value="{{ request('search') }}"
                            placeholder="Nama TA atau nama eksekutor" />
                    </div>

                    <div class="md:col-span-3">
                        <x-ui.select label="Tahun Ajaran Asal" name="from_school_year_id">
                            <option value="">Semua</option>
                            @foreach ($schoolYears as $sy)
                                <option value="{{ $sy->id }}" @selected((string) request('from_school_year_id') === (string) $sy->id)>
                                    {{ $sy->name }} @if ($sy->is_active)
                                        (Aktif)
                                    @endif
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div class="md:col-span-3">
                        <x-ui.select label="Tahun Ajaran Tujuan" name="to_school_year_id">
                            <option value="">Semua</option>
                            @foreach ($schoolYears as $sy)
                                <option value="{{ $sy->id }}" @selected((string) request('to_school_year_id') === (string) $sy->id)>
                                    {{ $sy->name }} @if ($sy->is_active)
                                        (Aktif)
                                    @endif
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div class="md:col-span-2">
                        <x-ui.select label="Status" name="status">
                            @foreach ($statusOptions as $val => $label)
                                <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div class="md:col-span-12 flex items-center gap-2 pt-1">
                        <x-ui.button variant="primary" type="submit">Terapkan</x-ui.button>

                        <a href="{{ route('enrollments.promotions.index') }}">
                            <x-ui.button variant="secondary">Reset</x-ui.button>
                        </a>

                        <div class="text-sm text-gray-500 ms-auto">
                            Total: <span class="font-semibold text-gray-900">{{ $promotions->total() }}</span>
                        </div>
                    </div>
                </form>
            </x-ui.card>

            {{-- Table --}}
            <x-ui.card title="Riwayat Promote" subtitle="Klik baris pada table untuk melihat detail Riwayat promosi.">
                <x-ui.table>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Waktu</th>
                            <th class="px-6 py-4 text-left font-semibold">Asal → Tujuan</th>
                            <th class="px-6 py-4 text-left font-semibold">Eksekutor</th>
                            <th class="px-6 py-4 text-left font-semibold">Status</th>
                            <th class="px-6 py-4 text-right font-semibold">Total Siswa</th>
                            <th class="px-6 py-4 text-right font-semibold">Naik Kelas</th>
                            <th class="px-6 py-4 text-right font-semibold">Lulus</th>
                            <th class="px-6 py-4 text-right font-semibold">Tidak Naik</th>
                        </tr>
                    </x-slot:head>

                    @forelse($promotions as $p)
                        @php
                            $detailUrl = route('enrollments.promotions.show', $p);
                        @endphp

                        <tr class="hover:bg-gray-50 cursor-pointer transition" data-row-link="{{ $detailUrl }}"
                            role="link" tabindex="0"
                            aria-label="Lihat detail promote {{ $p->fromYear?->name ?? '-' }} ke {{ $p->toYear?->name ?? '-' }}"
                            title="Klik untuk lihat detail">
                            <td class="px-6 py-4 text-gray-700 whitespace-nowrap">
                                {{ $p->executed_at?->format('d-m-Y H:i') ?? $p->created_at?->format('d-m-Y H:i') }}
                            </td>

                            <td class="px-6 py-4 text-gray-900">
                                <div class="font-semibold">
                                    {{ $p->fromYear?->name ?? '-' }} → {{ $p->toYear?->name ?? '-' }}
                                </div>
                            </td>

                            <td class="px-6 py-4 text-gray-700">
                                {{ $p->executor?->name ?? '-' }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-ui.badge :variant="$statusBadge($p->status)">
                                    {{ strtoupper($p->status ?? '-') }}
                                </x-ui.badge>
                                @if ($p->status === 'failed' && $p->error_message)
                                    <div class="text-xs text-red-600 mt-1">
                                        {{ \Illuminate\Support\Str::limit($p->error_message, 80) }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-right text-gray-900">
                                {{ (int) $p->total_students }}
                            </td>
                            <td class="px-6 py-4 text-right text-gray-900">
                                {{ (int) $p->moved_students }}
                            </td>
                            <td class="px-6 py-4 text-right text-gray-900">
                                {{ (int) $p->graduated_students }}
                            </td>
                            <td class="px-6 py-4 text-right text-gray-900">
                                {{ (int) $p->skipped_students }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                                Belum ada log promote.
                            </td>
                        </tr>
                    @endforelse

                    <x-slot:footer>
                        {{ $promotions->links() }}
                    </x-slot:footer>
                </x-ui.table>
            </x-ui.card>

        </div>
    </div>

    {{-- JS ringan: klik baris / Enter / Space untuk buka detail --}}
    <script>
        (function() {
            const rows = document.querySelectorAll('tr[data-row-link]');
            if (!rows.length) return;

            function go(url) {
                if (!url) return;
                window.location.href = url;
            }

            rows.forEach((row) => {
                const url = row.getAttribute('data-row-link');

                row.addEventListener('click', (e) => {
                    // kalau suatu saat ada element interaktif di dalam row, biar ga "nabrak"
                    const tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
                    if (['a', 'button', 'input', 'select', 'textarea', 'label'].includes(tag)) return;

                    go(url);
                });

                row.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        go(url);
                    }
                });
            });
        })();
    </script>
</x-app-layout>
