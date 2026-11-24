<x-layouts.app title="Listado de flotilla">

    {{-- Cuadrícula propia para los filtros: no depende de que Tailwind compile clases nuevas --}}
    <style>
        .grid-filtros {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 16px;
            align-items: end;
        }

        .grid-filtros>div {
            min-width: 0;
        }

        @media (min-width: 640px) {
            .grid-filtros {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1024px) {
            .grid-filtros {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        .chip-estado {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        .chip-estado.activo {
            outline: 2px solid currentColor;
            outline-offset: 2px;
        }
    </style>

    @php
        // Colores por estado (estilos en línea: siempre se ven, compile o no Tailwind)
        $estilos = [
            'Disponible' => 'background-color:#dcfce7;color:#15803d',
            'Asignado' => 'background-color:#dbeafe;color:#1d4ed8',
            'En mantenimiento' => 'background-color:#fef3c7;color:#b45309',
            'Fuera de servicio' => 'background-color:#ffedd5;color:#c2410c',
            'Baja' => 'background-color:#e5e7eb;color:#374151',
        ];
        $estiloNeutro = 'background-color:#e5e7eb;color:#374151';
    @endphp

    <div class="relative mb-6 w-full">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;">
            <div>
                <flux:heading size="xl" level="1">Listado de flotilla</flux:heading>
                <p style="font-size:14px;color:#94a3b8;margin-top:4px;">
                    {{ $totalFlotilla }} {{ $totalFlotilla === 1 ? 'vehículo' : 'vehículos' }} en la flotilla.
                </p>
            </div>
            <a href="{{ url('/admin/reportes') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-xs font-semibold rounded transition">
                <i class="fas fa-arrow-left mr-2"></i> Volver a reportes
            </a>
        </div>
        <br>
        <flux:separator variant="subtle" />
    </div>

    {{-- Resumen por estado: cada uno funciona como filtro rápido --}}
    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:20px;">
        <a href="{{ url('/admin/reportes/flotilla') }}"
            class="chip-estado {{ $filtros['estado'] === null ? 'activo' : '' }}" style="{{ $estiloNeutro }}">
            Todos <span>{{ $totalFlotilla }}</span>
        </a>
        @foreach ($estados as $estado)
            <a href="{{ url('/admin/reportes/flotilla?estado=' . urlencode($estado)) }}"
                class="chip-estado {{ $filtros['estado'] === $estado ? 'activo' : '' }}"
                style="{{ $estilos[$estado] }}">
                {{ $estado }} <span>{{ $totales[$estado] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    {{-- Filtros --}}
    <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
        <form action="{{ url('/admin/reportes/flotilla') }}" method="GET" class="p-4">
            <flux:heading size="lg" style="margin-bottom: 12px;">Filtros</flux:heading>

            <div class="grid-filtros">
                <div>
                    <flux:label>Estado</flux:label>
                    <flux:select name="estado" placeholder="Todos los estados">
                        @foreach ($estados as $estado)
                            <flux:select.option value="{{ $estado }}" :selected="$filtros['estado'] === $estado">
                                {{ $estado }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div>
                    <flux:label>Buscar</flux:label>
                    <flux:input name="buscar" value="{{ $filtros['buscar'] }}" icon="magnifying-glass"
                        placeholder="Número económico, placas, marca o modelo" autocomplete="off" />
                </div>

                <div style="display:flex;gap:8px;">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded transition">
                        <i class="fas fa-search mr-2"></i> Filtrar
                    </button>
                    <a href="{{ url('/admin/reportes/flotilla') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-xs font-semibold rounded transition">
                        <i class="fas fa-times mr-2"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Exportación (RF-34): respeta los filtros aplicados.
         Sin registros se avisa con un modal en lugar de descargar un archivo vacío. --}}
    @can('exportar reportes')
        @php
            $hayRegistros = $vehiculos->total() > 0;
            $consultaExportar = http_build_query(array_filter($filtros));
        @endphp
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:24px;">
            @if ($hayRegistros)
                <a href="{{ url('/admin/reportes/flotilla/pdf') . ($consultaExportar ? '?' . $consultaExportar : '') }}"
                    class="inline-flex items-center px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-xs font-semibold rounded transition">
                    <i class="fas fa-file-pdf mr-2"></i> Exportar PDF
                </a>
                <a href="{{ url('/admin/reportes/flotilla/excel') . ($consultaExportar ? '?' . $consultaExportar : '') }}"
                    class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold rounded transition">
                    <i class="fas fa-file-excel mr-2"></i> Exportar Excel
                </a>
            @else
                <button type="button" onclick="sinRegistros()" style="opacity:.6;"
                    class="inline-flex items-center px-4 py-2 bg-red-500 text-white text-xs font-semibold rounded">
                    <i class="fas fa-file-pdf mr-2"></i> Exportar PDF
                </button>
                <button type="button" onclick="sinRegistros()" style="opacity:.6;"
                    class="inline-flex items-center px-4 py-2 bg-green-500 text-white text-xs font-semibold rounded">
                    <i class="fas fa-file-excel mr-2"></i> Exportar Excel
                </button>
            @endif
        </div>
    @endcan

    {{-- Tabla --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800"
        style="margin-top: 24px;">
        <table class="w-full min-w-full border-collapse" style="width:100%;">
            <thead class="bg-gray-50 dark:bg-zinc-900 text-center">
                <tr>
                    @foreach (['Nro', 'Número económico', 'Placas', 'Vehículo', 'Año', 'Tipo', 'Combustible', 'Kilometraje', 'Estado'] as $columna)
                        <th
                            class="px-6 py-3 border-x border-b border-gray-200 dark:border-zinc-700 text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ $columna }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-zinc-800">
                @forelse ($vehiculos as $vehiculo)
                    @php $km = $vehiculo->kilometraje_vigente; @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition">
                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            {{ $vehiculos->firstItem() + $loop->index }}</td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            {{ $vehiculo->numero_economico }}</td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            {{ $vehiculo->placas }}</td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ $vehiculo->marca }} {{ $vehiculo->modelo }}</td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            {{ $vehiculo->anio }}</td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            {{ $vehiculo->getAttribute('tipo vehiculo') }}</td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            {{ $vehiculo->getAttribute('tipo combustible') }}</td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            {{ $km !== null ? number_format($km) . ' km' : '—' }}</td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-center">
                            <span style="{{ $estilos[$vehiculo->estado] ?? $estiloNeutro }}"
                                class="inline-block px-3 py-1 text-xs font-semibold rounded-full">
                                {{ $vehiculo->estado }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9"
                            class="px-3 py-6 border border-gray-200 dark:border-zinc-700 text-center text-sm text-gray-500">
                            No hay vehículos que coincidan con los filtros.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $vehiculos->links() }}
    </div>

    <script>
        // Aviso cuando no hay nada que exportar
        function sinRegistros() {
            Swal.fire({
                icon: 'info',
                title: 'No hay registros para exportar',
                text: 'Cambia los filtros e inténtalo de nuevo.',
                confirmButtonText: 'Entendido',
            });
        }
    </script>

    {{-- Mensaje que envía el controlador (por ejemplo, si se intentó exportar sin registros) --}}
    @if (session('mensaje'))
        <script>
            Swal.fire({
                icon: @json(session('icono', 'success')),
                title: @json(session('mensaje')),
                showConfirmButton: false,
                timer: 3000
            });
        </script>
    @endif
</x-layouts.app>