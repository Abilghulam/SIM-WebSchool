@php
    use App\Support\ActivityUiFormatter;

    $ev = $activity->event ?? '-';
    $meta = ActivityUiFormatter::eventMeta($ev);

    $subjectTypeFull = (string) ($activity->subject_type ?? '');
    $subjectType = $subjectTypeFull ? class_basename($subjectTypeFull) : '-';
    $subjectId = $activity->subject_id ?? '-';

    $subLabel = ActivityUiFormatter::subjectLabel($activity);
    $subUrl = ActivityUiFormatter::subjectUrl($activity);

    $sentence = ActivityUiFormatter::auditSentence($activity);

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

    $truncate = function (?string $s, int $n = 70) {
        $s = (string) ($s ?? '');
        return mb_strlen($s) <= $n ? $s : mb_substr($s, 0, $n) . '…';
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-semibold text-gray-900 leading-tight">Detail Activity Log</h2>
                    <x-ui.badge :variant="$meta['variant']">{{ $meta['label'] }}</x-ui.badge>
                </div>

                <p class="text-sm text-gray-500 mt-1">
                    {{ $activity->created_at?->format('d-m-Y H:i:s') }} • {{ $activity->causer?->name ?? 'Sistem' }}
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

            @include('components.ui.flash')

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
                        <div class="mt-1 font-semibold text-gray-900">{{ $activity->causer?->name ?? 'Sistem' }}</div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $activity->causer_id ? "User ID: {$activity->causer_id}" : '' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Subject</div>
                        <div class="mt-1 font-semibold text-gray-900">
                            @if ($subLabel && $subUrl)
                                <a href="{{ $subUrl }}"
                                    class="text-navy-500 hover:text-navy-700">{{ $subLabel }}</a>
                            @elseif($subLabel)
                                {{ $subLabel }}
                            @else
                                {{ $subjectType }}
                            @endif
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $subjectType }} • ID: {{ $subjectId }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500">Event</div>
                        <div class="mt-1 font-semibold text-gray-900">{{ $meta['label'] }}</div>
                        <div class="text-xs text-gray-500 mt-1">{{ $activity->event }}</div>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="text-xs text-gray-500">Keterangan (Audit)</div>
                    <div class="mt-1 text-gray-900">{{ $sentence }}</div>
                </div>
            </x-ui.card>

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
                            @php
                                $oldVal = $fmt($old[$k] ?? null);
                                $newVal = $fmt($attributes[$k] ?? null);
                                $oldShort = $truncate($oldVal, 80);
                                $newShort = $truncate($newVal, 80);
                                $isChanged = $oldVal !== $newVal;
                            @endphp

                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-semibold text-gray-900 whitespace-nowrap">{{ $k }}
                                </td>

                                <td class="px-6 py-4 text-gray-700">
                                    <code class="text-xs">{{ $oldShort }}</code>
                                    @if ($oldShort !== $oldVal)
                                        <div class="mt-2 text-xs text-gray-500 break-all">{{ $oldVal }}</div>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-gray-700">
                                    @if ($isChanged)
                                        <div class="inline-flex mb-2"><x-ui.badge variant="blue">changed</x-ui.badge>
                                        </div>
                                    @endif
                                    <code class="text-xs">{{ $newShort }}</code>
                                    @if ($newShort !== $newVal)
                                        <div class="mt-2 text-xs text-gray-500 break-all">{{ $newVal }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                @else
                    <x-ui.alert type="info">Tidak ada detail perubahan (properties old/attributes
                        kosong).</x-ui.alert>
                @endif
            </x-ui.card>

            <x-ui.card title="Properties (Raw)" subtitle="Untuk audit/debug (JSON).">
                <pre class="text-xs bg-gray-50 border border-gray-200 rounded-xl p-4 overflow-auto">{{ json_encode($properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </x-ui.card>

        </div>
    </div>
</x-app-layout>
