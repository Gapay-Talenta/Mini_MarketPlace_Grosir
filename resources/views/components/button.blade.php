@props(['type' => 'button', 'variant' => 'primary'])

@php
$variants = [
    'primary' => 'bg-amber-500 text-slate-900 font-bold hover:bg-amber-600 hover:text-white focus-visible:ring-amber-500 shadow-xs',
    'slate' => 'bg-slate-800 text-white font-semibold hover:bg-slate-900 focus-visible:ring-slate-500 shadow-xs dark:bg-slate-700 dark:hover:bg-slate-600',
    'secondary' => 'border border-slate-300 bg-white text-slate-700 font-medium hover:bg-slate-100 hover:border-slate-400 focus-visible:ring-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700',
    'outline' => 'border border-slate-300 text-slate-700 font-medium hover:bg-slate-100 hover:border-slate-400 focus-visible:ring-slate-400 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800',
];
@endphp

<button type="{{ $type }}"
    {{ $attributes->class(['inline-flex min-h-11 items-center justify-center gap-2 rounded-md px-4 py-2 text-sm transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2', $variants[$variant] ?? $variants['primary']]) }}>
    {{ $slot }}
</button>
