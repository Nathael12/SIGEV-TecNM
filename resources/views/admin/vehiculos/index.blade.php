<x-layouts.app title="Vehículos">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Vehículos</flux:heading>
        <br><flux:separator variant="subtle" />
    </div>
    @if (session('mensaje'))
        <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-sm text-green-800">{{ session('mensaje') }}</div>
    @endif
    <div class="flex justify-end">
        <a href="{{ route('admin.vehiculos.create') }}" class="inline-flex items-center rounded bg-blue-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-blue-600"><i class="fas fa-plus mr-2"></i> Registrar vehículo</a>
    </div>
    <div class="mt-6 overflow-x-auto rounded-lg border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <table class="w-full min-w-[900px] border-collapse">
            <thead class="bg-gray-50 text-center dark:bg-zinc-900">
                <tr>
                    @foreach (['Nro.', 'Número económico', 'Placas', 'Marca', 'Modelo', 'Año', 'Estado', 'Acciones'] as $encabezado)
                        <th class="border-x border-b border-gray-200 px-4 py-3 text-xs font-bold uppercase tracking-wider text-gray-500 dark:border-zinc-700 dark:text-gray-300">{{ $encabezado }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($vehiculos as $vehiculo)
                    <tr class="text-center transition hover:bg-gray-50 dark:hover:bg-zinc-700/50">
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700">{{ $loop->iteration }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700">{{ $vehiculo->numero_economico }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700">{{ $vehiculo->placas }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700">{{ $vehiculo->marca }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700">{{ $vehiculo->modelo }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700">{{ $vehiculo->anio }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700"><span @class([
                            'inline-block rounded-full px-3 py-1 text-xs font-semibold',
                            'bg-green-100 text-green-700' => $vehiculo->estado === 'Disponible',
                            'bg-blue-100 text-blue-700' => $vehiculo->estado === 'Asignado',
                            'bg-amber-100 text-amber-700' => $vehiculo->estado === 'En mantenimiento',
                            'bg-orange-100 text-orange-700' => $vehiculo->estado === 'Fuera de servicio',
                            'bg-gray-200 text-gray-700' => $vehiculo->estado === 'Baja' || ! in_array($vehiculo->estado, ['Disponible', 'Asignado', 'En mantenimiento', 'Fuera de servicio', 'Baja'], true),
                        ])>{{ $vehiculo->estado }}</span></td>
                        <td class="border border-gray-200 px-3 py-2 text-center dark:border-zinc-700">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('admin.vehiculos.show', $vehiculo->getKey()) }}" class="inline-flex items-center rounded bg-gray-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-gray-600"><i class="fas fa-eye mr-2"></i> Ver ficha</a>

                                @can('ver documentos')
                                <a href="{{ route('admin.documentos.index', ['vehiculo' => $vehiculo->getKey()]) }}" class="inline-flex items-center rounded bg-blue-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-blue-600"><i class="fas fa-folder-open mr-2"></i> Documentación</a>
                                @endcan

                                <a href="{{ route('admin.vehiculos.edit', $vehiculo->getKey()) }}" class="inline-flex items-center rounded bg-amber-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-amber-600"><i class="fas fa-pen mr-2"></i> Editar</a>
                                <form action="{{ route('admin.vehiculos.destroy', $vehiculo->getKey()) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este vehículo? Esta acción no se puede deshacer.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center rounded bg-red-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-700"><i class="fas fa-trash mr-2"></i> Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="border border-gray-200 px-3 py-6 text-center text-sm text-gray-500 dark:border-zinc-700">No hay vehículos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
