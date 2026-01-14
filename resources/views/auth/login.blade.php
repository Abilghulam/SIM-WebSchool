<x-guest-layout>
    <!-- Brand (pindah ke atas card) -->
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ url('/') }}" class="flex items-center gap-3">
            <div class="h-10 w-10 overflow-hidden rounded-2xl border"
                style="border-color: var(--line); background: rgba(15, 30, 55, 0.55);">
                <!-- SIMPAN LOGO DI: public/assets/images/logo.png -->
                <img src="{{ asset('assets/images/logo.png') }}" alt="Logo SMKN 9 Muaro Jambi"
                    class="h-full w-full object-contain p-1"
                    onerror="this.style.display='none'; this.parentElement.classList.add('flex','items-center','justify-center'); this.parentElement.innerHTML='<span class=\'text-[10px] font-semibold text-slate-400\'>LOGO</span>';">
            </div>

            <div class="leading-tight">
                <div class="text-sm font-bold tracking-tight" style="color: var(--navy);">
                    SMKN 9 Muaro Jambi
                </div>
                <div class="text-xs" style="color: var(--muted);">
                    Sistem Informasi Manajemen Sekolah
                </div>
            </div>
        </a>

        <a href="{{ url('/') }}"
            class="inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-sm font-semibold hover:opacity-95"
            style="border-color: var(--line); color: var(--navy); background: rgba(15,30,55,0.22);">
            Kembali
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </a>
    </div>

    <!-- Title -->
    <div class="mb-6">
        <div class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold"
            style="border-color: var(--line); background: rgba(15,30,55,0.28); color: var(--navy-soft);">
            <span class="h-2 w-2 rounded-full" style="background: rgba(175,205,228,.55);"></span>
            Login • SIM Sekolah
        </div>

        <h1 class="mt-4 text-xl font-bold tracking-tight" style="color: var(--navy);">
            Masuk ke Sistem
        </h1>
        <p class="mt-2 text-sm" style="color: var(--muted);">
            Gunakan NIP/Username dan password yang terdaftar.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Username -->
        <div>
            <x-input-label for="username" value="NIP / Username" class="text-sm" style="color: var(--navy-soft);" />
            <x-text-input id="username" type="text" name="username" :value="old('username')" required autofocus
                autocomplete="username"
                class="mt-1 block w-full rounded-xl border text-slate-100 placeholder:text-slate-500 focus:ring-0"
                style="border-color: var(--line); background: rgba(15,30,55,0.22);" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="text-sm" style="color: var(--navy-soft);" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password"
                class="mt-1 block w-full rounded-xl border text-slate-100 placeholder:text-slate-500 focus:ring-0"
                style="border-color: var(--line); background: rgba(15,30,55,0.22);" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center gap-2 select-none">
                <input id="remember_me" type="checkbox" name="remember"
                    class="h-4 w-4 rounded border-slate-600 bg-transparent"
                    style="accent-color: rgba(175,205,228,.75);">
                <span class="text-sm" style="color: var(--muted);">Remember me</span>
            </label>
        </div>

        <!-- Submit -->
        <div class="pt-2">
            <button type="submit"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold text-white hover:opacity-95"
                style="background: linear-gradient(135deg, rgba(35,90,135,.95), rgba(70,130,175,.92)); box-shadow: 0 10px 20px -16px rgba(140,178,205,.30);">
                Log in
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                    <path d="M10 17l5-5-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
        </div>
    </form>
</x-guest-layout>
