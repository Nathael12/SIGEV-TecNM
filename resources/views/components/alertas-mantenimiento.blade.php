@props(['alertas'])

<x-sweetalert-flash />

<section aria-label="Alertas de mantenimiento" class="space-y-2">
    @forelse ($alertas as $grupo)
        @php($mantenimiento = $grupo->first()->mantenimiento)
        @php($vehiculo = $mantenimiento?->vehiculo)
        @if (! $mantenimiento || ! $vehiculo)
            @continue
        @endif
        @php($alertaKm = $grupo->firstWhere('condicion', 'kilometraje'))
        @php($mostrarFecha = auth()->user()?->can('ver alertas mantenimiento') ?? false)
        @php($alertaFecha = $mostrarFecha ? $grupo->firstWhere('condicion', 'fecha') : null)
        @if (! $alertaKm && ! $alertaFecha)
            @continue
        @endif
        @php($km = $alertaKm ? \App\Support\AlertasMantenimiento::evaluarKilometraje((float) $vehiculo->{'kilometraje actual'}, $mantenimiento->getRawOriginal('proximo_mantenimiento_km')) : null)
        @php($fechaProxima = $mostrarFecha ? $mantenimiento->proximo_mantenimiento_fecha : null)
        @php($dias = $fechaProxima ? (int) now()->startOfDay()->diffInDays($fechaProxima, false) : null)
        @php($vencido = ($km && $km['condicion'] === 'Vencido') || ($dias !== null && $dias < 0))
        <article role="status" class="maintenance-alert {{ $vencido ? 'maintenance-alert--red' : 'maintenance-alert--amber' }} flex-wrap">
            <div class="min-w-0 flex-1 space-y-1">
                <div class="flex flex-wrap items-center gap-x-1.5 gap-y-0.5 leading-tight">
                    @can('ver detalle vehiculo')
                        <a href="{{ route('admin.vehiculos.show', $vehiculo->id_vehiculo) }}" class="font-semibold text-blue-800 underline-offset-2 hover:underline dark:text-blue-200">{{ $vehiculo->numero_economico }} · {{ $vehiculo->marca }} {{ $vehiculo->modelo }}</a>
                    @else
                        <strong>{{ $vehiculo->numero_economico }} · {{ $vehiculo->marca }} {{ $vehiculo->modelo }}</strong>
                    @endcan
                    <span class="text-xs">{{ $vehiculo->placas }} · {{ $mantenimiento->categoria_mantenimiento }}</span>
                </div>
                <div class="flex flex-col gap-0.5 text-xs sm:flex-row sm:flex-wrap sm:gap-x-2">
                    @if ($alertaKm && $km)
                        <span class="font-semibold text-sky-900 dark:text-sky-200">Kilometraje: {{ number_format((float) $vehiculo->{'kilometraje actual'}) }} / {{ number_format((float) $mantenimiento->getRawOriginal('proximo_mantenimiento_km')) }} km · {{ $km['kilometros_restantes'] > 0 ? 'Faltan '.number_format($km['kilometros_restantes']).' km' : 'Vencido por '.number_format(abs($km['kilometros_restantes'])).' km' }}</span>
                    @endif
                    @if ($alertaFecha)
                        <span class="font-medium text-violet-900 dark:text-violet-200">Fecha: {{ $fechaProxima->format('d/m/Y') }} · @if ($dias < 0)Vencida hace {{ abs($dias) }} {{ abs($dias) === 1 ? 'día' : 'días' }}@elseif ($dias === 0)Corresponde hoy @else Faltan {{ $dias }} {{ $dias === 1 ? 'día' : 'días' }}@endif</span>
                    @endif
                    @if (! $alertaFecha && $fechaProxima)
                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-700 dark:bg-zinc-800 dark:text-zinc-300">Próximo servicio · {{ $fechaProxima->format('d/m/Y') }} · @if ($dias < 0){{ abs($dias) }} {{ abs($dias) === 1 ? 'día vencido' : 'días vencidos' }}@elseif ($dias === 0)hoy @else faltan {{ $dias }} {{ $dias === 1 ? 'día' : 'días' }}@endif</span>
                    @endif
                </div>
            </div>
            @can('atender alertas mantenimiento')
                <form method="POST" action="{{ route('admin.alertas.atender', $grupo->first()->id) }}" class="shrink-0 self-end sm:self-center" data-swal-confirm="1" data-swal-title="Finalizar mantenimiento" data-swal-text="¿Marcar el mantenimiento como finalizado? El vehículo quedará Disponible, salvo que tenga otro servicio abierto o una condición que impida su disponibilidad." data-swal-confirm-text="Atender alerta" data-swal-color="#16a34a" data-swal-icon="question">
                    @csrf
                    <button type="submit" @disabled(! $fechaProxima) class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800">Marcar como atendida</button>
                    @if (! $fechaProxima)
                        <p class="mt-1 max-w-56 text-right text-xs text-amber-700 dark:text-amber-300">Registra la fecha del próximo mantenimiento para poder atender esta alerta</p>
                    @endif
                </form>
                <form method="POST" action="{{ route('admin.alertas.destroy', $grupo->first()->id) }}" class="shrink-0 self-end sm:self-center" data-swal-confirm="1" data-swal-title="Eliminar alerta" data-swal-text="¿Eliminar únicamente esta alerta? El estado del mantenimiento no cambiará." data-swal-confirm-text="Eliminar alerta" data-swal-color="#dc2626" data-swal-icon="warning">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center rounded-lg border border-red-300 bg-white px-3 py-2 text-xs font-semibold text-red-700 shadow-sm transition hover:bg-red-50 dark:border-red-900 dark:bg-zinc-900 dark:text-red-300 dark:hover:bg-red-950">Eliminar alerta</button>
                </form>
            @endcan
        </article>
    @empty
    @endforelse
    <div class="rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
        No hay alertas pendientes por kilometraje ni fecha.
    </div>
</section>
