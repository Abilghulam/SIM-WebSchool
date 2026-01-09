@props([
    'head' => null,     // slot untuk <tr> header
    'empty' => 'Data tidak ditemukan.',
    'striped' => false,
])

<div {{ $attributes->merge(['class' => 'overflow-hidden border border-gray-200 rounded-xl']) }}>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                {{ $head }}
            </thead>

            <tbody class="divide-y divide-gray-100 {{ $striped ? 'bg-white' : '' }}">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @isset($footer)
        <div class="px-6 py-4 border-t border-gray-200 bg-white">
            {{ $footer }}
        </div>
    @endisset
</div>
