@props(['title' => null])

<x-layouts::auth.simple :title="$title">
    @isset($aside)
        <x-slot:aside>
            {{ $aside }}
        </x-slot:aside>
    @endisset

    {{ $slot }}
</x-layouts::auth.simple>
