@props(['name', 'label' => null, 'type' => 'text'])

<div class="flex flex-col gap-1.5">
    @if ($label)
        <label for="{{ $name }}" class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $label }}</label>
    @endif
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
        {{ $attributes->class('min-h-11 rounded-md border border-slate-300 bg-white px-3.5 text-sm text-slate-900 placeholder-slate-400 transition-colors focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:border-amber-400') }} />
</div>
