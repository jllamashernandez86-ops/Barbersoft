@props([])

<x-dialog-modal {{ $attributes }}>
    {{-- Título del modal --}}
    @isset($title)
        <x-slot name="title">
            {{ $title }}
        </x-slot>
    @endisset

    {{-- Contenido principal --}}
    @isset($content)
        <x-slot name="content">
            {{ $content }}
        </x-slot>
    @else
        {{ $slot }}
    @endisset

    {{-- Pie del modal (botones) --}}
    @isset($footer)
        <x-slot name="footer">
            {{ $footer }}
        </x-slot>
    @endisset
</x-dialog-modal>
