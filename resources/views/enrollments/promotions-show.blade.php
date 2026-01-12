@php
    $statusBadge = match ($promotion->status ?? null) {
        'success' => 'green',
        'failed' => 'red',
        default => 'gray',
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">
                    Detail Log Promote
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $promotion->fromYear?->name ?? '-' }} → {{ $promotion->toYear?->name ?? '-' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('enrollments.promotions.index') }}">
                    <x-ui.button variant="secondary">Kembali</x-ui.button>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <x-ui.card title="Ringkasan Eksekusi">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <div class="text-sm text-gray-500">Waktu</div>
                        <div class="font-semibold text-gray-900">
                            {{ $promotion->executed_at?->format('d-m-Y H:i') ?? $promotion->created_at?->format('d-m-Y H:i') }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Eksekutor</div>
                        <div class="font-semibold text-gray-900">
                            {{ $promotion->executor?->name ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Status</div>
                        <div class="mt-1">
                            <x-ui.badge :variant="$statusBadge">
                                {{ strtoupper($promotion->status ?? '-') }}
                            </x-ui.badge>
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Catatan</div>
                        <div class="text-gray-700">
                            @if (($promotion->status ?? null) === 'failed' && $promotion->error_message)
                                {{ $promotion->error_message }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                    <div class="p-4 rounded-xl border border-gray-200">
                        <div class="text-sm text-gray-500">Total</div>
                        <div class="text-2xl font-bold text-gray-900">{{ (int) $promotion->total_students }}</div>
                    </div>
                    <div class="p-4 rounded-xl border border-gray-200">
                        <div class="text-sm text-gray-500">Moved</div>
                        <div class="text-2xl font-bold text-gray-900">{{ (int) $promotion->moved_students }}</div>
                    </div>
                    <div class="p-4 rounded-xl border border-gray-200">
                        <div class="text-sm text-gray-500">Graduated</div>
                        <div class="text-2xl font-bold text-gray-900">{{ (int) $promotion->graduated_students }}</div>
                    </div>
                    <div class="p-4 rounded-xl border border-gray-200">
                        <div class="text-sm text-gray-500">Skipped</div>
                        <div class="text-2xl font-bold text-gray-900">{{ (int) $promotion->skipped_students }}</div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Detail per Kelas" subtitle="Snapshot hasil promote untuk setiap mapping kelas.">
                <x-ui.table>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Kelas Asal</th>
                            <th class="px-6 py-4 text-left font-semibold">Kelas Tujuan</th>
                            <th class="px-6 py-4 text-right font-semibold">Aktif</th>
                            <th class="px-6 py-4 text-right font-semibold">Moved</th>
                            <th class="px-6 py-4 text-right font-semibold">Graduated</th>
                            <th class="px-6 py-4 text-right font-semibold">Skipped</th>
                        </tr>
                    </x-slot:head>

                    @forelse($promotion->items as $it)
                        @php
                            $fromName = $it->fromClassroom?->name ?? '-';
                            $toName = $it->toClassroom?->name ?? '(Lulus)';
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-900 font-semibold">
                                {{ $fromName }}
                                @if ($it->from_grade_level)
                                    <div class="text-xs text-gray-500 mt-1">Grade: {{ $it->from_grade_level }}</div>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-gray-900 font-semibold">
                                {{ $toName }}
                                @if ($it->to_grade_level)
                                    <div class="text-xs text-gray-500 mt-1">Grade: {{ $it->to_grade_level }}</div>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-right text-gray-900">{{ (int) $it->active_enrollments }}</td>
                            <td class="px-6 py-4 text-right text-gray-900">{{ (int) $it->moved_students }}</td>
                            <td class="px-6 py-4 text-right text-gray-900">{{ (int) $it->graduated_students }}</td>
                            <td class="px-6 py-4 text-right text-gray-900">{{ (int) $it->skipped_students }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                Tidak ada item log.
                            </td>
                        </tr>
                    @endforelse
                </x-ui.table>
            </x-ui.card>

            {{-- (Opsional) tampilkan mapping_json mentah --}}
            <x-ui.card title="Mapping (Snapshot)"
                subtitle="Snapshot mapping_json yang dipakai saat eksekusi (audit mentah).">
                <pre class="text-xs bg-gray-50 border border-gray-200 rounded-xl p-4 overflow-auto">{{ json_encode($promotion->mapping_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </x-ui.card>

        </div>
    </div>
</x-app-layout>
