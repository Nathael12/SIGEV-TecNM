<x-layouts.app title="Ver documento">

    {{-- Cuadrícula propia: no depende de que Tailwind compile clases nuevas --}}
    <style>
        .grid-documentos {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 24px 16px;
        }

        .grid-documentos>div {
            min-width: 0;
        }

        @media (min-width: 640px) {
            .grid-documentos {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .grid-documentos .campo-doble {
                grid-column: span 2 / span 2;
            }
        }

        @media (min-width: 1024px) {
            .grid-documentos {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
    </style>

    @php
        // Vigencia (RF-14)
        $estadoVigencia = $documento->estado_vigencia;
        $iconoVigencia = [
            'Vigente' => 'check-circle',
            'Por vencer' => 'exclamation-triangle',
            'Vencido' => 'x-circle',
        ][$estadoVigencia];

        $dias = (int) now()->startOfDay()->diffInDays($documento->vigencia_fin, false);
        if ($dias < 0) {
            $tiempoRestante = 'Venció hace ' . abs($dias) . ($dias === -1 ? ' día' : ' días');
        } elseif ($dias === 0) {
            $tiempoRestante = 'Vence hoy';
        } else {
            $tiempoRestante = 'Vence en ' . $dias . ($dias === 1 ? ' día' : ' días');
        }

        // Metadatos del archivo (RF-13)
        $bytes = $documento->tamano_archivo ?? 0;
        $tamano = $bytes >= 1048576
            ? number_format($bytes / 1048576, 2) . ' MB'
            : number_format($bytes / 1024, 1) . ' KB';

        $esImagen = str_starts_with((string) $documento->tipo_archivo, 'image/');
        $tipoLegible = $documento->tipo_archivo === 'application/pdf'
            ? 'PDF'
            : strtoupper(str_replace('image/', '', (string) $documento->tipo_archivo));

        $urlArchivo = url('/admin/documento/' . $documento->id_documento . '/archivo');
    @endphp

    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Datos del documento</flux:heading>
        <p style="font-size:14px;color:#94a3b8;margin-top:4px;">
            Vehículo {{ $documento->vehiculo->numero_economico }} · {{ $documento->vehiculo->marca }}
            {{ $documento->vehiculo->modelo }} {{ $documento->vehiculo->anio }}
        </p>
        <br>
        <flux:separator variant="subtle" />
    </div>

    <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
        <div class="p-6">
            <div class="grid-documentos">

                {{-- Fila 1 --}}
                <div>
                    <flux:label>Vehículo</flux:label>
                    <flux:input icon="truck"
                        value="{{ $documento->vehiculo->numero_economico }} · {{ $documento->vehiculo->placas }}"
                        readonly />
                </div>

                <div class="campo-doble">
                    <flux:label>Descripción del documento</flux:label>
                    <flux:input icon="document-text" value="{{ $documento->descripcion_documento }}" readonly />
                </div>

                <div>
                    <flux:label>Importe</flux:label>
                    <flux:input icon="banknotes" value="${{ number_format($documento->importe, 2) }}" readonly />
                </div>

                {{-- Fila 2: vigencia --}}
                <div>
                    <flux:label>Vigencia inicial</flux:label>
                    <flux:input icon="calendar-days" value="{{ $documento->vigencia_inicio->format('d/m/Y') }}"
                        readonly />
                </div>

                <div>
                    <flux:label>Vigencia final</flux:label>
                    <flux:input icon="calendar-days" value="{{ $documento->vigencia_fin->format('d/m/Y') }}"
                        readonly />
                </div>

                <div>
                    <flux:label>Estado de vigencia</flux:label>
                    <flux:input :icon="$iconoVigencia" value="{{ $estadoVigencia }}" readonly />
                </div>

                <div>
                    <flux:label>Tiempo restante</flux:label>
                    <flux:input icon="clock" value="{{ $tiempoRestante }}" readonly />
                </div>

                {{-- Fila 3: metadatos del archivo (RF-13) --}}
                <div class="campo-doble">
                    <flux:label>Nombre original del archivo</flux:label>
                    <flux:input icon="paper-clip" value="{{ $documento->nombre_original ?? 'Sin archivo' }}"
                        readonly />
                </div>

                <div>
                    <flux:label>Tipo de archivo</flux:label>
                    <flux:input icon="document" value="{{ $documento->ruta_archivo ? $tipoLegible : '—' }}"
                        readonly />
                </div>

                <div>
                    <flux:label>Tamaño</flux:label>
                    <flux:input icon="archive-box" value="{{ $documento->ruta_archivo ? $tamano : '—' }}" readonly />
                </div>

            </div>

            {{-- Visor del archivo (consulta, RF-12) --}}
            @if ($documento->ruta_archivo)
                <div style="margin-top: 32px;">
                    <flux:heading size="lg" level="2">Vista previa del archivo</flux:heading>
                    <div style="margin-top:12px;">
                        @if ($esImagen)
                            <img src="{{ $urlArchivo }}" alt="{{ $documento->descripcion_documento }}"
                                style="max-width:100%;max-height:600px;border-radius:8px;border:1px solid #94a3b8;">
                        @else
                            <iframe src="{{ $urlArchivo }}" title="{{ $documento->descripcion_documento }}"
                                style="width:100%;height:600px;border:1px solid #94a3b8;border-radius:8px;"></iframe>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Botones --}}
            <div class="flex justify-end gap-2" style="margin-top: 32px;">
                <a href="{{ url('/admin/documentos?vehiculo=' . $documento->vehiculo_id) }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-xs font-semibold rounded transition">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>

                @if ($documento->ruta_archivo)
                    <a href="{{ $urlArchivo }}" target="_blank"
                        class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-xs font-semibold rounded transition">
                        <i class="fas fa-external-link-alt mr-2"></i> Abrir en otra pestaña
                    </a>

                    @can('descargar documento')
                        <a href="{{ url('/admin/documento/' . $documento->id_documento . '/descargar') }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded transition">
                            <i class="fas fa-download mr-2"></i> Descargar
                        </a>
                    @endcan
                @endif

                
            </div>
        </div>
    </div>
</x-layouts.app>