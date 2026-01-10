<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900">Ganti Password</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-ui.flash />

            <x-ui.card title="Wajib Ganti Password"
                subtitle="Demi keamanan akun, kamu harus mengganti password sebelum melanjutkan.">
                <form method="POST" action="{{ route('password.change.update') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <x-ui.input label="Password Saat Ini" name="current_password" type="password"
                        autocomplete="current-password" required />

                    <x-ui.input label="Password Baru" name="password" type="password" autocomplete="new-password"
                        required />

                    <x-ui.input label="Konfirmasi Password Baru" name="password_confirmation" type="password"
                        autocomplete="new-password" required />

                    <div class="flex justify-end">
                        <x-ui.button type="submit">
                            Simpan Password
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
