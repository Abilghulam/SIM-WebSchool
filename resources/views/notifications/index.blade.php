<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Notifikasi</h2>
                <p class="text-sm text-gray-500 mt-1">Pusat notifikasi akun kamu.</p>
            </div>

            <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                @csrf
                <x-ui.button variant="secondary">Tandai semua dibaca</x-ui.button>
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-ui.flash />

            <div class="flex items-center gap-2">
                <a href="{{ route('notifications.index', ['tab' => 'unread']) }}">
                    <x-ui.badge :variant="($tab ?? 'unread') === 'unread' ? 'blue' : 'gray'">Unread</x-ui.badge>
                </a>
                <a href="{{ route('notifications.index', ['tab' => 'all']) }}">
                    <x-ui.badge :variant="($tab ?? 'unread') === 'all' ? 'blue' : 'gray'">All</x-ui.badge>
                </a>
            </div>

            <x-ui.card title="Daftar Notifikasi">
                @if ($notifications->isEmpty())
                    <div class="text-sm text-gray-600">Belum ada notifikasi.</div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach ($notifications as $n)
                            @php
                                $data = $n->data ?? [];
                                $title = data_get($data, 'title', 'Notifikasi');
                                $message = data_get($data, 'message', '');
                                $level = data_get($data, 'level', 'gray');
                                $group = data_get($data, 'group');
                                $actionUrl = data_get($data, 'action_url');
                                $actionText = data_get($data, 'action_text', 'Buka');
                                $isUnread = is_null($n->read_at);
                            @endphp

                            <div class="py-4 flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <x-ui.badge :variant="$level">{{ $group ?? 'Info' }}</x-ui.badge>
                                        @if ($isUnread)
                                            <x-ui.badge variant="amber">Baru</x-ui.badge>
                                        @endif
                                    </div>

                                    <div class="mt-2 font-semibold text-gray-900">{{ $title }}</div>
                                    <div class="mt-1 text-sm text-gray-600">{{ $message }}</div>
                                    <div class="mt-2 text-xs text-gray-400">
                                        {{ $n->created_at?->format('d-m-Y H:i') }}
                                    </div>
                                </div>

                                <div class="shrink-0 flex items-center gap-2">
                                    <a href="{{ route('notifications.read', $n->id) }}">
                                        <x-ui.button size="sm" variant="secondary">
                                            {{ $actionUrl ? $actionText : 'Tandai dibaca' }}
                                        </x-ui.button>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
