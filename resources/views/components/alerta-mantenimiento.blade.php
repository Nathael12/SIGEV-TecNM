@props(['tipo', 'vehiculo' => null, 'alerta' => null, 'diasRestantes' => null])

@if ($tipo === 'kilometraje')
    @php($vencido = $vehiculo->condicion_mantenimiento === 'Vencido')
    @php($mantenimiento = $vehiculo->ultimoMantenimiento)
    <article role="status" class="maintenance-alert {{ $vencido ? 'maintenance-alert--red' : 'maintenance-alert--amber' }}">
        <div class="min-w-0 flex-1">
            <p class="mb-0.5 text-[10px] font-bold uppercase tracking-wide {{ $vencido ? 'text-red-700 dark:text-red-300' : 'text-amber-700 dark:text-amber-300' }}">Alerta por kilometraje</p>
            <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 leading-tight">
                @can('ver detalle vehiculo')<a href="{{ route('admin.vehiculos.show', $vehiculo->id_vehiculo) }}" class="font-semibold text-blue-800 underline-offset-2 hover:underline dark:text-blue-200">{{ $vehiculo->numero_economico }} · {{ $vehiculo->marca }} {{ $vehiculo->modelo }}</a>@else<strong class="font-semibold">{{ $vehiculo->numero_economico }} · {{ $vehiculo->marca }} {{ $vehiculo->modelo }}</strong>@endcan
                <span>· Placas {{ $vehiculo->placas }}</span><span>· {{ $mantenimiento->categoria_mantenimiento }}</span>
                <span>· Km actual: {{ number_format((float) $vehiculo->{'kilometraje actual'}) }}</span><span>· Próximo: {{ number_format($mantenimiento->proximo_mantenimiento_km) }}</span>
                @if ($vehiculo->kilometros_restantes > 0)<span>· Faltan {{ number_format($vehiculo->kilometros_restantes) }} km</span>
                @elseif ($vehiculo->kilometros_restantes < 0)<span class="font-semibold text-red-700 dark:text-red-300">· Superado por {{ number_format(abs($vehiculo->kilometros_restantes)) }} km</span>
                @else<span class="font-semibold text-red-700 dark:text-red-300">· Se alcanzó el kilometraje programado</span>@endif
            </div>
        </div>
        <span class="shrink-0 self-end rounded-full px-2 py-1 text-[10px] font-semibold leading-tight sm:self-center {{ $vencido ? 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200' }}">{{ $vencido ? 'Mantenimiento vencido' : 'Próximo mantenimiento' }}</span>
    </article>
@else
    @php($vencida = $alerta->tipo === 'vencida')
    <div role="status" class="maintenance-alert {{ $vencida ? 'maintenance-alert--red' : 'maintenance-alert--amber' }}">
        <div class="min-w-0 flex-1">
            <span class="mb-0.5 block text-[10px] font-bold uppercase tracking-wide {{ $vencida ? 'text-red-700 dark:text-red-300' : 'text-amber-700 dark:text-amber-300' }}">Alerta por fecha</span>
            <div class="flex flex-wrap items-center gap-x-1.5 gap-y-0.5 leading-tight">
                <strong class="{{ $vencida ? 'text-red-800 dark:text-red-200' : 'text-amber-800 dark:text-amber-200' }}">{{ $vencida ? 'Mantenimiento vencido' : 'Próximo mantenimiento' }}</strong>
                @can('ver detalle vehiculo')<a href="{{ route('admin.vehiculos.show', $alerta->mantenimiento->vehiculo->id_vehiculo) }}" class="font-semibold text-blue-800 underline-offset-2 hover:underline dark:text-blue-200">{{ $alerta->mantenimiento->vehiculo->marca }} {{ $alerta->mantenimiento->vehiculo->modelo }} ({{ $alerta->mantenimiento->vehiculo->numero_economico }})</a>@else<strong class="font-semibold">{{ $alerta->mantenimiento->vehiculo->marca }} {{ $alerta->mantenimiento->vehiculo->modelo }} ({{ $alerta->mantenimiento->vehiculo->numero_economico }})</strong>@endcan
                <span>· {{ $alerta->mantenimiento->categoria_mantenimiento }} · {{ $alerta->fecha_mantenimiento->format('d/m/Y') }}</span>
                <span>{{ $alerta->mantenimiento->descripcion_mantenimiento }}</span>
                <span class="font-semibold {{ $vencida ? 'text-red-700 dark:text-red-300' : 'text-amber-800 dark:text-amber-200' }}">
                    @if ($diasRestantes < 0)· Vencido hace {{ abs($diasRestantes) }} {{ abs($diasRestantes) === 1 ? 'día' : 'días' }}
                    @elseif ($diasRestantes === 0)· Corresponde para hoy
                    @else· Faltan {{ $diasRestantes }} {{ $diasRestantes === 1 ? 'día' : 'días' }}@endif
                </span>
            </div>
        </div>
        @can('atender alertas mantenimiento')
            <form method="POST" action="{{ route('admin.alertas.atender', $alerta) }}" class="shrink-0 self-end sm:self-center">
                @csrf
                <button type="submit" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800">Marcar como atendida</button>
            </form>
        @endcan
    </div>
@endif
