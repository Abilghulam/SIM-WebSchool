{{-- resources/views/activity-logs/show.blade.php --}}
@php
    $ev = $activity->event ?? '-';

    $variant = match ($ev) {
        'created' => 'green',
        'updated' => 'blue',
        'deleted' => 'red',
        'bulk_placement_executed' => 'amber',
        default => 'gray',
    };

    $subjectType = $activity->subject_type ? class_basename((string) $activity->subject_type) : '-';
    $subjectId = $activity->subject_id ?? '-';

    // gabungkan keys old & attributes untuk tabel perubahan
    $keys = array_unique(array_merge(array_keys($old ?? []), array_keys($attributes ?? [])));

    $fmt = function ($v) {
        if ($v === null) {
            return '-';
        }
        if (is_bool($v)) {
            return $v ? 'true' : 'false';
        }
        if (is_scalar($v)) {
            return (string) $v;
        }
        return json_encode($v, JSON_UNESCAPED_UNICODE);
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-semibold text-gray-900 leading-tight">
                        Detail Activity Log
                    </h2>

                    <x-ui.badge :variant="$variant">{{ $ev }}</x-ui.badge>
                </div>

                <p class="text-sm text-gray-500 mt-1">
                    {{ $activity->created_at?->format('d-m-Y H:i:s') }} •
                    {{ $activity->causer?->name ?? '-' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('activity-logs.index') }}">
                    <x-ui.button variant="secondary">← Kembali</x-ui.button>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash --}}
            @include('components.ui.flash')

            {{-- Ringkasan --}}
            <x-ui.card title="Ringkasan" subtitle="Informasi umum aktivitas yang tercatat.">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <div class="text-xs text-gray-500">Waktu</div>
                        <div class="mt-1 font-semibold text-gray-900">
                            {{ $activity->created_at?->format('d-m-Y H:i:s') }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Pelaku</div>
                        <div class="mt-1 font-semibold text-gray-900">
                            {{ $activity->causer?->name ?? '-' }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $activity->causer_id ? "User ID: {$activity->causer_id}" : '' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Subject</div>
                        <div class="mt-1 font-semibold text-gray-900">
                            {{ $subjectType }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            ID: {{ $subjectId }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Log</div>
                        <div class="mt-1 font-semibold text-gray-900">
                            {{ $activity->log_name ?? '-' }}
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="text-xs text-gray-500">Deskripsi</div>
                    <div class="mt-1 text-gray-900">
                        {{ $activity->description ?? '-' }}
                    </div>
                </div>
            </x-ui.card>

            {{-- Perubahan --}}
            <x-ui.card title="Perubahan" subtitle="Perbandingan old vs new (jika tersedia).">
                @if (count($keys) > 0)
                    <x-ui.table>
                        <x-slot:head>
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold">Field</th>
                                <th class="px-6 py-4 text-left font-semibold">Old</th>
                                <th class="px-6 py-4 text-left font-semibold">New</th>
                            </tr>
                        </x-slot:head>

                        @foreach ($keys as $k)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-semibold text-gray-900 whitespace-nowrap">
                                    {{ $k }}
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    <code class="text-xs">{{ $fmt($old[$k] ?? null) }}</code>
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    <code class="text-xs">{{ $fmt($attributes[$k] ?? null) }}</code>
                                </td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                @else
                    <x-ui.alert type="info">
                        Tidak ada detail perubahan (properties old/attributes kosong).
                    </x-ui.alert>
                @endif
            </x-ui.card>

            {{-- Properties Raw --}}
            <x-ui.card title="Properties (Raw)" subtitle="Untuk audit/debug (JSON).">
                <pre class="text-xs bg-gray-50 border border-gray-200 rounded-xl p-4 overflow-auto">{{ json_encode($properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </x-ui.card>

        </div>
    </div>
</x-app-layout>
