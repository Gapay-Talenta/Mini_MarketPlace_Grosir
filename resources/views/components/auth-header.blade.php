@props([
    'title',
    'description',
])

<div class="flex w-full flex-col gap-1 text-left">
    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100">{{ $title }}</h1>
    <p class="text-xs leading-relaxed text-slate-600 dark:text-slate-400">{{ $description }}</p>
</div>
