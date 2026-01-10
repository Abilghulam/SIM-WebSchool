{{-- resources/views/enrollments/promote.blade.php --}}

@php
    // biar aman kalau controller belum kirim objectnya
    $fromYearId = $fromYearId ?? request('from_year_id');
    $toYearId = $toYearId ?? request('to_year_id');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">
                    Promosi Tahun Ajaran
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Naik kelas otomatis berdasarkan mapping kelas asal → kelas tujuan (grade + 1). Kelas 12 otomatis
                    diluluskan.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash message --}}
            @if (session('success'))
                <x-ui.alert type="success" :message="session('success')" />
            @endif

            @if (session('error'))
                <x-ui.alert type="danger" :message="session('error')" />
            @endif

            @if ($errors->any())
                <x-ui.alert type="danger" message="Validasi gagal. Periksa input promosi." />
            @endif

            {{-- Filter TA --}}
            <x-ui.card title="Pilih Tahun Ajaran" subtitle="Tentukan TA sumber (asal) dan TA tujuan (baru).">
                <form method="GET" action="{{ route('enrollments.promote.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div>
                            <x-ui.label for="from_year_id" value="Tahun Ajaran Asal" />
                            <x-ui.select name="from_year_id" id="from_year_id">
                                @foreach ($schoolYears as $sy)
                                    <option value="{{ $sy->id }}" @selected((int) $fromYearId === (int) $sy->id)>
                                        {{ $sy->name }} @if ($sy->is_active)
                                            (Aktif)
                                        @endif
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </div>

                        <div>
                            <x-ui.label for="to_year_id" value="Tahun Ajaran Tujuan" />
                            <x-ui.select name="to_year_id" id="to_year_id">
                                <option value="">-- pilih --</option>
                                @foreach ($schoolYears as $sy)
                                    <option value="{{ $sy->id }}" @selected((int) $toYearId === (int) $sy->id)>
                                        {{ $sy->name }} @if ($sy->is_active)
                                            (Aktif)
                                        @endif
                                    </option>
                                @endforeach
                            </x-ui.select>
                            <p class="text-xs text-gray-500 mt-1">
                                Pilih TA tujuan agar mapping otomatis terisi.
                            </p>
                        </div>

                    </div>

                    <div class="flex items-center gap-2">
                        <x-ui.button variant="primary" type="submit">
                            Tampilkan Mapping
                        </x-ui.button>

                        @if ($fromYearId || $toYearId)
                            <a href="{{ route('enrollments.promote.index') }}">
                                <x-ui.button variant="secondary" type="button">
                                    Reset
                                </x-ui.button>
                            </a>
                        @endif
                    </div>
                </form>
            </x-ui.card>

            {{-- Mapping --}}
            <x-ui.card title="Mapping Kelas (Asal → Tujuan)"
                subtitle="Kelas tujuan wajib grade + 1 dari kelas asal. Kelas 12 akan diluluskan otomatis.">
                @if (!$fromYearId || !$toYearId)
                    <div class="text-sm text-gray-600">
                        Pilih TA asal dan TA tujuan terlebih dahulu.
                    </div>
                @elseif ($fromClassrooms->isEmpty())
                    <div class="text-sm text-gray-600">
                        Tidak ada enrollment aktif di TA asal.
                    </div>
                @else
                    <form method="POST" action="{{ route('enrollments.promote.store') }}" class="space-y-4">
                        @csrf

                        <input type="hidden" name="from_year_id" value="{{ $fromYearId }}">
                        <input type="hidden" name="to_year_id" value="{{ $toYearId }}">

                        <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600">
                            <span>TA Asal: <strong class="text-gray-900">{{ $fromYear?->name }}</strong></span>
                            <span class="text-gray-300">•</span>
                            <span>TA Tujuan: <strong class="text-gray-900">{{ $toYear?->name }}</strong></span>
                        </div>

                        {{-- PREVIEW --}}
                        <div class="mb-4">
                            <x-ui.card title="Preview Jumlah Siswa (Sebelum Promosi)"
                                subtitle="Ringkasan siswa aktif di TA asal dan proyeksi distribusi ke kelas tujuan berdasarkan mapping otomatis.">

                                <div class="flex flex-wrap items-center gap-3 text-sm text-gray-700">
                                    <div>
                                        Total siswa aktif TA asal:
                                        <x-ui.badge variant="gray">{{ $totalFromStudents ?? 0 }}</x-ui.badge>
                                    </div>

                                    <div class="text-gray-300">•</div>

                                    <div>
                                        Diproyeksikan lulus (kelas 12):
                                        <x-ui.badge variant="green">{{ $graduateCount ?? 0 }}</x-ui.badge>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <x-ui.table>
                                        <x-slot:head>
                                            <tr>
                                                <th class="px-6 py-4 text-left font-semibold">Kelas Asal</th>
                                                <th class="px-6 py-4 text-left font-semibold">Jumlah Siswa</th>
                                                <th class="px-6 py-4 text-left font-semibold">Kelas Tujuan</th>
                                                <th class="px-6 py-4 text-left font-semibold">Proyeksi di Tujuan</th>
                                            </tr>
                                        </x-slot:head>

                                        @foreach ($fromClassrooms as $c)
                                            @php
                                                $countFrom = (int) ($fromCounts[$c->id] ?? 0);
                                                $toId = $defaultMap[$c->id] ?? null;

                                                $toClass = $toId ? $toClassrooms->firstWhere('id', $toId) : null;
                                                $countToProjected = $toId
                                                    ? (int) ($toProjectedCounts[$toId] ?? 0)
                                                    : null;

                                                $isGrade12 = (int) $c->grade_level >= 12;
                                            @endphp

                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 font-semibold text-gray-900 whitespace-nowrap">
                                                    {{ $c->name }}
                                                </td>

                                                <td class="px-6 py-4">
                                                    <x-ui.badge variant="gray">{{ $countFrom }}</x-ui.badge>
                                                </td>

                                                <td class="px-6 py-4 text-gray-700">
                                                    @if ($isGrade12)
                                                        <x-ui.badge variant="green">Lulus</x-ui.badge>
                                                    @else
                                                        {{ $toClass?->name ?? '-' }}
                                                    @endif
                                                </td>

                                                <td class="px-6 py-4 text-gray-700">
                                                    @if ($isGrade12)
                                                        <span class="text-gray-500">-</span>
                                                    @else
                                                        <x-ui.badge
                                                            variant="gray">{{ $countToProjected ?? 0 }}</x-ui.badge>
                                                        <span class="text-xs text-gray-500 ml-2">
                                                            (akumulasi dari semua kelas asal yang menuju kelas ini)
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </x-ui.table>
                                </div>
                            </x-ui.card>
                        </div>

                        <x-ui.table>
                            <x-slot:head>
                                <tr>
                                    <th class="px-6 py-4 text-left font-semibold">Kelas Asal</th>
                                    <th class="px-6 py-4 text-left font-semibold">Tingkat</th>
                                    <th class="px-6 py-4 text-left font-semibold">Jurusan</th>
                                    <th class="px-6 py-4 text-left font-semibold">Kelas Tujuan</th>
                                    <th class="px-6 py-4 text-left font-semibold">Catatan</th>
                                </tr>
                            </x-slot:head>

                            @foreach ($fromClassrooms as $c)
                                @php
                                    $isGrade12 = (int) $c->grade_level >= 12;
                                    $selected = old("map.$c->id", $defaultMap[$c->id] ?? null);

                                    // validasi: hanya boleh grade+1
                                    $allowedTargets = $toClassrooms
                                        ->where('grade_level', (int) $c->grade_level + 1)
                                        ->values();
                                @endphp

                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-semibold text-gray-900 whitespace-nowrap">
                                        {{ $c->name }}
                                    </td>

                                    <td class="px-6 py-4 text-gray-700 whitespace-nowrap">
                                        {{ $c->grade_level }}
                                    </td>

                                    <td class="px-6 py-4 text-gray-700">
                                        {{ $c->major?->name ?? '-' }}
                                    </td>

                                    <td class="px-6 py-4">
                                        @if ($isGrade12)
                                            <input type="hidden" name="map[{{ $c->id }}]" value="">
                                            <x-ui.badge variant="green">Lulus</x-ui.badge>
                                        @else
                                            <x-ui.select name="map[{{ $c->id }}]" class="w-full">
                                                <option value="">-- pilih kelas tujuan --</option>

                                                @foreach ($allowedTargets as $t)
                                                    <option value="{{ $t->id }}" @selected((string) $selected === (string) $t->id)>
                                                        {{ $t->name }} ({{ $t->major?->name ?? '-' }})
                                                    </option>
                                                @endforeach
                                            </x-ui.select>

                                            @if ($allowedTargets->isEmpty())
                                                <div class="text-xs text-red-600 mt-1">
                                                    Tidak ada kelas tujuan grade {{ (int) $c->grade_level + 1 }} di TA
                                                    tujuan.
                                                </div>
                                            @endif
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 text-gray-600">
                                        @if ($isGrade12)
                                            Otomatis lulus, tidak dibuat enrollment baru.
                                        @else
                                            Hanya kelas grade {{ (int) $c->grade_level + 1 }}.
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            <x-slot:footer>
                                <div class="flex items-center justify-end gap-2">
                                    <x-ui.button type="submit" variant="primary"
                                        onclick="return confirm('Yakin menjalankan promosi? Enrollment TA asal akan dinonaktifkan dan dibuatkan enrollment baru di TA tujuan.')">
                                        Proses Promosi
                                    </x-ui.button>
                                </div>
                            </x-slot:footer>
                        </x-ui.table>
                    </form>
                @endif
            </x-ui.card>

        </div>
    </div>
</x-app-layout>
