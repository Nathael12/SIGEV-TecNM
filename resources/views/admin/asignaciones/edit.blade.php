<x-layouts.app title="Corregir asignación">

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
        $finalizada = ! $asignacion->activa;
    @endphp

    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Corregir asignación</flux:heading>
        <p style="font-size:14px;color:#94a3b8;margin-top:4px;">
            Vehículo {{ $vehiculo->numero_economico }} · {{ $vehiculo->marca }} {{ $vehiculo->modelo }}
            {{ $vehiculo->anio }}. El vehículo y el kilometraje inicial no se pueden modificar.
        </p>
        <br>
        <flux:separator variant="subtle" />
    </div>

    <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
        <form action="{{ url('/admin/asignacion/' . $asignacion->id_asignacion) }}" method="POST" autocomplete="off">
            @csrf
            @method('PUT')

            <div class="p-6">
                <div class="grid-asignaciones">

                    {{-- Fila 1 --}}
                    <div>
                        <flux:label>Vehículo</flux:label>
                        <flux:input icon="truck" value="{{ $vehiculo->numero_economico }} · {{ $vehiculo->placas }}"
                            readonly />
                    </div>

                    <div class="campo-doble">
                        <flux:label>Solicitante <span class="text-red-500" title="Campo obligatorio">(*)</span>
                        </flux:label>
                        <flux:select name="usuario_id" required>
                            @foreach ($solicitantes as $solicitante)
                                <flux:select.option value="{{ $solicitante->id }}"
                                    :selected="old('usuario_id', $asignacion->usuario_id) == $solicitante->id">
                                    {{ $solicitante->nombre_usuario }} {{ $solicitante->apellido_paterno }}
                                    {{ $solicitante->apellido_materno }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="usuario_id" />
                    </div>

                    <div>
                        <flux:label>Estado de la asignación</flux:label>
                        <flux:input :icon="$asignacion->activa ? 'clock' : 'check-circle'"
                            value="{{ $asignacion->estado_asignacion }}" readonly />
                    </div>

                    {{-- Fila 2 --}}
                    <div>
                        <flux:label>Fecha y hora de salida <span class="text-red-500"
                                title="Campo obligatorio">(*)</span></flux:label>
                        <x-fecha-hora-12h name="fecha_salida"
                            :value="old('fecha_salida', $asignacion->fecha_salida->format('Y-m-d\TH:i'))"
                            :required="true" />
                        <flux:error name="fecha_salida" />
                    </div>

                    <div>
                        <flux:label>Fecha y hora de regreso @if ($finalizada)
                                <span class="text-red-500" title="Campo obligatorio">(*)</span>
                            @endif
                        </flux:label>
                        @if ($finalizada)
                            <x-fecha-hora-12h name="fecha_regreso"
                                :value="old('fecha_regreso', $asignacion->fecha_regreso->format('Y-m-d\TH:i'))"
                                :required="true" />
                            <flux:error name="fecha_regreso" />
                        @else
                            <flux:input icon="calendar-days" value="Pendiente" readonly />
                        @endif
                    </div>

                    <div>
                        <flux:label>Kilometraje inicial</flux:label>
                        <flux:input icon="map" value="{{ number_format($asignacion->kilometraje_inicial) }} km"
                            readonly />
                    </div>

                    <div>
                        <flux:label>Kilometraje final @if ($finalizada)
                                <span class="text-red-500" title="Campo obligatorio">(*)</span>
                            @endif
                        </flux:label>
                        @if ($finalizada)
                            <flux:input name="kilometraje_final" type="number" step="1"
                                min="{{ $asignacion->kilometraje_inicial }}"
                                value="{{ old('kilometraje_final', $asignacion->kilometraje_final) }}" icon="map"
                                placeholder="Ej. 15180" autocomplete="off" required />
                            <flux:error name="kilometraje_final" />
                        @else
                            <flux:input icon="map" value="Pendiente" readonly />
                        @endif
                    </div>

                    {{-- Fila 3 --}}
                    <div class="campo-doble">
                        <flux:label>Destino <span class="text-red-500" title="Campo obligatorio">(*)</span>
                        </flux:label>
                        <flux:input name="destino_vehiculo"
                            value="{{ old('destino_vehiculo', $asignacion->destino_vehiculo) }}" icon="map-pin"
                            placeholder="Ej. Campeche, Campeche" autocomplete="off" required />
                        <flux:error name="destino_vehiculo" />
                    </div>

                    <div class="campo-doble">
                        <flux:label>Motivo de uso <span class="text-red-500" title="Campo obligatorio">(*)</span>
                        </flux:label>
                        <flux:input name="motivo_uso" value="{{ old('motivo_uso', $asignacion->motivo_uso) }}"
                            icon="clipboard-document-list" placeholder="Ej. Visita a empresa vinculada"
                            autocomplete="off" required />
                        <flux:error name="motivo_uso" />
                    </div>

                </div>

                {{-- Botones --}}
                <div class="flex justify-end gap-2" style="margin-top: 32px;">
                    <a href="{{ url('/admin/asignaciones') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-xs font-semibold rounded transition">
                        <i class="fas fa-arrow-left mr-2"></i> Cancelar
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold rounded transition">
                        <i class="fas fa-save mr-2"></i> Actualizar
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>