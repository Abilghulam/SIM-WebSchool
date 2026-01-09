@props([
    'title',
    'description' => null,
    'actions' => null, // slot kanan atas header
])

<x-ui.card :title="$title" :subtitle="$description" :actions="$actions">
    <div {{ $attributes->merge(['class' => 'space-y-4']) }}>
        {{ $slot }}
    </div>
</x-ui.card>
