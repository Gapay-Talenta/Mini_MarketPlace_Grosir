@props(['status' => 'default'])

@php
$map = [
    'active' => 'bg-emerald-100 text-emerald-800 border border-emerald-400 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-700',
    'pending' => 'bg-amber-100 text-amber-800 border border-amber-400 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-700',
    'suspended' => 'bg-rose-100 text-rose-800 border border-rose-400 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-700',
    'sky' => 'bg-sky-100 text-sky-800 border border-sky-400 dark:bg-sky-950/60 dark:text-sky-300 dark:border-sky-700',
    'default' => 'bg-slate-100 text-slate-700 border border-slate-300 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
];
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-sm px-2.5 py-0.5 text-xs font-bold tracking-tight uppercase', $map[$status] ?? $map['default']]) }}>
    {{ $slot }}
</span>
