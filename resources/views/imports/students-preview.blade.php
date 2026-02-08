<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Preview Import Data Siswa</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Preview hasil pengecekan data siswa berdasarkan file yang diunggah
                </p>
            </div>

            <a href="{{ route('imports.students.create') }}">
                <x-ui.button variant="secondary">← Kembali</x-ui.button>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-ui.flash />

            {{-- KPI + Pengaturan --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Total Siswa --}}
                <x-ui.card title="Total Siswa">
                    <div class="flex items-start justify-between gap-3">
                        <div class="rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-700 p-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-users-round">
                                <path d="M18 21a8 8 0 0 0-16 0" />
                                <circle cx="10" cy="8" r="5" />
                                <path d="M22 20c0-3.37-2-6.5-4-8a5 5 0 0 0-.45-8.3" />
                            </svg>
                        </div>

                        <div class="text-right">
                            <div class="text-3xl font-bold text-gray-900">{{ data_get($result, 'stats.total_rows', 0) }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">Baris data siswa yang terbaca</div>
                        </div>
                    </div>
                </x-ui.card>

                {{-- Siap Diproses --}}
                <x-ui.card title="Siap Diproses">
                    <div class="flex items-start justify-between gap-3">
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 p-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-user-round-check">
                                <path d="M2 21a8 8 0 0 1 13.292-6" />
                                <circle cx="10" cy="8" r="5" />
                                <path d="m16 19 2 2 4-4" />
                            </svg>
                        </div>

                        <div class="text-right">
                            <div class="text-3xl font-bold text-gray-900">{{ data_get($result, 'stats.valid_rows', 0) }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">Baris data siswa yang siap disimpan</div>
                        </div>
                    </div>
                </x-ui.card>

                {{-- Perlu Perbaikan --}}
                <x-ui.card title="Perlu Perbaikan">
                    <div class="flex items-start justify-between gap-3">
                        <div class="rounded-xl border border-red-200 bg-red-50 text-red-700 p-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-user-round-x">
                                <path d="M2 21a8 8 0 0 1 11.873-7" />
                                <circle cx="10" cy="8" r="5" />
                                <path d="m17 17 5 5" />
                                <path d="m22 17-5 5" />
                            </svg>
                        </div>

                        <div class="text-right">
                            <div class="text-3xl font-bold text-gray-900">
                                {{ data_get($result, 'stats.invalid_rows', 0) }}</div>
                            <div class="text-xs text-gray-500 mt-1">Baris data siswa yang bermasalah</div>
                        </div>
                    </div>
                </x-ui.card>

                {{-- Pengaturan Import --}}
                <x-ui.card title="Pengaturan Import">
                    <div class="flex flex-wrap gap-2">
                        <span
                            class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $importSettings['mode_pill'] ?? '' }}">
                            {{ $importSettings['mode_label'] ?? '-' }}
                        </span>
                        <span
                            class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $importSettings['strategy_pill'] ?? '' }}">
                            {{ $importSettings['strategy_label'] ?? '-' }}
                        </span>
                    </div>

                    <div class="mt-3 space-y-2 text-sm">
                        <div
                            class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white px-3 py-2">
                            <div class="text-xs text-gray-500">Tahun Ajaran</div>
                            <div class="text-xs font-semibold text-gray-900 text-right">
                                {{ $importSettings['default_school_year_text'] ?? '-' }}
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white px-3 py-2">
                            <div class="text-xs text-gray-500">Kelas</div>
                            <div class="text-xs font-semibold text-gray-900 text-right">
                                {{ $importSettings['default_classroom_text'] ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 text-xs text-gray-500">
                        <span class="font-semibold text-gray-700">Tips:</span>
                        {{ $importSettings['tips'] ?? '-' }}
                    </div>
                </x-ui.card>
            </div>

            {{-- Preview --}}
            <x-ui.card title="Preview Data Siswa" subtitle="Klik baris merah untuk melihat detail errornya.">
                <div class="overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">NIS</th>
                                <th class="py-2 pr-4">Nama</th>
                                <th class="py-2 pr-4">Gender</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-4">Tahun Ajaran</th>
                                <th class="py-2 pr-4">Kelas</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($previewRows as $r)
                                @php
                                    $isError = (bool) ($r['is_error'] ?? false);
                                    $line = (int) ($r['line'] ?? 0);

                                    $rowClass = $isError
                                        ? 'border-t bg-red-50/60 cursor-pointer hover:bg-red-100/60 transition'
                                        : 'border-t';

                                    $cellMuted = $isError ? 'text-red-900' : '';

                                    $badgeClass = $isError
                                        ? 'border-red-200 bg-red-50 text-red-700'
                                        : 'border-gray-200 bg-gray-50 text-gray-700';

                                    $errorPillClass =
                                        'inline-flex items-center rounded-full border border-red-200 bg-white px-2 py-0.5 text-[11px] font-semibold text-red-700';
                                @endphp

                                <tr class="{{ $rowClass }}"
                                    @if ($isError && $line > 0) data-error-line="{{ $line }}"
                                        role="button"
                                        tabindex="0"
                                        aria-label="Lihat error baris {{ $line }}"
                                        title="Klik untuk lihat detail error" @endif>
                                    <td class="py-2 pr-4 {{ $cellMuted }}">{{ $r['nis'] ?? '-' }}</td>

                                    <td
                                        class="py-2 pr-4 font-semibold {{ $isError ? 'text-red-900' : 'text-gray-900' }}">
                                        {{ $r['full_name'] ?? '-' }}
                                    </td>

                                    <td class="py-2 pr-4 {{ $cellMuted }}">{{ $r['gender'] ?? '-' }}</td>
                                    <td class="py-2 pr-4 {{ $cellMuted }}">{{ $r['status'] ?? '-' }}</td>

                                    <td class="py-2 pr-4">
                                        <span
                                            class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold {{ $badgeClass }}">
                                            {{ $r['school_year_text'] ?? '-' }}
                                        </span>
                                    </td>

                                    {{-- Kelas + badge Error kecil di ujung kanan --}}
                                    <td class="py-2 pr-4">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold {{ $badgeClass }}">
                                                {{ $r['classroom_text'] ?? '-' }}
                                            </span>

                                            @if ($isError)
                                                <span class="ms-auto {{ $errorPillClass }}">Error</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-gray-500">
                                        Tidak ada data siswa yang berhasil diproses.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="text-xs text-gray-500 mt-3">
                    Catatan: baris berwarna merah menandakan ada error dan perlu perbaikan.
                </div>
            </x-ui.card>

            {{-- Error preview --}}
            @if (!empty($errorRows))
                {{-- wrapper supaya id pasti ada walau x-ui.card tidak meneruskan atribut --}}
                <div id="errorCard">
                    <x-ui.card title="Perlu Perbaikan"
                        subtitle="Klik baris merah di tabel untuk langsung lompat ke detail errornya.">
                        <div id="errorList" class="space-y-3">
                            @foreach ($errorRows as $e)
                                @php $line = (int) ($e['line'] ?? 0); @endphp

                                <div id="error-item-{{ $line }}" data-error-item="{{ $line }}"
                                    class="rounded-2xl border border-red-200 bg-red-50 p-4 transition">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="text-sm font-semibold text-red-800">
                                                Baris {{ $line ?: '-' }} perlu diperbaiki
                                            </div>
                                            <div class="text-xs text-red-700 mt-1">
                                                Periksa data pada baris ini di file import, lalu upload ulang.
                                            </div>
                                        </div>

                                        <span
                                            class="inline-flex items-center rounded-full border border-red-200 bg-white px-3 py-1 text-xs font-semibold text-red-700">
                                            Error
                                        </span>
                                    </div>

                                    <ul class="mt-3 text-sm text-red-800 list-disc pl-5 space-y-1">
                                        @foreach ($e['messages'] ?? [] as $msg)
                                            <li>{{ $msg }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach

                            @if (data_get($result, 'has_more_errors'))
                                <div class="text-xs text-gray-500">
                                    Masih ada error lainnya, tampilan daftar error dipersingkat.
                                </div>
                            @endif
                        </div>
                    </x-ui.card>
                </div>
            @endif

            {{-- Actions --}}
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('imports.students.commit') }}" data-loading-scope>
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <x-ui.button variant="primary" type="submit" :disabled="data_get($result, 'stats.valid_rows', 0) === 0" data-loading-text="Menyimpan...">
                        Simpan Hasil Import
                    </x-ui.button>
                </form>

                @if (data_get($result, 'stats.valid_rows', 0) === 0)
                    <div class="text-sm text-gray-500">
                        Belum ada baris yang bisa disimpan.
                    </div>
                @elseif (data_get($result, 'stats.invalid_rows', 0) > 0)
                    <div class="text-sm text-gray-500 ms-auto">
                        Ada baris yang bermasalah. Baris tersebut akan dilewati saat proses penyimpanan.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- CSS murni: anti Tailwind purge, highlight pasti tampil --}}
    <style>
        .import-error-highlight {
            background: rgba(254, 226, 226, 0.7);
            box-shadow: 0 0 0 2px rgba(248, 113, 113, .85), 0 0 0 8px rgba(248, 113, 113, .18);
            border-color: rgba(248, 113, 113, .85) !important;
        }

        @keyframes importErrorPulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.01);
            }
        }

        .import-error-pulse {
            animation: importErrorPulse 0.9s ease-in-out 2;
        }
    </style>

    {{-- JS: klik baris merah -> scroll ke error card + item terkait + highlight --}}
    <script>
        (function() {
            const errorCard = document.getElementById('errorCard');
            if (!errorCard) return;

            const H = 'import-error-highlight';
            const P = 'import-error-pulse';

            function clearHighlights() {
                document.querySelectorAll('[data-error-item]').forEach((el) => {
                    el.classList.remove(H);
                    el.classList.remove(P);
                });
            }

            function focusError(line) {
                line = String(line || '').trim();
                if (!line) return;

                const target = document.querySelector(`[data-error-item="${CSS.escape(line)}"]`);
                if (!target) return;

                // scroll ke card error (biar konteks kebaca)
                errorCard.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

                // lanjut scroll ke item error + highlight
                setTimeout(() => {
                    clearHighlights();

                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    target.classList.add(H);
                    target.classList.add(P);

                    // stop pulse lebih cepat, highlight lebih lama
                    setTimeout(() => target.classList.remove(P), 1200);
                    setTimeout(() => target.classList.remove(H), 4500);
                }, 250);
            }

            document.querySelectorAll('tr[data-error-line]').forEach((row) => {
                const line = row.getAttribute('data-error-line');
                row.addEventListener('click', () => focusError(line));
                row.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        focusError(line);
                    }
                });
            });
        })();
    </script>
</x-app-layout>
