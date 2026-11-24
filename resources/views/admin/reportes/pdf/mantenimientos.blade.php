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
        <b>Periodo (fecha del servicio):</b>
        @if ($filtros['desde'] || $filtros['hasta'])
            {{ $filtros['desde'] ? \Illuminate\Support\Carbon::parse($filtros['desde'])->format('d/m/Y') : 'sin límite' }}
            al
            {{ $filtros['hasta'] ? \Illuminate\Support\Carbon::parse($filtros['hasta'])->format('d/m/Y') : 'sin límite' }}
        @else
            Todo el historial
        @endif
        <br>
        <b>Mantenimientos:</b> {{ $totales['mantenimientos'] }}
        · <b>Costo total:</b> ${{ number_format($totales['costo'], 2) }}
        · <b>Último servicio:</b> {{ $totales['ultimo'] ? $totales['ultimo']->format('d/m/Y') : '—' }}
    </div>

    <table class="datos">
        <thead>
            <tr>
                <th>Nro</th>
                <th>Fecha</th>
                <th>Vehículo</th>
                <th>Categoría</th>
                <th>Descripción</th>
                <th>Kilometraje</th>
                <th>Monto</th>
                <th>Próximo mantenimiento</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($mantenimientos as $mantenimiento)
                <tr>
                    <td class="centro">{{ $loop->iteration }}</td>
                    <td class="centro">{{ $mantenimiento->fecha_mantenimiento->format('d/m/Y') }}</td>
                    <td class="centro">{{ $mantenimiento->vehiculo?->numero_economico }}<br>{{ $mantenimiento->vehiculo?->placas }}</td>
                    <td>{{ $mantenimiento->categoria_mantenimiento }}</td>
                    <td>{{ $mantenimiento->descripcion_mantenimiento }}</td>
                    <td class="centro">{{ number_format($mantenimiento->kilometraje_mantenimiento) }} km</td>
                    <td class="centro">${{ number_format($mantenimiento->monto_mantenimiento, 2) }}</td>
                    <td class="centro">
                        @if ($mantenimiento->proximo_mantenimiento_fecha || $mantenimiento->proximo_mantenimiento_km !== null)
                            {{ $mantenimiento->proximo_mantenimiento_fecha ? $mantenimiento->proximo_mantenimiento_fecha->format('d/m/Y') : '' }}
                            @if ($mantenimiento->proximo_mantenimiento_fecha && $mantenimiento->proximo_mantenimiento_km !== null)
                                <br>
                            @endif
                            {{ $mantenimiento->proximo_mantenimiento_km !== null ? number_format($mantenimiento->proximo_mantenimiento_km) . ' km' : '' }}
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="vacio">No hay mantenimientos que coincidan con los filtros.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection