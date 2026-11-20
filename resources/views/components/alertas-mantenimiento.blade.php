@props(['alertas'])

<section aria-label="Alertas de mantenimiento" class="space-y-3">
    @forelse ($alertas as $grupo)
        @php($mantenimiento = $grupo->first()->mantenimiento)
        @php($vehiculo = $mantenimiento->vehiculo)
        @php($alertaKm = $grupo->firstWhere('condicion', 'kilometraje'))
        @php($alertaFecha = $grupo->firstWhere('condicion', 'fecha'))
        @php($km = $alertaKm ? \App\Support\AlertasMantenimiento::evaluarKilometraje((float) $vehiculo->{'kilometraje actual'}, $mantenimiento->getRawOriginal('proximo_mantenimiento_km')) : null)
        @php($fechaProxima = $mantenimiento->proximo_mantenimiento_fecha)
        @php($dias = $fechaProxima ? (int) now()->startOfDay()->diffInDays($fechaProxima, false) : null)
        @php($vencido = ($km && $km['condicion'] === 'Vencido') || ($dias !== null && $dias < 0))
        <article role="status" class="maintenance-alert {{ $vencido ? 'maintenance-alert--red' : 'maintenance-alert--amber' }} flex-wrap">
            <div class="min-w-0 flex-1 space-y-2">
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                    @can('ver detalle vehiculo')
                        <a href="{{ route('admin.vehiculos.show', $vehiculo->id_vehiculo) }}" class="font-semibold text-blue-800 underline-offset-2 hover:underline dark:text-blue-200">{{ $vehiculo->numero_economico }} · {{ $vehiculo->marca }} {{ $vehiculo->modelo }}</a>
                    @else
                        <strong>{{ $vehiculo->numero_economico }} · {{ $vehiculo->marca }} {{ $vehiculo->modelo }}</strong>
                    @endcan
                    <span class="text-xs">· Placas {{ $vehiculo->placas }} · {{ $mantenimiento->categoria_mantenimiento }}</span>
                </div>
                <div class="flex flex-wrap gap-2 text-xs">
                    @if ($alertaKm && $km)
                        <span class="inline-flex rounded-full bg-sky-100 px-2.5 py-1 font-semibold text-sky-900 dark:bg-sky-950 dark:text-sky-200">Kilometraje · Actual {{ number_format((float) $vehiculo->{'kilometraje actual'}) }} km · Próximo {{ number_format((float) $mantenimiento->getRawOriginal('proximo_mantenimiento_km')) }} km · {{ $km['kilometros_restantes'] > 0 ? 'Faltan '.number_format($km['kilometros_restantes']).' km' : 'Vencido por '.number_format(abs($km['kilometros_restantes'])).' km' }}</span>
                    @endif
                    @if ($alertaFecha)
                        <span class="inline-flex rounded-full bg-violet-100 px-2.5 py-1 font-semibold text-violet-900 dark:bg-violet-950 dark:text-violet-200">Fecha · {{ $fechaProxima->format('d/m/Y') }} · @if ($dias < 0)Vencida hace {{ abs($dias) }} {{ abs($dias) === 1 ? 'día' : 'días' }}@elseif ($dias === 0)Corresponde hoy @else Faltan {{ $dias }} {{ $dias === 1 ? 'día' : 'días' }}@endif</span>
                    @endif
                    @if (! $alertaFecha && $fechaProxima)
                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-700 dark:bg-zinc-800 dark:text-zinc-300">Próximo servicio · {{ $fechaProxima->format('d/m/Y') }} · @if ($dias < 0){{ abs($dias) }} {{ abs($dias) === 1 ? 'día vencido' : 'días vencidos' }}@elseif ($dias === 0)hoy @else faltan {{ $dias }} {{ $dias === 1 ? 'día' : 'días' }}@endif</span>
                    @endif
                </div>
            </div>
            @can('atender alertas mantenimiento')
                <form method="POST" action="{{ route('admin.alertas.atender', $grupo->first()->id) }}" class="shrink-0 self-end sm:self-center" onsubmit="return confirm('¿Marcar como atendida esta alerta? Esto no registrará un mantenimiento nuevo.')">
                    @csrf
                    <button type="submit" @disabled(! $fechaProxima) class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800">Marcar como atendida</button>
                    @if (! $fechaProxima)
                        <p class="mt-1 max-w-56 text-right text-xs text-amber-700 dark:text-amber-300">Registra la fecha del próximo mantenimiento para poder atender esta alerta</p>
                    @endif
                </form>
            @endcan
        </article>
    @empty
    @endforelse
    <div class="rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
        No hay alertas pendientes por kilometraje ni fecha.
    </div>
</section>
