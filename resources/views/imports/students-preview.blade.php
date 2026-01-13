<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Preview Import Siswa</h2>
                <p class="text-sm text-gray-500 mt-1">Cek sample + error. Kalau oke, lanjut commit.</p>
            </div>

            <a href="{{ route('imports.students.create') }}">
                <x-ui.button variant="secondary">← Kembali</x-ui.button>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-ui.flash />

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <x-ui.card title="Total Baris">
                    <div class="text-3xl font-bold">{{ $result['stats']['total_rows'] }}</div>
                </x-ui.card>
                <x-ui.card title="Valid">
                    <div class="text-3xl font-bold">{{ $result['stats']['valid_rows'] }}</div>
                </x-ui.card>
                <x-ui.card title="Invalid">
                    <div class="text-3xl font-bold">{{ $result['stats']['invalid_rows'] }}</div>
                </x-ui.card>

                <x-ui.card title="Konfigurasi">
                    <div class="text-sm font-semibold text-gray-900">
                        {{ $options['mode'] }} • {{ $options['strategy'] }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1 space-y-1">
                        <div>Default TA: <span
                                class="font-semibold">{{ $options['default_school_year_id'] ?? '-' }}</span></div>
                        <div>Default Kelas: <span
                                class="font-semibold">{{ $options['default_classroom_id'] ?? '-' }}</span></div>
                    </div>
                </x-ui.card>
            </div>

            <x-ui.card title="Sample Data (maks 20 baris)">
                <div class="overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">NIS</th>
                                <th class="py-2 pr-4">Nama</th>
                                <th class="py-2 pr-4">Gender</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-4">TA (id)</th>
                                <th class="py-2 pr-4">Kelas (id)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($result['preview'] as $r)
                                @php
                                    $sy = $r['school_year_id'] ?? ($options['default_school_year_id'] ?? null);
                                    $cl = $r['classroom_id'] ?? ($options['default_classroom_id'] ?? null);
                                @endphp
                                <tr class="border-t">
                                    <td class="py-2 pr-4">{{ $r['nis'] }}</td>
                                    <td class="py-2 pr-4 font-semibold text-gray-900">{{ $r['full_name'] }}</td>
                                    <td class="py-2 pr-4">{{ $r['gender'] ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ $r['status'] ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ $sy ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ $cl ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-gray-500">
                                        Tidak ada sample (mungkin semua baris invalid).
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>

            <x-ui.card title="Error (maks 50)" subtitle="Perbaiki file jika ada error, lalu upload ulang.">
                @if (empty($result['errors']))
                    <div class="text-sm text-gray-700">
                        Tidak ada error. ✅ Kamu bisa commit.
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($result['errors'] as $e)
                            <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                                <div class="text-sm font-semibold text-red-700">Baris {{ $e['line'] }}</div>
                                <ul class="mt-2 text-sm text-red-700 list-disc pl-5 space-y-1">
                                    @foreach ($e['errors'] as $msg)
                                        <li>{{ $msg }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach

                        @if ($result['has_more_errors'])
                            <div class="text-xs text-gray-500">
                                Masih ada error lain, tampilannya dipotong.
                            </div>
                        @endif
                    </div>
                @endif
            </x-ui.card>

            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('imports.students.commit') }}" data-loading-scope>
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <x-ui.button variant="primary" type="submit" :disabled="$result['stats']['valid_rows'] === 0" data-loading-text="Menyimpan...">
                        Commit Import
                    </x-ui.button>
                </form>

                @if ($result['stats']['valid_rows'] === 0)
                    <div class="text-sm text-gray-500">
                        Tidak ada baris valid untuk di-commit.
                    </div>
                @elseif ($result['stats']['invalid_rows'] > 0)
                    <div class="text-sm text-gray-500 ms-auto">
                        Ada error: commit tetap bisa, tapi baris invalid akan di-skip.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
