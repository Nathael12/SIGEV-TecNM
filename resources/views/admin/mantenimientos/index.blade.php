<x-layouts.app title="Mantenimiento">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Mantenimiento</flux:heading>
        <br><flux:separator variant="subtle" />
    </div>
    @if (session('mensaje'))
        <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-sm text-green-800">{{ session('mensaje') }}</div>
    @endif
    <div class="mb-6">
        <x-alertas-mantenimiento :alertas="$alertasMantenimiento" />
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
            </tr></thead>
            <tbody>
                @forelse ($mantenimientos as $mantenimiento)
                    <tr class="text-center transition hover:bg-gray-50 dark:hover:bg-zinc-700/50">
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700">{{ $loop->iteration }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700">{{ $mantenimiento->vehiculo->numero_economico }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700">{{ $mantenimiento->vehiculo->marca }} {{ $mantenimiento->vehiculo->modelo }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700">{{ $mantenimiento->vehiculo->placas }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700" title="{{ $mantenimiento->descripcion_mantenimiento }}">{{ $mantenimiento->categoria_mantenimiento }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700">{{ $mantenimiento->fecha_mantenimiento->format('d/m/Y') }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700">{{ number_format($mantenimiento->kilometraje_mantenimiento) }} km</td>
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700">${{ number_format((float) $mantenimiento->monto_mantenimiento, 2) }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-sm dark:border-zinc-700">{{ $mantenimiento->proximo_mantenimiento_fecha->format('d/m/Y') }} · {{ number_format($mantenimiento->proximo_mantenimiento_km) }} km</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="border border-gray-200 px-3 py-6 text-center text-sm text-gray-500 dark:border-zinc-700">No hay mantenimientos registrados para los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
