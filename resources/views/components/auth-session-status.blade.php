@props([
    'status',
])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-sm border border-emerald-500 bg-emerald-100 px-3 py-2.5 text-xs font-bold text-emerald-700 dark:border-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300']) }} role="status">
        {{ $status }}
    </div>
@endif
