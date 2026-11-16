<x-layouts.app title="Asignaciones">

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
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
    </style>

    @php
        $estilos = [
            'Activa' => 'background-color:#dbeafe;color:#1d4ed8',
            'Finalizada' => 'background-color:#dcfce7;color:#15803d',
        ];
    @endphp

    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Asignaciones de vehículos</flux:heading>
        <p style="font-size:14px;color:#94a3b8;margin-top:4px;">
            {{ $enRuta }} {{ $enRuta === 1 ? 'vehículo en ruta' : 'vehículos en ruta' }} en este momento.
        </p>
        <br>
        <flux:separator variant="subtle" />
    </div>

    {{-- Filtros (RF-21: historial por vehículo y por solicitante) --}}
    <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
        <form action="{{ url('/admin/asignaciones') }}" method="GET" class="p-4">
            <div class="grid-filtros">

                <div>
                    <flux:label>Vehículo</flux:label>
                    <flux:select name="vehiculo" placeholder="Todos los vehículos">
                        @foreach ($vehiculos as $vehiculo)
                            <flux:select.option value="{{ $vehiculo->id_vehiculo }}"
                                :selected="request('vehiculo') == $vehiculo->id_vehiculo">
                                {{ $vehiculo->numero_economico }} · {{ $vehiculo->placas }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div>
                    <flux:label>Solicitante</flux:label>
                    <flux:select name="solicitante" placeholder="Todos los solicitantes">
                        @foreach ($solicitantes as $solicitante)
                            <flux:select.option value="{{ $solicitante->id }}"
                                :selected="request('solicitante') == $solicitante->id">
                                {{ $solicitante->nombre_usuario }} {{ $solicitante->apellido_paterno }}
                                {{ $solicitante->apellido_materno }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div>
                    <flux:label>Estado</flux:label>
                    <flux:select name="estado" placeholder="Todas">
                        <flux:select.option value="activa" :selected="request('estado') === 'activa'">Activas (en ruta)
                        </flux:select.option>
                        <flux:select.option value="finalizada" :selected="request('estado') === 'finalizada'">
                            Finalizadas</flux:select.option>
                    </flux:select>
                </div>

                <div style="display:flex;gap:8px;">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded transition">
                        <i class="fas fa-search mr-2"></i> Filtrar
                    </button>
                    <a href="{{ url('/admin/asignaciones') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-xs font-semibold rounded transition">
                        <i class="fas fa-times mr-2"></i> Limpiar
                    </a>
                </div>

            </div>
        </form>
    </div>

    @can('ver formulario crear asignacion')
        <div class="flex justify-end" style="margin-top: 24px;">
            <a href="{{ url('/admin/asignaciones/create') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded transition">
                <i class="fas fa-plus mr-2"></i>
                Nueva asignación
            </a>
        </div>
    @endcan

    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800"
        style="margin-top: 24px;">
        <table class="min-w-full border-collapse">
            <thead class="bg-gray-50 dark:bg-zinc-900 text-center">
                <tr>
                    @foreach (['Nro', 'Vehículo', 'Solicitante', 'Salida', 'Regreso', 'Destino', 'Kilometraje', 'Estado', 'Acciones'] as $columna)
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
                            {{ $loop->iteration }}</td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            {{ $asignacion->vehiculo->numero_economico }} · {{ $asignacion->vehiculo->placas }}</td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ $asignacion->solicitante->nombre_usuario }}
                            {{ $asignacion->solicitante->apellido_paterno }}
                            {{ $asignacion->solicitante->apellido_materno }}</td>

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
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            {{ number_format($asignacion->kilometraje_inicial) }} →
                            {{ $asignacion->kilometraje_final !== null ? number_format($asignacion->kilometraje_final) : '—' }}
                        </td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-center">
                            <span style="{{ $estilos[$asignacion->estado_asignacion] }}"
                                class="inline-block px-3 py-1 text-xs font-semibold rounded-full">
                                {{ $asignacion->estado_asignacion }}
                            </span>
                        </td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-center">
                            <div class="flex justify-center gap-2">
                                @can('ver detalle asignacion')
                                    <a href="{{ url('/admin/asignacion/' . $asignacion->id_asignacion) }}"
                                        class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-xs font-semibold rounded transition">
                                        <i class="fas fa-eye mr-2"></i> Ver
                                    </a>
                                @endcan

                                @can('ver formulario editar asignacion')
                                    <a href="{{ url('/admin/asignacion/' . $asignacion->id_asignacion . '/edit') }}"
                                        class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold rounded transition">
                                        <i class="fas fa-pencil-alt mr-2"></i> Editar
                                    </a>
                                @endcan

                                @if ($asignacion->activa && auth()->user()->can('registrar devolucion'))
                                    <a href="{{ url('/admin/asignacion/' . $asignacion->id_asignacion . '/devolucion') }}"
                                        class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded transition">
                                        <i class="fas fa-undo mr-2"></i> Registrar devolución
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9"
                            class="px-3 py-6 border border-gray-200 dark:border-zinc-700 text-center text-sm text-gray-500">
                            No hay asignaciones registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if (session('mensaje'))
        <script>
            Swal.fire({
                position: 'top-end',
                icon: @json(session('icono', 'success')),
                title: @json(session('mensaje')),
                showConfirmButton: false,
                timer: 4000
            });
        </script>
    @endif
</x-layouts.app>