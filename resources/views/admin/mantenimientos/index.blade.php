<x-layouts.app title="Mantenimiento">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Mantenimiento</flux:heading>
        <br><flux:separator variant="subtle" />
    </div>
    @if (session('mensaje'))
        <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-sm text-green-800">{{ session('mensaje') }}</div>
    @endif
    @error('alerta')
        <div role="alert" class="mb-4 rounded-lg bg-amber-100 px-4 py-3 text-sm text-amber-900 dark:bg-amber-950 dark:text-amber-200">{{ $message }}</div>
    @enderror
    <div class="mb-6 space-y-3">
        <x-alertas-mantenimiento :alertas="$alertasUnificadas" />
    </div>
    <div class="flex justify-end">
        <a href="{{ route('admin.mantenimientos.create') }}" class="inline-flex items-center rounded bg-blue-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-blue-600"><i class="fas fa-plus mr-2"></i> Registrar mantenimiento</a>
    </div>
    <div class="mt-6 overflow-x-auto rounded-lg border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <table class="w-full min-w-[1200px] border-collapse">
            <thead class="bg-gray-50 text-center dark:bg-zinc-900"><tr>
                @foreach (['Nro.', 'Núm. económico', 'Vehículo', 'Placas', 'Categoría', 'Fecha del servicio', 'Kilometraje', 'Monto', 'Próximo mantenimiento'] as $encabezado)
                    <th class="border-x border-b border-gray-200 px-4 py-3 text-xs font-bold uppercase tracking-wider text-gray-500 dark:border-zinc-700 dark:text-gray-300">{{ $encabezado }}</th>
                @endforeach
                <th class="border-x border-b border-gray-200 px-4 py-3 text-xs font-bold uppercase tracking-wider text-gray-500 dark:border-zinc-700 dark:text-gray-300">ESTADO DE ALERTA</th>
                <th class="border-x border-b border-gray-200 px-4 py-3 text-xs font-bold uppercase tracking-wider text-gray-500 dark:border-zinc-700 dark:text-gray-300">Acciones</th>
            </tr></thead>
            <tbody>
                @forelse ($mantenimientos as $mantenimiento)
                    <tr class="text-center transition hover:bg-gray-50 dark:hover:bg-zinc-700/50">
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700">{{ $loop->iteration }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700">{{ $mantenimiento->vehiculo->numero_economico }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-sm font-medium dark:border-zinc-700">{{ $mantenimiento->vehiculo->marca }} {{ $mantenimiento->vehiculo->modelo }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700">{{ $mantenimiento->vehiculo->placas }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700" title="{{ $mantenimiento->descripcion_mantenimiento }}">{{ $mantenimiento->categoria_mantenimiento }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700">{{ $mantenimiento->fecha_mantenimiento->format('d/m/Y') }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700">{{ number_format($mantenimiento->kilometraje_mantenimiento) }} km</td>
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700">${{ number_format((float) $mantenimiento->monto_mantenimiento, 2) }}</td>
                        @php($proximoKilometraje = $mantenimiento->getRawOriginal('proximo_mantenimiento_km'))
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700">{{ $mantenimiento->proximo_mantenimiento_fecha?->format('d/m/Y') ?? 'Sin fecha' }} · {{ is_numeric($proximoKilometraje) && trim((string) $proximoKilometraje) !== '' ? number_format((float) $proximoKilometraje) . ' km' : 'Sin kilometraje' }}</td>
                        @php($alertaActiva = $mantenimiento->alertas->firstWhere('estado', 'activa'))
                        @php($alertaAtendida = $mantenimiento->alertas->firstWhere('estado', 'atendida'))
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700">
                            @if ($alertaActiva)
                                <span class="inline-flex rounded-full bg-orange-100 px-2.5 py-1 text-xs font-semibold text-orange-800 dark:bg-orange-950 dark:text-orange-200">Pendiente</span>
                            @elseif ($alertaAtendida)
                                <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800 dark:bg-green-950 dark:text-green-200">Atendida</span>
                            @else
                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-zinc-700 dark:text-zinc-300">Sin alerta</span>
                            @endif
                        </td>
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700">
                            <div class="flex items-center justify-center gap-2">
                                @can('editar mantenimiento')
                                    <a href="{{ route('admin.mantenimientos.edit', $mantenimiento) }}" class="rounded bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-600">Editar</a>
                                @endcan
                                @can('eliminar mantenimiento')
                                    <form action="{{ route('admin.mantenimientos.destroy', $mantenimiento) }}" method="POST" onsubmit="return confirm('¿Eliminar este mantenimiento? Esta acción no se puede deshacer.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">Eliminar</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="border border-gray-200 px-3 py-6 text-center text-sm text-gray-500 dark:border-zinc-700">No hay mantenimientos registrados para los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
