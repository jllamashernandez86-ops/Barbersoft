<div class="max-w-6xl mx-auto">
    {{-- Encabezado + acciones --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-bold">Clientes</h1>
            <p class="text-sm text-gray-500">Gestiona los clientes de la barbería</p>
        </div>
        <div class="flex items-center gap-2">
            <input type="text" wire:model.debounce.400ms="search"
                   placeholder="Buscar nombre, teléfono o correo"
                   class="border rounded px-3 py-2 w-64">
            <select wire:model="perPage" class="border rounded px-2 py-2">
                <option value="10">10 / pág.</option>
                <option value="25">25 / pág.</option>
                <option value="50">50 / pág.</option>
            </select>
            <button wire:click="openModal" class="px-4 py-2 rounded bg-black text-white">
                + Nuevo
            </button>
        </div>
    </div>

    {{-- Alertas --}}
    @if (session('ok'))
        <div class="mb-4 p-3 rounded bg-green-50 text-green-700 border border-green-200">
            {{ session('ok') }}
        </div>
    @endif

    {{-- Tabla --}}
    <div class="overflow-x-auto bg-white rounded shadow">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr class="text-left text-sm text-gray-600">
                    <th class="p-3 border-b">Nombre</th>
                    <th class="p-3 border-b">Teléfono</th>
                    <th class="p-3 border-b">Correo</th>
                    <th class="p-3 border-b w-40 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="text-sm">
            @forelse ($items as $c)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3">{{ $c->nombre }}</td>
                    <td class="p-3">{{ $c->telefono }}</td>
                    <td class="p-3">{{ $c->correo }}</td>
                    <td class="p-3 text-right">
                        <button wire:click="openModal({{ $c->id }})"
                                class="underline mr-3">Editar</button>
                        <button wire:click="confirmDelete({{ $c->id }})"
                                class="underline text-red-600">Eliminar</button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="p-6 text-center text-gray-500">Sin resultados.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $items->links() }}
    </div>

    {{-- MODAL: Crear/Editar --}}
    <x-dialog-modal wire:model="modalOpen" maxWidth="lg">
        <x-slot name="title">
            <span class="font-semibold">{{ $editingId ? 'Editar cliente' : 'Nuevo cliente' }}</span>
        </x-slot>

        <x-slot name="content">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <x-label value="Nombre" />
                    <x-input wire:model.defer="nombre" type="text" class="mt-1 w-full" />
                    @error('nombre') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <x-label value="Teléfono" />
                    <x-input wire:model.defer="telefono" type="text" class="mt-1 w-full" />
                    @error('telefono') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <x-label value="Correo" />
                    <x-input wire:model.defer="correo" type="email" class="mt-1 w-full" />
                    @error('correo') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('modalOpen', false)">Cancelar</x-secondary-button>
            <x-button class="ml-2" wire:click="save">Guardar</x-button>
        </x-slot>
    </x-dialog-modal>

    {{-- MODAL: Confirmar eliminación --}}
    <x-confirmation-modal wire:model="confirmingDelete">
        <x-slot name="title">Eliminar cliente</x-slot>
        <x-slot name="content">
            ¿Seguro que deseas eliminar este cliente? Esta acción no se puede deshacer.
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="$set('confirmingDelete', false)">Cancelar</x-secondary-button>
            <x-danger-button class="ml-2" wire:click="delete">Eliminar</x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>
