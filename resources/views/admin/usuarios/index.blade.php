<x-layouts.app :title="__('Usuarios')">
    <div class="flex flex-col gap-6">

        {{-- Título --}}

        <div class="border-b border-zinc-200 pb-4 dark:border-zinc-700">
            <h1 class="text-2xl font-semibold text-zinc-800 dark:text-white">Usuarios del sistema</h1>
        </div>

        {{-- Botón crear --}}
        <div class="flex items-center justify-between gap-4">
            <button type="button"
                class="flex items-center gap-2 rounded-lg bg-blue-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-600">
                <flux:icon.plus class="size-4" />
                Crear nuevo
            </button>
        </div>

        {{-- Tabla --}}
        <div class="overflow-x-auto rounded-lg border border-zinc-200 bg-white shadow-sm">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 text-xs uppercase text-zinc-600">
                    <tr>
                        <th class="border-b border-r border-zinc-200 px-4 py-3 text-center">Nro</th>
                        <th class="border-b border-r border-zinc-200 px-4 py-3 text-left">Nombre(s)</th>
                        <th class="border-b border-r border-zinc-200 px-4 py-3 text-left">Apellido paterno</th>
                        <th class="border-b border-r border-zinc-200 px-4 py-3 text-left">Apellido materno</th>
                        <th class="border-b border-r border-zinc-200 px-4 py-3 text-left">Correo</th>
                        <th class="border-b border-r border-zinc-200 px-4 py-3 text-left">Teléfono</th>
                        <th class="border-b border-r border-zinc-200 px-4 py-3 text-center">Rol</th>
                        <th class="border-b border-r border-zinc-200 px-4 py-3 text-center">Estado</th>
                        <th class="border-b border-zinc-200 px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody class="text-zinc-700">

                    {{-- Fila de ejemplo 1 --}}
                    <tr class="border-b border-zinc-200">
                        <td class="border-r border-zinc-200 px-4 py-3 text-center">1</td>
                        <td class="border-r border-zinc-200 px-4 py-3">Juan</td>
                        <td class="border-r border-zinc-200 px-4 py-3">Pérez</td>
                        <td class="border-r border-zinc-200 px-4 py-3">López</td>
                        <td class="border-r border-zinc-200 px-4 py-3">juan@correo.com</td>
                        <td class="border-r border-zinc-200 px-4 py-3">9611234567</td>
                        <td class="border-r border-zinc-200 px-4 py-3 text-center">Jefe de flotilla</td>
                        <td class="border-r border-zinc-200 px-4 py-3 text-center">
                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Activo</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button"
                                    class="flex items-center gap-1.5 rounded-md bg-green-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-600">
                                    <flux:icon.pencil-square class="size-4" /> Editar
                                </button>
                                <button type="button"
                                    class="flex items-center gap-1.5 rounded-md bg-red-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-600">
                                    <flux:icon.no-symbol class="size-4" /> Desactivar
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- Fila de ejemplo 2 --}}
                    <tr class="border-b border-zinc-200">
                        <td class="border-r border-zinc-200 px-4 py-3 text-center">2</td>
                        <td class="border-r border-zinc-200 px-4 py-3">María</td>
                        <td class="border-r border-zinc-200 px-4 py-3">Gómez</td>
                        <td class="border-r border-zinc-200 px-4 py-3">Ruiz</td>
                        <td class="border-r border-zinc-200 px-4 py-3">maria@correo.com</td>
                        <td class="border-r border-zinc-200 px-4 py-3">9617654321</td>
                        <td class="border-r border-zinc-200 px-4 py-3 text-center">Jefe de flotilla</td>
                        <td class="border-r border-zinc-200 px-4 py-3 text-center">
                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Activo</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button"
                                    class="flex items-center gap-1.5 rounded-md bg-green-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-600">
                                    <flux:icon.pencil-square class="size-4" /> Editar
                                </button>
                                <button type="button"
                                    class="flex items-center gap-1.5 rounded-md bg-red-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-600">
                                    <flux:icon.no-symbol class="size-4" /> Desactivar
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- Fila de ejemplo 3 (inactivo) --}}
                    <tr class="border-b border-zinc-200">
                        <td class="border-r border-zinc-200 px-4 py-3 text-center">3</td>
                        <td class="border-r border-zinc-200 px-4 py-3">Carlos</td>
                        <td class="border-r border-zinc-200 px-4 py-3">Díaz</td>
                        <td class="border-r border-zinc-200 px-4 py-3">Mora</td>
                        <td class="border-r border-zinc-200 px-4 py-3">carlos@correo.com</td>
                        <td class="border-r border-zinc-200 px-4 py-3">9619988776</td>
                        <td class="border-r border-zinc-200 px-4 py-3 text-center">Jefe de flotilla</td>
                        <td class="border-r border-zinc-200 px-4 py-3 text-center">
                            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Inactivo</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button"
                                    class="flex items-center gap-1.5 rounded-md bg-green-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-600">
                                    <flux:icon.pencil-square class="size-4" /> Editar
                                </button>
                                <button type="button"
                                    class="flex items-center gap-1.5 rounded-md bg-blue-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-600">
                                    <flux:icon.check-circle class="size-4" /> Activar
                                </button>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>