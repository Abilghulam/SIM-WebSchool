@if (session('success'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition
        class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700 flex items-start justify-between">
        <div>{{ session('success') }}</div>

        <button @click="show = false" class="text-green-700 hover:text-green-900">
            ✕
        </button>
    </div>
@endif
