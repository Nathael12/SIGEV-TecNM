<x-layouts.app title="Ver asignación">

    {{-- Cuadrícula propia: no depende de que Tailwind compile clases nuevas --}}
    <style>
        .grid-asignaciones {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 24px 16px;
        }

        .grid-asignaciones>div {
            min-width: 0;
        }

        @media (min-width: 640px) {
            .grid-asignaciones {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .grid-asignaciones .campo-doble,
            .grid-asignaciones .campo-triple,
            .grid-asignaciones .campo-completo {
                grid-column: span 2 / span 2;
            }
        }

        @media (min-width: 1024px) {
            .grid-asignaciones {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .grid-asignaciones .campo-triple {
                grid-column: span 3 / span 3;
            }

            .grid-asignaciones .campo-completo {
                grid-column: span 4 / span 4;
            }
        }
    </style>

    @php
        $vehiculo = $asignacion->vehiculo;
        $solicitante = $asignacion->solicitante;
        $nombreSolicitante = trim($solicitante->nombre_usuario . ' ' . $solicitante->apellido_paterno . ' ' . $solicitante->apellido_materno);
    @endphp

    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Datos de la asignación</flux:heading>
        <p style="font-size:14px;color:#94a3b8;margin-top:4px;">
            Vehículo {{ $vehiculo->numero_economico }} · {{ $vehiculo->marca }} {{ $vehiculo->modelo }}
            {{ $vehiculo->anio }}
        </p>
        <br>
        <flux:separator variant="subtle" />
    </div>

    <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
        <div class="p-6">
            <div class="grid-asignaciones">

                {{-- Fila 1 --}}
                <div>
                    <flux:label>Vehículo</flux:label>
                    <flux:input icon="truck" value="{{ $vehiculo->numero_economico }} · {{ $vehiculo->placas }}"
                        readonly />
                </div>

                <div class="campo-doble">
                    <flux:label>Solicitante</flux:label>
                    <flux:input icon="user" value="{{ $nombreSolicitante }}" readonly />
                </div>

                <div>
                    <flux:label>Estado de la asignación</flux:label>
                    <flux:input :icon="$asignacion->activa ? 'clock' : 'check-circle'"
                        value="{{ $asignacion->estado_asignacion }}" readonly />
                </div>

                {{-- Fila 2 --}}
                <div>
                    <flux:label>Fecha y hora de salida</flux:label>
                    <flux:input icon="calendar-days" value="{{ $asignacion->fecha_salida->format('d/m/Y h:i A') }}"
                        readonly />
                </div>

                <div>
                    <flux:label>Fecha y hora de regreso</flux:label>
                    <flux:input icon="calendar-days"
                        value="{{ $asignacion->fecha_regreso ? $asignacion->fecha_regreso->format('d/m/Y h:i A') : 'Pendiente' }}"
                        readonly />
                </div>

                <div>
                    <flux:label>Kilometraje inicial</flux:label>
                    <flux:input icon="map" value="{{ number_format($asignacion->kilometraje_inicial) }} km"
                        readonly />
                </div>

                <div>
                    <flux:label>Kilometraje final</flux:label>
                    <flux:input icon="map"
                        value="{{ $asignacion->kilometraje_final !== null ? number_format($asignacion->kilometraje_final) . ' km' : 'Pendiente' }}"
                        readonly />
                </div>

                {{-- Fila 3 --}}
                <div>
                    <flux:label>Kilómetros recorridos</flux:label>
                    <flux:input icon="arrows-right-left"
                        value="{{ $asignacion->kilometros_recorridos !== null ? number_format($asignacion->kilometros_recorridos) . ' km' : '—' }}"
                        readonly />
                </div>

                <div class="campo-triple">
                    <flux:label>Destino</flux:label>
                    <flux:input icon="map-pin" value="{{ $asignacion->destino_vehiculo }}" readonly />
                </div>

                {{-- Fila 4 --}}
                <div class="campo-completo">
                    <flux:label>Motivo de uso</flux:label>
                    <flux:input icon="clipboard-document-list" value="{{ $asignacion->motivo_uso }}" readonly />
                </div>

            </div>

            {{-- Botones --}}
            <div class="flex justify-end gap-2" style="margin-top: 32px;">
                <a href="{{ url('/admin/asignaciones') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-xs font-semibold rounded transition">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>

                @if ($asignacion->activa && auth()->user()->can('registrar devolucion'))
                    <a href="{{ url('/admin/asignacion/' . $asignacion->id_asignacion . '/devolucion') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded transition">
                        <i class="fas fa-undo mr-2"></i> Registrar devolución
                    </a>
                @endif
            </div>
        </div>
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