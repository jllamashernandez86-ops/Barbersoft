@props([
    'id' => null,
    'maxWidth' => '2xl',
])

@php
    $maxWidthClass = match ($maxWidth) {
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        default => 'sm:max-w-2xl',
    };
@endphp

<div
    x-data="{ open: @entangle($attributes->wire('model')) }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center"
>
    <!-- fondo -->
    <div class="fixed inset-0 bg-gray-900/60" @click="open = false"></div>

    <!-- cuadro del modal -->
    <div class="bg-white rounded-lg shadow-xl w-full {{ $maxWidthClass }} mx-4 overflow-hidden">
        @isset($title)
            <div class="px-6 py-4 border-b">
                {{ $title }}
            </div>
        @endisset

        <div class="px-6 py-4">
            {{ $content ?? $slot }}
        </div>

        @isset($footer)
            <div class="px-6 py-4 bg-gray-50 border-t flex justify-end gap-2">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
