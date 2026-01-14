<button
    {{ $attributes->merge([
        'type' => 'submit',
        'class' => 'inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs text-white uppercase tracking-widest
                       bg-gradient-to-br from-slate-900 to-blue-900 hover:from-slate-800 hover:to-blue-800
                       active:from-slate-950 active:to-blue-950
                       focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2
                       transition ease-in-out duration-150',
    ]) }}>
    {{ $slot }}
</button>
