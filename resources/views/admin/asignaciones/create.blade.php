<x-layouts.app title="Registrar salida">

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
        // Verificar si existen vehículos disponibles y solicitantes activos
        $sinVehiculos = $vehiculos->isEmpty();
        $sinSolicitantes = $solicitantes->isEmpty();

        // Obtener el ID, marca, modelo y año de cada vehículo.
        $datosVehiculos = $vehiculos->mapWithKeys(function ($vehiculo) {
            return [
                $vehiculo->id_vehiculo => [
                    'marca' => $vehiculo->marca,
                    'modelo' => $vehiculo->modelo,
                    'anio' => $vehiculo->anio,
                ]
            ];
        });
    @endphp

    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Registrar salida de vehículo</flux:heading>
        <p style="font-size:14px;color:#94a3b8;margin-top:4px;">
            Solo se pueden asignar vehículos disponibles a usuarios activos con rol Solicitante.
        </p>
        <br>
        <flux:separator variant="subtle" />
    </div>

    {{-- Avisos cuando falta algo para poder registrar --}}
    @if ($sinVehiculos || $sinSolicitantes)
        <div
            style="background-color:#fef3c7;color:#92400e;border:1px solid #fcd34d;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:14px;">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            @if ($sinVehiculos)
                No hay vehículos disponibles para asignar.
            @endif
            @if ($sinSolicitantes)
                No hay usuarios activos con rol Solicitante. Crea uno desde el módulo de Usuarios.
            @endif
        </div>
    @endif

    <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
        <form id="form-asignacion" action="{{ url('/admin/asignaciones/create') }}" method="POST" autocomplete="off">
            @csrf

            <div class="p-6">
                <div class="grid-asignaciones">

                    {{-- Fila 1 --}}

                    {{-- Vehículo --}}
                    <div>
                        <flux:label>Vehículo <span class="text-red-500" title="Campo obligatorio">(*)</span>
                        </flux:label>
                        <flux:select name="vehiculo_id" placeholder="Selecciona un vehículo..." required>
                            @foreach ($vehiculos as $vehiculo)
                                <flux:select.option value="{{ $vehiculo->id_vehiculo }}"
                                    :selected="old('vehiculo_id') == $vehiculo->id_vehiculo">
                                    {{ $vehiculo->numero_economico }} · {{ $vehiculo->placas }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>

                        <p id="vehiculo-hint" style="font-size:12px;color:#94a3b8;margin-top:4px;">
                            Selecciona un vehículo para consultar su marca, modelo y año.
                        </p>
                        <flux:error name="vehiculo_id" />
                    </div>

                    {{-- Solicitante (2 columnas) --}}
                    <div class="campo-doble">
                        <flux:label>Solicitante <span class="text-red-500" title="Campo obligatorio">(*)</span>
                        </flux:label>
                        <flux:select name="usuario_id" placeholder="Selecciona un solicitante..." required>
                            @foreach ($solicitantes as $solicitante)
                                <flux:select.option value="{{ $solicitante->id }}"
                                    :selected="old('usuario_id') == $solicitante->id">
                                    {{ $solicitante->nombre_usuario }} {{ $solicitante->apellido_paterno }}
                                    {{ $solicitante->apellido_materno }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="usuario_id" />
                    </div>

                    {{-- Fecha y hora de salida --}}
                    <div>
                        <flux:label>Fecha y hora de salida <span class="text-red-500"
                                title="Campo obligatorio">(*)</span></flux:label>
                        <x-fecha-hora-12h name="fecha_salida" :value="old('fecha_salida')" :required="true" />
                        <flux:error name="fecha_salida" />
                    </div>

                    {{-- Fila 2 --}}

                    {{-- Kilometraje inicial: solo lectura, se toma del vehículo (RN-05) --}}
                    <div>
                        <flux:label>Kilometraje inicial</flux:label>
                        <flux:input name="kilometraje_inicial" icon="map" placeholder="Elige un vehículo"
                            readonly />
                        <p id="km-hint" style="font-size:12px;color:#94a3b8;margin-top:4px;">
                            Se toma automáticamente del kilometraje actual del vehículo.</p>
                    </div>

                    {{-- Destino (3 columnas) --}}
                    <div class="campo-triple">
                        <flux:label>Destino <span class="text-red-500" title="Campo obligatorio">(*)</span>
                        </flux:label>
                        <flux:input name="destino_vehiculo" value="{{ old('destino_vehiculo') }}"
                            icon="map-pin" placeholder="Ej. Campeche, Campeche" autocomplete="off" required />
                        <flux:error name="destino_vehiculo" />
                    </div>

                    {{-- Fila 3 --}}

                    {{-- Motivo de uso (fila completa) --}}
                    <div class="campo-completo">
                        <flux:label>Motivo de uso <span class="text-red-500" title="Campo obligatorio">(*)</span>
                        </flux:label>
                        <flux:input name="motivo_uso" value="{{ old('motivo_uso') }}" icon="clipboard-document-list"
                            placeholder="Ej. Visita a empresa vinculada, traslado de alumnos…" autocomplete="off"
                            required />
                        <flux:error name="motivo_uso" />
                    </div>

                </div>

                {{-- Botones --}}
                <div class="flex justify-end gap-2" style="margin-top: 32px;">
                    <a href="{{ url('/admin/asignaciones') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-xs font-semibold rounded transition">
                        <i class="fas fa-arrow-left mr-2"></i> Cancelar
                    </a>
                    <button type="submit" @disabled($sinVehiculos || $sinSolicitantes)
                        class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded transition"
                        style="{{ $sinVehiculos || $sinSolicitantes ? 'opacity:.5;cursor:not-allowed;' : '' }}">
                        <i class="fas fa-save mr-2"></i> Registrar salida
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Kilometraje vigente de cada vehículo disponible
        const kilometrajes = @json($kilometrajes);

        // Recibir la información de marca, modelo y año.
        const datosVehiculos = @json($datosVehiculos);

        // ELEMENTOS DEL FORMULARIO
        const selectVehiculo = document.querySelector('select[name="vehiculo_id"]');
        const inputKm = document.querySelector('input[name="kilometraje_inicial"]');
        const pista = document.getElementById('km-hint');

        // FUNCIÓN PARA OBTENER INFORMACIÓN DEL VEHÍCULO
        function obtenerInformacionVehiculo() {

            if (!selectVehiculo) return;
            const vehiculoId = selectVehiculo.value;
            const vehiculo = datosVehiculos[vehiculoId];
            const pistaVehiculo = document.getElementById('vehiculo-hint');

            if (vehiculo) {
                pistaVehiculo.textContent =
                    'Marca: ' + vehiculo.marca +
                    ' | Modelo: ' + vehiculo.modelo +
                    ' | Año: ' + vehiculo.anio;

            } else {
                pistaVehiculo.textContent =
                    'Selecciona un vehículo para consultar su marca, modelo y año.';
            }
        }

        // FUNCIÓN PARA ACTUALIZAR KILOMETRAJE
        function actualizarKilometraje() {
            if (!selectVehiculo || !inputKm) return;

            const km = kilometrajes[selectVehiculo.value];

            if (km === undefined) {
                inputKm.value = '';
                pista.textContent = 'Se toma automáticamente del kilometraje actual del vehículo.';
                return;
            }

            // No se captura: es el kilometraje vigente del vehículo (RN-05)
            inputKm.value = Math.ceil(km);
            pista.textContent = 'Kilometraje actual del vehículo: ' + km.toLocaleString('es-MX') + ' km.';
        }

        if (selectVehiculo) {
            selectVehiculo.addEventListener('change', function () {

                // Ejecutar la función que muestra marca, modelo y año.
                obtenerInformacionVehiculo();

                // Ejecutar la función que actualiza el kilometraje.
                actualizarKilometraje();

            });
            obtenerInformacionVehiculo();
            actualizarKilometraje();
        }

        // ---------------------------------------------------------------
        // Aviso antes de guardar: si el solicitante ya tiene asignaciones activas
        // (sin devolución), se muestra cuántas son y sus fechas para que se confirme.
        // ---------------------------------------------------------------
        const asignacionesPrevias = @json($asignacionesPrevias);

        const formulario = document.getElementById('form-asignacion');
        const selectSolicitante = document.querySelector('select[name="usuario_id"]');

        // Evita que un destino con símbolos rompa el HTML del aviso
        function escapar(texto) {
            const div = document.createElement('div');
            div.textContent = texto ?? '';
            return div.innerHTML;
        }

        formulario.addEventListener('submit', function (evento) {
            if (!window.Swal || formulario.dataset.confirmado === '1') return;

            const previas = asignacionesPrevias[selectSolicitante.value];

            // Sin asignaciones previas: se guarda directo
            if (!previas || previas.length === 0) return;

            evento.preventDefault();

            const nombre = selectSolicitante.options[selectSolicitante.selectedIndex].text
                .replace(/\s+/g, ' ').trim();

            const cantidad = previas.length;
            const textoCantidad = cantidad === 1 ? '1 asignación activa' : cantidad + ' asignaciones activas';

            const lista = previas.map(function (a) {
                return '<li style="padding:6px 0;border-bottom:1px solid #e5e7eb;">' +
                    '<b>' + escapar(a.salida) + '</b> · ' + escapar(a.destino) + '</b> · ' + escapar(a.motivo_uso) +'</li>';
            }).join('');

            Swal.fire({
                icon: 'info',
                title: escapar(nombre) + ' ya tiene ' + textoCantidad,
                html: '<ul style="list-style:none;padding:0;margin:0;text-align:left;font-size:14px;max-height:220px;overflow-y:auto;">' +
                    lista + '</ul><br>¿Deseas registrar otra asignación?',
                showCancelButton: true,
                confirmButtonText: 'Sí, registrar',
                cancelButtonText: 'Revisar',
            }).then(function (resultado) {
                if (resultado.isConfirmed) {
                    formulario.dataset.confirmado = '1';
                    formulario.submit();
                }
            });
        });

    </script>
</x-layouts.app>