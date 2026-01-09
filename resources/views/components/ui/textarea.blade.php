@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
])

<x-ui.field :label="$label" :hint="$hint" :error="$error" :required="$required">
    <textarea
        {{ $attributes->merge([
            'class' => 'block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500',
        ]) }}>{{ $slot }}</textarea>
</x-ui.field>
