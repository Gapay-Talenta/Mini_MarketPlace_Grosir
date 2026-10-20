@props(['title' => 'Belum ada data', 'description' => null])

<div {{ $attributes->class('flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-slate-300 bg-slate-50/50 p-10 text-center dark:border-slate-700 dark:bg-slate-900/50') }}>
    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $title }}</p>
    @if ($description)
        <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-3">{{ $action }}</div>
    @endisset
</div>
