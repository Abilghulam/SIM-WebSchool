{{-- resources/views/activity-logs/index.blade.php --}}
@php
    $user = auth()->user();

    // Badge variant untuk event
    $eventBadge = function (?string $ev) {
        return match ($ev) {
            'created' => 'green',
            'updated' => 'blue',
            'deleted' => 'red',
            // event custom (contoh)
            'school_year_activated' => 'amber',
            'bulk_placement_executed' => 'amber',
            'enrollments_promoted' => 'amber',

            'student_document_uploaded', 'teacher_document_uploaded' => 'green',
            'student_document_deleted', 'teacher_document_deleted' => 'red',

            default => 'gray',
        };
    };

    // helper: ringkas subject type
    $subjectShort = function ($type) {
        return $type ? class_basename((string) $type) : '-';
    };

    // label subject yang "manusiawi"
    $subjectLabel = function ($a) use ($subjectShort) {
        $type = (string) ($a->subject_type ?? '');
        $s = $a->subject; // butuh ->with('subject')

        if (!$s) {
            $short = $subjectShort($type);
            $id = $a->subject_id ?? '-';
            return "{$short} #{$id}";
        }

        return match (class_basename($type)) {
            'Student' => trim(($s->nis ?? '-') . ' - ' . ($s->full_name ?? '')),
            'Teacher' => trim(($s->nip ?? '-') . ' - ' . ($s->full_name ?? '')),
            'SchoolYear' => (string) ($s->name ?? 'SchoolYear'),
            'Classroom' => (string) ($s->name ?? 'Classroom'),
            'Major' => trim(($s->code ?? '') . ($s->code ? ' - ' : '') . ($s->name ?? 'Major')),
            'HomeroomAssignment' => 'Homeroom Assignment',
            'StudentDocument', 'TeacherDocument' => (string) ($s->file_name ?? ($s->title ?? 'Dokumen')),
            default => (string) ($s->name ?? ($s->title ?? $subjectShort($type) . ' #' . ($a->subject_id ?? '-'))),
        };
    };

    // optional: link subject ke halaman detail (kalau route ada)
    $subjectUrl = function ($a) {
        $type = (string) ($a->subject_type ?? '');
        $s = $a->subject;

        if (!$s) {
            return null;
        }

        return match (class_basename($type)) {
            'Student' => route('students.show', $s),
            'Teacher' => route('teachers.show', $s),
            'SchoolYear' => route('school-years.show', $s),
            'Classroom' => route('classrooms.edit', $s), // kamu belum punya show, jadi arahkan ke edit
            'Major' => route('majors.edit', $s), // sama, arahkan ke edit
            default => null,
        };
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">
                    Activity Log
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Audit trail aktivitas penting sistem (domain). Hanya admin yang dapat mengakses.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('activity-logs.index') }}">
                    <x-ui.button variant="secondary">Reset</x-ui.button>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash --}}
            @include('components.ui.flash')

            {{-- Filter --}}
            <x-ui.card title="Filter" subtitle="Saring log berdasarkan event, subject, dan rentang tanggal.">
                <form method="GET" action="{{ route('activity-logs.index') }}"
                    class="grid grid-cols-1 md:grid-cols-12 gap-4">

                    <div class="md:col-span-4">
                        <x-ui.select label="Event" name="event">
                            <option value="">Semua</option>
                            @foreach ($events ?? [] as $ev)
                                <option value="{{ $ev }}" @selected(request('event') === $ev)>
                                    {{ $ev }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div class="md:col-span-4">
                        <x-ui.select label="Subject Type" name="subject_type">
                            <option value="">Semua</option>
                            @foreach ($subjectTypes ?? [] as $st)
                                <option value="{{ $st }}" @selected(request('subject_type') === $st)>
                                    {{ class_basename((string) $st) }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div class="md:col-span-2">
                        <x-ui.input label="Dari" type="date" name="date_from" value="{{ request('date_from') }}" />
                    </div>

                    <div class="md:col-span-2">
                        <x-ui.input label="Sampai" type="date" name="date_to" value="{{ request('date_to') }}" />
                    </div>

                    <div class="md:col-span-12 flex items-center gap-2 pt-1">
                        <x-ui.button variant="primary" type="submit">Terapkan</x-ui.button>

                        <a href="{{ route('activity-logs.index') }}">
                            <x-ui.button variant="secondary">Reset</x-ui.button>
                        </a>

                        <div class="text-sm text-gray-500 ms-auto">
                            Total: <span class="font-semibold text-gray-900">{{ $activities->total() }}</span>
                        </div>
                    </div>
                </form>
            </x-ui.card>

            {{-- Table --}}
            <x-ui.card title="Daftar Activity" subtitle="Klik Detail untuk melihat perubahan/properties.">
                <x-ui.table>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Waktu</th>
                            <th class="px-6 py-4 text-left font-semibold">Pelaku</th>
                            <th class="px-6 py-4 text-left font-semibold">Event</th>
                            <th class="px-6 py-4 text-left font-semibold">Subject</th>
                            <th class="px-6 py-4 text-left font-semibold">Deskripsi</th>
                            <th class="px-6 py-4 text-right font-semibold">Aksi</th>
                        </tr>
                    </x-slot:head>

                    @forelse ($activities as $a)
                        @php
                            $ev = $a->event ?? '-';
                            $variant = $eventBadge($a->event);
                            $causerName = $a->causer?->name ?? '-';

                            $subLabel = $subjectLabel($a);
                            $subUrl = $subjectUrl($a);
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                {{ $a->created_at?->format('d-m-Y H:i') }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $causerName }}</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $a->causer_id ? "User ID: {$a->causer_id}" : '' }}
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-ui.badge :variant="$variant">
                                    {{ $ev }}
                                </x-ui.badge>
                            </td>

                            <td class="px-6 py-4 text-gray-700">
                                <div class="font-semibold text-gray-900">
                                    @if ($subUrl)
                                        <a href="{{ $subUrl }}" class="text-navy-500 hover:text-navy-700">
                                            {{ $subLabel }}
                                        </a>
                                    @else
                                        {{ $subLabel }}
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $subjectShort($a->subject_type) }} • ID: {{ $a->subject_id ?? '-' }}
                                </div>
                            </td>

                            <td class="px-6 py-4 text-gray-700">
                                <div class="line-clamp-2">
                                    {{ $a->description ?? '-' }}
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="{{ route('activity-logs.show', $a) }}"
                                    class="text-navy-500 hover:text-navy-700 font-semibold">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                Data tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse

                    <x-slot:footer>
                        {{ $activities->links() }}
                    </x-slot:footer>
                </x-ui.table>
            </x-ui.card>

        </div>
    </div>
</x-app-layout>
