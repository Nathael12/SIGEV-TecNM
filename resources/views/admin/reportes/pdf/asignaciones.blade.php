@extends('admin.reportes.pdf.layout')

@section('contenido')
    <div class="bloque">
        <b>Vehículo:</b>
        @if ($vehiculo)
            {{ $vehiculo->numero_economico }} · {{ $vehiculo->placas }} — {{ $vehiculo->marca }}
            {{ $vehiculo->modelo }} {{ $vehiculo->anio }}
        @else
            Todos
        @endif
        &nbsp;·&nbsp;
        <b>Periodo (fecha de salida):</b>
        @if ($filtros['desde'] || $filtros['hasta'])
            {{ $filtros['desde'] ? \Illuminate\Support\Carbon::parse($filtros['desde'])->format('d/m/Y') : 'sin límite' }}
            al
            {{ $filtros['hasta'] ? \Illuminate\Support\Carbon::parse($filtros['hasta'])->format('d/m/Y') : 'sin límite' }}
        @else
            Todo el historial
        @endif
        <br>
        <b>Asignaciones:</b> {{ $totales['asignaciones'] }}
        · <b>Activas:</b> {{ $totales['activas'] }}
        · <b>Finalizadas:</b> {{ $totales['finalizadas'] }}
        · <b>Km recorridos (finalizadas):</b> {{ number_format($totales['km']) }} km
    </div>

    <table class="datos">
        <thead>
            <tr>
                <th>Nro</th>
                <th>Vehículo</th>
                <th>Solicitante</th>
                <th>Salida</th>
                <th>Regreso</th>
                <th>Destino</th>
                <th>Motivo</th>
                <th>Km inicial</th>
                <th>Km final</th>
                <th>Recorridos</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($asignaciones as $asignacion)
                <tr>
                    <td class="centro">{{ $loop->iteration }}</td>
                    <td class="centro">{{ $asignacion->vehiculo?->numero_economico }}<br>{{ $asignacion->vehiculo?->placas }}</td>
                    <td>
                        {{ $asignacion->solicitante?->nombre_usuario }}
                        {{ $asignacion->solicitante?->apellido_paterno }}
                        {{ $asignacion->solicitante?->apellido_materno }}
                    </td>
                    <td class="centro">{{ $asignacion->fecha_salida->format('d/m/Y h:i A') }}</td>
                    <td class="centro">{{ $asignacion->fecha_regreso ? $asignacion->fecha_regreso->format('d/m/Y h:i A') : '—' }}</td>
                    <td>{{ $asignacion->destino_vehiculo }}</td>
                    <td>{{ $asignacion->motivo_uso }}</td>
                    <td class="centro">{{ number_format($asignacion->kilometraje_inicial) }}</td>
                    <td class="centro">{{ $asignacion->kilometraje_final !== null ? number_format($asignacion->kilometraje_final) : '—' }}</td>
                    <td class="centro">{{ $asignacion->kilometros_recorridos !== null ? number_format($asignacion->kilometros_recorridos) . ' km' : '—' }}</td>
                    <td class="centro">
                        <b style="color: {{ $asignacion->activa ? '#1d4ed8' : '#15803d' }};">{{ $asignacion->estado_asignacion }}</b>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="vacio">No hay asignaciones que coincidan con los filtros.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection