<x-layouts.app title="Historial de asignaciones">

    {{-- Cuadrícula propia: no depende de que Tailwind compile clases nuevas --}}
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
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        .grid-resumen {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        @media (min-width: 1024px) {
            .grid-resumen {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        .dato-resumen {
            padding: 16px 20px;
        }

        .dato-resumen .numero {
            font-size: 26px;
            font-weight: 700;
            line-height: 1.2;
        }

        .dato-resumen .etiqueta {
            font-size: 13px;
            color: #94a3b8;
        }
    </style>

    @php
        $estilos = [
            'Activa' => 'background-color:#dbeafe;color:#1d4ed8',
            'Finalizada' => 'background-color:#dcfce7;color:#15803d',
        ];
    @endphp

    <div class="relative mb-6 w-full">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;">
            <div>
                <flux:heading size="xl" level="1">Historial de asignaciones</flux:heading>
                <p style="font-size:14px;color:#94a3b8;margin-top:4px;">
                    @if ($vehiculoSeleccionado)
                        Vehículo {{ $vehiculoSeleccionado->numero_economico }} · {{ $vehiculoSeleccionado->placas }}
                        — {{ $vehiculoSeleccionado->marca }} {{ $vehiculoSeleccionado->modelo }}
                        {{ $vehiculoSeleccionado->anio }}
                    @else
                        Todos los vehículos. Elige uno para ver solo su historial.
                    @endif
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

    {{-- Resumen del conjunto filtrado --}}
    <div class="grid-resumen">
        <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 dato-resumen">
            <div class="numero">{{ number_format($totales['asignaciones']) }}</div>
            <div class="etiqueta">Asignaciones</div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 dato-resumen">
            <div class="numero">{{ number_format($totales['activas']) }}</div>
            <div class="etiqueta">Activas (sin devolución)</div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 dato-resumen">
            <div class="numero">{{ number_format($totales['finalizadas']) }}</div>
            <div class="etiqueta">Finalizadas</div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 dato-resumen">
            <div class="numero">{{ number_format($totales['km']) }} km</div>
            <div class="etiqueta">Recorridos (asignaciones finalizadas)</div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
        <form action="{{ url('/admin/reportes/asignaciones') }}" method="GET" class="p-4">
            <flux:heading size="lg" style="margin-bottom: 12px;">Filtros</flux:heading>

            <div class="grid-filtros">
                <div>
                    <flux:label>Vehículo</flux:label>
                    <flux:select name="vehiculo" placeholder="Todos los vehículos">
                        @foreach ($vehiculos as $vehiculo)
                            <flux:select.option value="{{ $vehiculo->id_vehiculo }}"
                                :selected="(string) $filtros['vehiculo'] === (string) $vehiculo->id_vehiculo">
                                {{ $vehiculo->numero_economico }} · {{ $vehiculo->placas }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="vehiculo" />
                </div>

                <div>
                    <flux:label>Salidas desde</flux:label>
                    <flux:input type="date" name="desde" value="{{ $filtros['desde'] }}" />
                    <flux:error name="desde" />
                </div>

                <div>
                    <flux:label>Salidas hasta</flux:label>
                    <flux:input type="date" name="hasta" value="{{ $filtros['hasta'] }}" />
                    <flux:error name="hasta" />
                </div>

                <div style="display:flex;gap:8px;">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded transition">
                        <i class="fas fa-search mr-2"></i> Filtrar
                    </button>
                    <a href="{{ url('/admin/reportes/asignaciones') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-xs font-semibold rounded transition">
                        <i class="fas fa-times mr-2"></i> Limpiar
                    </a>
                </div>
            </div>

            <p style="font-size:12px;color:#94a3b8;margin-top:12px;">
                El periodo se aplica sobre la fecha de salida de cada asignación.
            </p>
        </form>
    </div>

    {{-- Exportación (RF-34): respeta los filtros aplicados.
         Sin registros se avisa con un modal en lugar de descargar un archivo vacío. --}}
    @can('exportar reportes')
        @php
            $hayRegistros = $totales['asignaciones'] > 0;
            $consultaExportar = http_build_query(array_filter($filtros));
        @endphp
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:24px;">
            @if ($hayRegistros)
                <a href="{{ url('/admin/reportes/asignaciones/pdf') . ($consultaExportar ? '?' . $consultaExportar : '') }}"
                    class="inline-flex items-center px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-xs font-semibold rounded transition">
                    <i class="fas fa-file-pdf mr-2"></i> Exportar PDF
                </a>
                <a href="{{ url('/admin/reportes/asignaciones/excel') . ($consultaExportar ? '?' . $consultaExportar : '') }}"
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
                    @foreach (['Nro', 'Vehículo', 'Solicitante', 'Salida', 'Regreso', 'Destino', 'Motivo', 'Km inicial', 'Km final', 'Recorridos', 'Estado'] as $columna)
                        <th
                            class="px-6 py-3 border-x border-b border-gray-200 dark:border-zinc-700 text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ $columna }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-zinc-800">
                @forelse ($asignaciones as $asignacion)
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition">
                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            {{ $asignaciones->firstItem() + $loop->index }}</td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            {{ $asignacion->vehiculo?->numero_economico }} · {{ $asignacion->vehiculo?->placas }}</td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ $asignacion->solicitante?->nombre_usuario }}
                            {{ $asignacion->solicitante?->apellido_paterno }}
                            {{ $asignacion->solicitante?->apellido_materno }}</td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            {{ $asignacion->fecha_salida->format('d/m/Y h:i A') }}</td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            {{ $asignacion->fecha_regreso ? $asignacion->fecha_regreso->format('d/m/Y h:i A') : '—' }}
                        </td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ \Illuminate\Support\Str::limit($asignacion->destino_vehiculo, 28) }}</td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ \Illuminate\Support\Str::limit($asignacion->motivo_uso, 30) }}</td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            {{ number_format($asignacion->kilometraje_inicial) }}</td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            {{ $asignacion->kilometraje_final !== null ? number_format($asignacion->kilometraje_final) : '—' }}
                        </td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            {{ $asignacion->kilometros_recorridos !== null ? number_format($asignacion->kilometros_recorridos) . ' km' : '—' }}
                        </td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-center">
                            <span style="{{ $estilos[$asignacion->estado_asignacion] }}"
                                class="inline-block px-3 py-1 text-xs font-semibold rounded-full">
                                {{ $asignacion->estado_asignacion }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11"
                            class="px-3 py-6 border border-gray-200 dark:border-zinc-700 text-center text-sm text-gray-500">
                            No hay asignaciones que coincidan con los filtros.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $asignaciones->links() }}
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