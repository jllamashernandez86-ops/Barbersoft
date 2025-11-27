@php
    $from = request('from');
    $until = request('until');
@endphp

<x-filament::page>
    <form method="GET" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="text-sm text-gray-600 dark:text-gray-300">Desde</label>
                <input class="fi-input block w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white/50 dark:bg-gray-900/50 px-3 py-2" type="date" name="from" value="{{ $from }}" />
            </div>

            <div>
                <label class="text-sm text-gray-600 dark:text-gray-300">Hasta</label>
                <input class="fi-input block w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white/50 dark:bg-gray-900/50 px-3 py-2" type="date" name="until" value="{{ $until }}" />
            </div>

            <div class="flex items-end">
                <button type="submit" class="fi-btn inline-flex items-center gap-1 rounded-lg bg-primary-600 px-4 py-2 text-white hover:bg-primary-700">
                    Filtrar
                </button>
            </div>
        </div>
    </form>

    <div class="mt-6 flex items-center gap-3">
        <form method="POST" action="{{ route('reports.export.pagos') }}">
            @csrf
            <input type="hidden" name="from" value="{{ $from }}" />
            <input type="hidden" name="until" value="{{ $until }}" />
            <button class="fi-btn inline-flex items-center gap-1 rounded-lg bg-primary-600 px-4 py-2 text-white hover:bg-primary-700">
                Exportar pagos (Excel)
            </button>
        </form>

        <form method="POST" action="{{ route('reports.export.resumen') }}">
            @csrf
            <input type="hidden" name="from" value="{{ $from }}" />
            <input type="hidden" name="until" value="{{ $until }}" />
            <button class="fi-btn inline-flex items-center gap-1 rounded-lg bg-gray-600 px-4 py-2 text-white hover:bg-gray-700">
                Exportar resumen (Excel)
            </button>
        </form>
    </div>
</x-filament::page>
