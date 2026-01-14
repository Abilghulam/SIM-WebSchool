<button
    {{ $attributes->merge([
        'type' => 'button',
        'class' => 'inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest
                       bg-white border border-gray-300 shadow-sm hover:bg-gray-50
                       focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2
                       disabled:opacity-25 transition ease-in-out duration-150',
    ]) }}>
    {{ $slot }}
</button>
