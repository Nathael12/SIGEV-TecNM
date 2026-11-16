<x-layouts.app title="Registrar devolución">

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

            .grid-asignaciones .campo-doble {
                grid-column: span 2 / span 2;
            }
        }

        @media (min-width: 1024px) {
            .grid-asignaciones {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
    </style>

    @php
        $vehiculo = $asignacion->vehiculo;
        $solicitante = $asignacion->solicitante;
        $nombreSolicitante = trim($solicitante->nombre_usuario . ' ' . $solicitante->apellido_paterno . ' ' . $solicitante->apellido_materno);
    @endphp

    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Registrar devolución</flux:heading>
        <p style="font-size:14px;color:#94a3b8;margin-top:4px;">
            Vehículo {{ $vehiculo->numero_economico }} · {{ $vehiculo->marca }} {{ $vehiculo->modelo }}
            {{ $vehiculo->anio }} · Salió el {{ $asignacion->fecha_salida->format('d/m/Y h:i A') }}
        </p>
        <br>
        <flux:separator variant="subtle" />
    </div>

    <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
        <form id="form-devolucion"
            action="{{ url('/admin/asignacion/' . $asignacion->id_asignacion . '/devolucion') }}" method="POST"
            autocomplete="off">
            @csrf
            @method('PUT')

            <div class="p-6">
                <div class="grid-asignaciones">

                    {{-- Datos de la salida (solo lectura) --}}
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
                        <flux:label>Kilometraje inicial</flux:label>
                        <flux:input icon="map" value="{{ number_format($asignacion->kilometraje_inicial) }} km"
                            readonly />
                    </div>

                    {{-- Datos de la devolución --}}
                    <div>
                        <flux:label>Fecha y hora de regreso <span class="text-red-500"
                                title="Campo obligatorio">(*)</span></flux:label>
                        <x-fecha-hora-12h name="fecha_regreso" :value="old('fecha_regreso')" :required="true" />
                        <flux:error name="fecha_regreso" />
                    </div>

                    <div>
                        <flux:label>Kilometraje final <span class="text-red-500"
                                title="Campo obligatorio">(*)</span></flux:label>
                        <flux:input name="kilometraje_final" type="number" step="1"
                            min="{{ $asignacion->kilometraje_inicial }}" value="{{ old('kilometraje_final') }}"
                            icon="map" placeholder="Ej. {{ $asignacion->kilometraje_inicial + 150 }}"
                            autocomplete="off" required />
                        <p style="font-size:12px;color:#94a3b8;margin-top:4px;">
                            Inicial: {{ number_format($asignacion->kilometraje_inicial) }} km. El final no puede ser
                            menor.</p>
                        <flux:error name="kilometraje_final" />
                    </div>

                    <div class="campo-doble">
                        <flux:label>Estado del vehículo al regresar <span class="text-red-500"
                                title="Campo obligatorio">(*)</span></flux:label>
                        <flux:select name="estado_vehiculo" required>
                            <flux:select.option value="Disponible"
                                :selected="old('estado_vehiculo', 'Disponible') === 'Disponible'">Disponible
                            </flux:select.option>
                            <flux:select.option value="En mantenimiento"
                                :selected="old('estado_vehiculo') === 'En mantenimiento'">En mantenimiento
                            </flux:select.option>
                            <flux:select.option value="Fuera de servicio"
                                :selected="old('estado_vehiculo') === 'Fuera de servicio'">Fuera de servicio
                            </flux:select.option>
                        </flux:select>
                        <p style="font-size:12px;color:#94a3b8;margin-top:4px;">
                            Elige "En mantenimiento" o "Fuera de servicio" si el vehículo regresó con alguna falla o
                            no puede usarse.</p>
                        <flux:error name="estado_vehiculo" />
                    </div>

                </div>

                {{-- Botones --}}
                <div class="flex justify-end gap-2" style="margin-top: 32px;">
                    <a href="{{ url('/admin/asignaciones') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-xs font-semibold rounded transition">
                        <i class="fas fa-arrow-left mr-2"></i> Cancelar
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded transition">
                        <i class="fas fa-undo mr-2"></i> Registrar devolución
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>

        // Confirmación antes de guardar: ayuda a detectar errores de captura en el kilometraje
        const formulario = document.getElementById('form-devolucion');
        const kmInicial = @json($asignacion->kilometraje_inicial);

        formulario.addEventListener('submit', function (evento) {
            if (!window.Swal || formulario.dataset.confirmado === '1') return;

            evento.preventDefault();

            const kmFinal = parseInt(formulario.querySelector('input[name="kilometraje_final"]').value, 10);
            const recorridos = kmFinal - kmInicial;

            Swal.fire({
                icon: 'question',
                title: 'Confirmar devolución',
                html: 'Kilometraje final: <b>' + kmFinal.toLocaleString('es-MX') + ' km</b><br>' +
                    'Kilómetros recorridos: <b>' + recorridos.toLocaleString('es-MX') + ' km</b><br><br>' +
                    '¿Los datos son correctos?',
                showCancelButton: true,
                confirmButtonText: 'Sí, registrar',
                cancelButtonText: 'Revisar',
            }).then((resultado) => {
                if (resultado.isConfirmed) {
                    formulario.dataset.confirmado = '1';
                    formulario.submit();
                }
            });
        });
    </script>
</x-layouts.app>