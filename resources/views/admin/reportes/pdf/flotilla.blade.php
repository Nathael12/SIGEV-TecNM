@extends('admin.reportes.pdf.layout')

@section('contenido')
    @php
        $colores = [
            'Disponible' => '#15803d',
            'Asignado' => '#1d4ed8',
            'En mantenimiento' => '#b45309',
            'Fuera de servicio' => '#c2410c',
            'Baja' => '#374151',
        ];
    @endphp

    <div class="bloque">
        <b>Filtros aplicados:</b>
        @if ($filtros['estado'] || $filtros['buscar'])
            @if ($filtros['estado']) Estado: {{ $filtros['estado'] }} @endif
            @if ($filtros['estado'] && $filtros['buscar']) · @endif
            @if ($filtros['buscar']) Búsqueda: "{{ $filtros['buscar'] }}" @endif
        @else
            Ninguno (flotilla completa)
        @endif
        <br>
        <b>Total:</b> {{ $vehiculos->count() }} {{ $vehiculos->count() === 1 ? 'vehículo' : 'vehículos' }}
        @foreach ($estados as $estado)
            @if (($totales[$estado] ?? 0) > 0)
                · {{ $estado }}: {{ $totales[$estado] }}
            @endif
        @endforeach
    </div>

    <table class="datos">
        <thead>
            <tr>
                <th>Nro</th>
                <th>Núm. económico</th>
                <th>Placas</th>
                <th>Vehículo</th>
                <th>Año</th>
                <th>Tipo</th>
                <th>Combustible</th>
                <th>Kilometraje</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($vehiculos as $vehiculo)
                @php $km = $vehiculo->kilometraje_vigente; @endphp
                <tr>
                    <td class="centro">{{ $loop->iteration }}</td>
                    <td class="centro">{{ $vehiculo->numero_economico }}</td>
                    <td class="centro">{{ $vehiculo->placas }}</td>
                    <td>{{ $vehiculo->marca }} {{ $vehiculo->modelo }}</td>
                    <td class="centro">{{ $vehiculo->anio }}</td>
                    <td class="centro">{{ $vehiculo->getAttribute('tipo vehiculo') }}</td>
                    <td class="centro">{{ $vehiculo->getAttribute('tipo combustible') }}</td>
                    <td class="centro">{{ $km !== null ? number_format($km) . ' km' : '—' }}</td>
                    <td class="centro">
                        <b style="color: {{ $colores[$vehiculo->estado] ?? '#374151' }};">{{ $vehiculo->estado }}</b>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="vacio">No hay vehículos que coincidan con los filtros.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection