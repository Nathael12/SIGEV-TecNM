@props(['tipo', 'vehiculo' => null, 'alerta' => null, 'diasRestantes' => null])

@if ($tipo === 'kilometraje')
    @php($vencido = $vehiculo->condicion_mantenimiento === 'Vencido')
    @php($mantenimiento = $vehiculo->ultimoMantenimiento)
    <article role="status" class="maintenance-alert flex-col items-stretch justify-start {{ $vencido ? 'maintenance-alert--red' : 'maintenance-alert--amber' }}">
        <div class="flex items-center justify-between gap-2">
            <p class="text-[10px] font-bold uppercase tracking-wide {{ $vencido ? 'text-red-700 dark:text-red-300' : 'text-amber-700 dark:text-amber-300' }}">Por kilometraje</p>
            <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold leading-tight {{ $vencido ? 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200' }}">{{ $vencido ? 'Vencido' : 'Próximo' }}</span>
        </div>

        @can('ver detalle vehiculo')
            <a href="{{ route('admin.vehiculos.show', $vehiculo->id_vehiculo) }}" class="truncate text-sm font-semibold text-blue-800 underline-offset-2 hover:underline dark:text-blue-200" title="{{ $vehiculo->marca }} {{ $vehiculo->modelo }}">{{ $vehiculo->marca }} {{ $vehiculo->modelo }} ({{ $vehiculo->numero_economico }})</a>
        @else
            <strong class="truncate text-sm font-semibold">{{ $vehiculo->marca }} {{ $vehiculo->modelo }} ({{ $vehiculo->numero_economico }})</strong>
        @endcan

        <p class="truncate text-[11px] text-slate-600 dark:text-zinc-300" title="{{ $mantenimiento->categoria_mantenimiento }} · Placas {{ $vehiculo->placas }}">{{ $mantenimiento->categoria_mantenimiento }} · {{ $vehiculo->placas }}</p>

        <p class="text-[11px] font-medium {{ $vencido ? 'text-red-700 dark:text-red-300' : 'text-amber-800 dark:text-amber-200' }}">
            Actual {{ number_format((float) $vehiculo->{'kilometraje actual'}) }} km · Próximo {{ number_format($mantenimiento->proximo_mantenimiento_km) }} km
            @if ($vehiculo->kilometros_restantes > 0)
                · Faltan {{ number_format($vehiculo->kilometros_restantes) }} km
            @elseif ($vehiculo->kilometros_restantes < 0)
                · Superado por {{ number_format(abs($vehiculo->kilometros_restantes)) }} km
            @else
                · Kilometraje alcanzado
            @endif
        </p>
    </article>
@else
    @php($vencida = $alerta->tipo === 'vencida')
    @php($veh = $alerta->mantenimiento->vehiculo)
    <div role="status" class="maintenance-alert flex-col items-stretch justify-start {{ $vencida ? 'maintenance-alert--red' : 'maintenance-alert--amber' }}">
        <div class="flex items-center justify-between gap-2">
            <p class="text-[10px] font-bold uppercase tracking-wide {{ $vencida ? 'text-red-700 dark:text-red-300' : 'text-amber-700 dark:text-amber-300' }}">Por fecha</p>
            <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold leading-tight {{ $vencida ? 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200' }}">
                @if ($diasRestantes < 0)
                    Hace {{ abs($diasRestantes) }} {{ abs($diasRestantes) === 1 ? 'día' : 'días' }}
                @elseif ($diasRestantes === 0)
                    Hoy
                @else
                    En {{ $diasRestantes }} {{ $diasRestantes === 1 ? 'día' : 'días' }}
                @endif
            </span>
        </div>

        @can('ver detalle vehiculo')
            <a href="{{ route('admin.vehiculos.show', $veh->id_vehiculo) }}" class="truncate text-sm font-semibold text-blue-800 underline-offset-2 hover:underline dark:text-blue-200" title="{{ $veh->marca }} {{ $veh->modelo }}">{{ $veh->marca }} {{ $veh->modelo }} ({{ $veh->numero_economico }})</a>
        @else
            <strong class="truncate text-sm font-semibold">{{ $veh->marca }} {{ $veh->modelo }} ({{ $veh->numero_economico }})</strong>
        @endcan

        <p class="truncate text-[11px] font-medium text-slate-700 dark:text-zinc-200">{{ $vencida ? 'Mantenimiento vencido' : 'Próximo mantenimiento' }} · {{ $alerta->mantenimiento->categoria_mantenimiento }} · {{ $alerta->fecha_mantenimiento->format('d/m/Y') }}</p>

        @if ($alerta->mantenimiento->descripcion_mantenimiento)
            <p class="line-clamp-1 text-[11px] text-slate-500 dark:text-zinc-400" title="{{ $alerta->mantenimiento->descripcion_mantenimiento }}">{{ $alerta->mantenimiento->descripcion_mantenimiento }}</p>
        @endif

        @can('atender alertas mantenimiento')
            <form method="POST" action="{{ route('admin.alertas.atender', $alerta) }}" class="self-end">
                @csrf
                <button type="submit" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-2 py-1 text-[11px] font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800">Marcar como atendida</button>
            </form>
        @endcan
    </div>
@endif