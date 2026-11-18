@props(['tipo', 'vehiculo' => null, 'alerta' => null, 'diasRestantes' => null])

@if ($tipo === 'kilometraje')
    @php($vencido = $vehiculo->condicion_mantenimiento === 'Vencido')
    @php($mantenimiento = $vehiculo->ultimoMantenimiento)
    <article role="status" class="maintenance-alert {{ $vencido ? 'maintenance-alert--red' : 'maintenance-alert--amber' }}">
        <div class="min-w-0 flex-1">
            <p class="mb-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-400">Alerta por kilometraje</p>
            <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 leading-tight">
                <span class="font-semibold text-amber-100">{{ $vehiculo->numero_economico }} · {{ $vehiculo->marca }} {{ $vehiculo->modelo }}</span>
                <span>· Placas {{ $vehiculo->placas }}</span>
                <span>· {{ $mantenimiento->categoria_mantenimiento }}</span>
                <span>· Km actual: {{ number_format((float) $vehiculo->{'kilometraje actual'}) }}</span>
                <span>· Próximo: {{ number_format($mantenimiento->proximo_mantenimiento_km) }}</span>
                @if ($vehiculo->kilometros_restantes > 0)
                    <span>· Faltan {{ number_format($vehiculo->kilometros_restantes) }} km</span>
                @elseif ($vehiculo->kilometros_restantes < 0)
                    <span class="text-red-300">· Superado por {{ number_format(abs($vehiculo->kilometros_restantes)) }} km</span>
                @else
                    <span class="text-red-300">· Se alcanzó el kilometraje programado</span>
                @endif
            </div>
        </div>
        <span class="shrink-0 self-end rounded-full px-2 py-1 text-[10px] font-semibold leading-tight sm:self-center {{ $vencido ? 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200' }}">
            {{ $vencido ? 'Mantenimiento vencido' : 'Próximo mantenimiento' }}
        </span>
    </article>
@else
    @php($vencida = $alerta->tipo === 'vencida')
    <div role="status" class="maintenance-alert text-sm {{ $vencida ? 'maintenance-alert--red' : 'maintenance-alert--amber' }}">
        <div class="min-w-0 flex-1">
            <span class="mb-0.5 block text-[10px] font-bold uppercase tracking-wide {{ $vencida ? 'text-red-300' : 'text-amber-400' }}">Alerta por fecha</span>
            <div class="flex flex-wrap items-center gap-x-1.5 gap-y-0.5 leading-tight">
                <strong class="text-amber-100">{{ $vencida ? 'Mantenimiento vencido:' : 'Próximo mantenimiento:' }}</strong>
                <span>{{ $alerta->mantenimiento->vehiculo->marca }} {{ $alerta->mantenimiento->vehiculo->modelo }} ({{ $alerta->mantenimiento->vehiculo->numero_economico }}) · {{ $alerta->mantenimiento->categoria_mantenimiento }} · {{ $alerta->fecha_mantenimiento->format('d/m/Y') }}</span>
                <span class="font-semibold {{ $vencida ? 'text-red-300' : 'text-amber-100' }}">
                    @if ($diasRestantes < 0)
                        · Vencido hace {{ abs($diasRestantes) }} {{ abs($diasRestantes) === 1 ? 'día' : 'días' }}
                    @elseif ($diasRestantes === 0)
                        · Corresponde para hoy
                    @else
                        · Faltan {{ $diasRestantes }} {{ $diasRestantes === 1 ? 'día' : 'días' }}
                    @endif
                </span>
            </div>
        </div>
        @can('atender alertas mantenimiento')
            <form method="POST" action="{{ route('admin.alertas.atender', $alerta) }}" class="shrink-0 self-end sm:self-center">
                @csrf
                <button type="submit" class="inline-flex items-center rounded-lg border border-amber-600 bg-zinc-800 px-2 py-1.5 text-xs font-semibold text-amber-200 shadow-sm transition hover:bg-zinc-700">Marcar como atendida</button>
            </form>
        @endcan
    </div>
@endif
