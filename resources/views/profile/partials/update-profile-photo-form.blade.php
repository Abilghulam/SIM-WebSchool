<section>
    @php
        $photoUrl = $user->profilePhotoUrl();

        // fallback warna inline (anti Tailwind purge untuk class dinamis)
        $seed = (string) ($user->email ?? ($user->username ?? ($user->id ?? 'user')));
        $hash = crc32($seed);
        $palette = [
            '#475569',
            '#4b5563',
            '#52525b',
            '#57534e',
            '#dc2626',
            '#ea580c',
            '#d97706',
            '#ca8a04',
            '#65a30d',
            '#16a34a',
            '#059669',
            '#0d9488',
            '#0891b2',
            '#0284c7',
            '#2563eb',
            '#4f46e5',
            '#7c3aed',
            '#9333ea',
            '#c026d3',
            '#db2777',
            '#e11d48',
        ];
        $bg = $palette[$hash % count($palette)];
    @endphp

    <header>
        <h2 class="text-lg font-medium text-gray-900">Profile Photo</h2>
        <p class="mt-1 text-sm text-gray-600">
            Update your account profile photo
        </p>
    </header>

    <div class="mt-4 flex items-center gap-4">
        @if ($photoUrl)
            <img src="{{ $photoUrl }}" alt="Foto Profil"
                class="h-14 w-14 rounded-full object-cover ring-2 ring-gray-200 shrink-0">
        @else
            <div class="h-14 w-14 rounded-full flex items-center justify-center text-white text-lg font-bold shrink-0 leading-none"
                style="background-color: {{ $bg }};" title="{{ $user->name }}">
                {{ $user->avatarInitials() }}
            </div>
        @endif

        <div class="text-sm text-gray-700">
            <div class="font-semibold">{{ $user->name }}</div>
            <div class="text-gray-500">{{ $user->email }}</div>
        </div>
    </div>

    {{-- Upload form --}}
    <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data"
        class="mt-6 space-y-4">
        @csrf

        <div>
            <x-input-label for="photo" value="Upload foto (JPG/PNG/WEBP, max 2MB)" />
            <input id="photo" name="photo" type="file" accept="image/*" class="mt-1 block w-full" required>
            <x-input-error class="mt-2" :messages="$errors->get('photo')" />
        </div>

        <div class="flex items-center gap-3">
            <x-ui.button variant="primary" type="submit">
                Simpan Foto
            </x-ui.button>

            @if (session('status') === 'profile-photo-updated')
                <p class="text-sm text-green-600">Foto diperbarui.</p>
            @endif
        </div>
    </form>

    {{-- Delete form (separate, no nested form) --}}
    @if ($user->profile_photo_path)
        <form method="POST" action="{{ route('profile.photo.delete') }}" class="mt-3">
            @csrf
            @method('DELETE')

            <x-ui.button variant="danger" type="submit" class="text-sm font-semibold text-red-600 hover:text-red-800">
                Hapus Foto
            </x-ui.button>

            @if (session('status') === 'profile-photo-deleted')
                <span class="ml-3 text-sm text-green-600">Foto dihapus.</span>
            @endif
        </form>
    @endif
</section>
