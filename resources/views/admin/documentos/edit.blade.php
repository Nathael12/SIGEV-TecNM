<x-layouts.app title="Editar documento">

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
        // Estado actual del archivo adjunto
        $tieneArchivo = (bool) $documento->ruta_archivo;
        $esImagenActual = $tieneArchivo && str_starts_with((string) $documento->tipo_archivo, 'image/');
        $urlActual = url('/admin/documento/' . $documento->id_documento . '/archivo');

        $bytes = $documento->tamano_archivo ?? 0;
        $tamanoActual = $bytes >= 1048576
            ? number_format($bytes / 1048576, 2) . ' MB'
            : number_format($bytes / 1024, 1) . ' KB';

        $textoActual = $tieneArchivo
            ? 'Archivo actual: ' . $documento->nombre_original . ' (' . $tamanoActual . '). Si no eliges otro, se conserva.'
            : 'Este documento no tiene archivo. Formatos: PDF, JPG, PNG (Máx. 5 MB)';
    @endphp

    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Editar documento</flux:heading>
        <p style="font-size:14px;color:#94a3b8;margin-top:4px;">
            Vehículo {{ $documento->vehiculo->numero_economico }} · {{ $documento->vehiculo->marca }}
            {{ $documento->vehiculo->modelo }} {{ $documento->vehiculo->anio }} · Placas
            {{ $documento->vehiculo->placas }}
        </p>
        <br>
        <flux:separator variant="subtle" />
    </div>

    <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
        {{-- enctype multipart/form-data es obligatorio para subir archivos --}}
        <form action="{{ url('/admin/documento/' . $documento->id_documento) }}" method="POST"
            enctype="multipart/form-data" autocomplete="off">
            @csrf
            @method('PUT')

            <div class="p-6">
                <div class="grid-documentos">

                    {{-- Fila 1 --}}

                    {{-- Vehículo (fijo: un documento no cambia de vehículo) --}}
                    <div>
                        <flux:label>Vehículo</flux:label>
                        <flux:input icon="truck"
                            value="{{ $documento->vehiculo->numero_economico }} · {{ $documento->vehiculo->placas }}"
                            readonly />
                    </div>

                    {{-- Descripción (2 columnas) --}}
                    <div class="campo-doble">
                        <flux:label>Descripción del documento <span class="text-red-500"
                                title="Campo obligatorio">(*)</span></flux:label>
                        <flux:input name="descripcion_documento"
                            value="{{ old('descripcion_documento', $documento->descripcion_documento) }}"
                            icon="document-text" placeholder="Ej. Póliza de seguro 2026" autocomplete="off"
                            required />
                        <flux:error name="descripcion_documento" />
                    </div>

                    {{-- Importe --}}
                    <div>
                        <flux:label>Importe <span class="text-red-500" title="Campo obligatorio">(*)</span>
                        </flux:label>
                        <flux:input name="importe" type="number" step="0.01" min="0"
                            value="{{ old('importe', $documento->importe) }}" icon="banknotes"
                            placeholder="Ej. 4500.00" autocomplete="off" required />
                        <flux:error name="importe" />
                    </div>

                    {{-- Fila 2 --}}

                    {{-- Vigencia inicial --}}
                    <div>
                        <flux:label>Vigencia inicial <span class="text-red-500" title="Campo obligatorio">(*)</span>
                        </flux:label>
                        <flux:input name="vigencia_inicio" type="date"
                            value="{{ old('vigencia_inicio', $documento->vigencia_inicio->format('Y-m-d')) }}"
                            icon="calendar-days" required />
                        <flux:error name="vigencia_inicio" />
                    </div>

                    {{-- Vigencia final --}}
                    <div>
                        <flux:label>Vigencia final <span class="text-red-500" title="Campo obligatorio">(*)</span>
                        </flux:label>
                        <flux:input name="vigencia_fin" type="date"
                            value="{{ old('vigencia_fin', $documento->vigencia_fin->format('Y-m-d')) }}"
                            icon="calendar-days" required />
                        <flux:error name="vigencia_fin" />
                    </div>

                    {{-- Archivo adjunto: opcional al editar (2 columnas) --}}
                    <div class="campo-doble">
                        <flux:label>Reemplazar archivo (opcional)</flux:label>
                        <div style="display:flex;align-items:center;gap:16px;margin-top:4px;">

                            {{-- Vista previa --}}
                            <div
                                style="width:96px;height:96px;border:2px dashed #94a3b8;border-radius:12px;overflow:hidden;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <img id="image-preview" src="{{ $esImagenActual ? $urlActual : '#' }}"
                                    alt="Vista previa"
                                    style="display:{{ $esImagenActual ? 'block' : 'none' }};width:100%;height:100%;object-fit:cover;">
                                <span id="placeholder-icon"
                                    style="display:{{ $esImagenActual ? 'none' : 'flex' }};opacity:.5;">
                                    <flux:icon name="document-text" style="width:40px;height:40px;" />
                                </span>
                            </div>

                            <div style="flex:1;min-width:0;">
                                <input type="file" name="archivo" id="archivo-input"
                                    accept=".pdf,.jpg,.jpeg,.png,image/*,application/pdf" style="display:none;">
                                <label for="archivo-input"
                                    style="cursor:pointer;display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border:1px solid #94a3b8;border-radius:8px;font-size:14px;font-weight:700;">
                                    <flux:icon name="cloud-arrow-up" variant="micro" />
                                    <span>Cambiar archivo</span>
                                </label>
                                <p id="file-name"
                                    style="font-size:12px;color:#94a3b8;margin-top:8px;font-style:italic;word-break:break-all;">
                                    {{ $textoActual }}</p>
                            </div>
                        </div>
                        <flux:error name="archivo" />
                    </div>

                </div>

                {{-- Botones --}}
                <div class="flex justify-end gap-2" style="margin-top: 32px;">
                    <a href="{{ url('/admin/documentos?vehiculo=' . $documento->vehiculo_id) }}"
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

    <script>
        // Estado original del archivo, para restaurarlo si cancelan la selección
        const original = {
            esImagen: @json($esImagenActual),
            url: @json($urlActual),
            texto: @json($textoActual),
        };

        document.getElementById('archivo-input').addEventListener('change', function () {
            const archivo = this.files[0];
            const imagen = document.getElementById('image-preview');
            const icono = document.getElementById('placeholder-icon');
            const nombre = document.getElementById('file-name');

            // Sin selección: se vuelve a mostrar el archivo actual
            if (!archivo) {
                nombre.textContent = original.texto;
                if (original.esImagen) {
                    imagen.src = original.url;
                    imagen.style.display = 'block';
                    icono.style.display = 'none';
                } else {
                    imagen.style.display = 'none';
                    icono.style.display = 'flex';
                }
                return;
            }

            const mb = (archivo.size / 1024 / 1024).toFixed(2);
            nombre.textContent = 'Nuevo archivo: ' + archivo.name + ' (' + mb + ' MB)';

            if (archivo.type.startsWith('image/')) {
                imagen.src = URL.createObjectURL(archivo);
                imagen.style.display = 'block';
                icono.style.display = 'none';
            } else {
                imagen.style.display = 'none';
                icono.style.display = 'flex';
            }
        });
    </script>
</x-layouts.app>