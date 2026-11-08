<x-layouts.app title="Documentos">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">
            @if ($vehiculo)
                Documentos del vehículo {{ $vehiculo->numero_economico }}
            @else
                Documentos de vehículos
            @endif
        </flux:heading>
        @if ($vehiculo)
            <p style="font-size:14px;color:#94a3b8;margin-top:4px;">
                {{ $vehiculo->marca }} {{ $vehiculo->modelo }} {{ $vehiculo->anio }} · Placas {{ $vehiculo->placas }}
            </p>
        @endif
        <br>
        <flux:separator variant="subtle" />
    </div>

    {{-- Alerta de vencimientos (RF-14) --}}
    @if ($vencidos > 0 || $porVencer > 0)
        <div
            style="background-color:#fef3c7;color:#92400e;border:1px solid #fcd34d;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:14px;">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            @if ($vencidos > 0)
                <strong>{{ $vencidos }}</strong> documento(s) vencido(s).
            @endif
            @if ($porVencer > 0)
                <strong>{{ $porVencer }}</strong> documento(s) por vencer en los próximos
                {{ \App\Models\Documento::DIAS_ALERTA }} días.
            @endif
        </div>
    @endif

    <div style="display:flex;justify-content:space-between;gap:8px;">
        <a href="{{ route('admin.vehiculos.index') }}"
            class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-xs font-semibold rounded transition">
            <i class="fas fa-arrow-left mr-2"></i>
            Volver a vehículos
        </a>
        @if ($vehiculo && auth()->user()->can('ver formulario crear documento'))
            <a href="{{ url('/admin/documentos/create?vehiculo=' . $vehiculo->getKey()) }}"
                class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded transition">
                <i class="fas fa-plus mr-2"></i>
                Crear nuevo
            </a>
        @endif
    </div>

    @php
        $estilos = [
            'Vigente' => 'background-color:#dcfce7;color:#15803d',
            'Por vencer' => 'background-color:#fef3c7;color:#b45309',
            'Vencido' => 'background-color:#fee2e2;color:#b91c1c',
        ];
    @endphp

    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800"
        style="margin-top: 24px;">
        <table class="min-w-full border-collapse">
            <thead class="bg-gray-50 dark:bg-zinc-900 text-center">
                <tr>
                    @foreach (['Nro', 'Vehículo', 'Descripción', 'Vigencia inicial', 'Vigencia final', 'Importe', 'Vigencia', 'Archivo', 'Acciones'] as $columna)
                        <th
                            class="px-6 py-3 border-x border-b border-gray-200 dark:border-zinc-700 text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ $columna }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-zinc-800">
                @forelse ($documentos as $documento)
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition">
                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            {{ $loop->iteration }}</td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            {{ $documento->vehiculo->numero_economico }} · {{ $documento->vehiculo->placas }}</td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ $documento->descripcion_documento }}</td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            {{ $documento->vigencia_inicio->format('d/m/Y') }}</td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            {{ $documento->vigencia_fin->format('d/m/Y') }}</td>

                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            ${{ number_format($documento->importe, 2) }}</td>

                        {{-- Vigencia (RF-14) --}}
                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-center">
                            <span style="{{ $estilos[$documento->estado_vigencia] }}"
                                class="inline-block px-3 py-1 text-xs font-semibold rounded-full">
                                {{ $documento->estado_vigencia }}
                            </span>
                        </td>

                        {{-- Archivo --}}
                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            @if ($documento->ruta_archivo)
                                @if ($documento->tipo_archivo === 'application/pdf')
                                    <i class="fas fa-file-pdf mr-1" style="color:#ef4444"></i>
                                @else
                                    <i class="fas fa-file-image mr-1" style="color:#3b82f6"></i>
                                @endif
                                {{ \Illuminate\Support\Str::limit($documento->nombre_original, 22) }}
                            @else
                                <span style="color:#94a3b8">Sin archivo</span>
                            @endif
                        </td>

                        {{-- Acciones --}}
                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-center">
                            <div class="flex justify-center gap-2">

                                @can('ver detalle documento')
                                    <a href="{{ url('/admin/documento/' . $documento->id_documento) }}"
                                        class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-xs font-semibold rounded transition">
                                        <i class="fas fa-eye mr-2"></i> Ver
                                    </a>
                                @endcan

                                @can('ver formulario editar documento')
                                    <a href="{{ url('/admin/documento/' . $documento->id_documento . '/edit') }}"
                                        class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold rounded transition">
                                        <i class="fas fa-pencil-alt mr-2"></i> Editar
                                    </a>
                                @endcan

                                @if ($documento->ruta_archivo && auth()->user()->can('descargar documento'))
                                    <a href="{{ url('/admin/documento/' . $documento->id_documento . '/descargar') }}"
                                        class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded transition">
                                        <i class="fas fa-download mr-2"></i> Descargar
                                    </a>
                                @endif

                                @can('eliminar documento')
                                    <form action="{{ url('/admin/documento/' . $documento->id_documento) }}" method="post"
                                        id="miFormulario{{ $documento->id_documento }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-xs font-semibold rounded transition"
                                            onclick="preguntar({{ $documento->id_documento }}, event)">
                                            <i class="fas fa-trash-alt mr-2"></i> Eliminar
                                        </button>
                                    </form>
                                @endcan

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9"
                            class="px-3 py-6 border border-gray-200 dark:border-zinc-700 text-center text-sm text-gray-500">
                            No hay documentos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        function preguntar(id, event) {
            event.preventDefault();

            Swal.fire({
                title: '¿Desea eliminar este documento?',
                text: '',
                icon: 'question',
                showDenyButton: true,
                confirmButtonText: 'Eliminar',
                confirmButtonColor: '#a5161d',
                denyButtonColor: '#270a0a',
                denyButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('miFormulario' + id).submit();
                }
            });
        }
    </script>

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