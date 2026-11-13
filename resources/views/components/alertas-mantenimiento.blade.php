<section aria-label="Alertas de mantenimiento" class="space-y-4">
    @if ($alertas->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
            No hay alertas por ahora. Se mostrarán cuando un vehículo tenga un mantenimiento registrado y esté a {{ number_format(config('maintenance.kilometers_alert', 500)) }} km o menos del kilometraje de su próximo servicio.
        </div>
    @else
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Alertas de mantenimiento</h2>
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($alertas as $vehiculo)
                @php($mantenimiento = $vehiculo->ultimoMantenimiento)
                <article class="rounded-2xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950/30">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-semibold text-slate-900 dark:text-white">{{ $vehiculo->numero_economico }} · {{ $vehiculo->marca }} {{ $vehiculo->modelo }}</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-zinc-300">Placas {{ $vehiculo->placas }} · {{ $mantenimiento->categoria_mantenimiento }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $vehiculo->condicion_mantenimiento === 'Vencido' ? 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-200' : ($vehiculo->condicion_mantenimiento === 'Requiere mantenimiento' ? 'bg-orange-100 text-orange-800 dark:bg-orange-950 dark:text-orange-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200') }}">{{ $vehiculo->condicion_mantenimiento }}</span>
                    </div>
                    <p class="mt-3 text-sm text-slate-700 dark:text-zinc-200">Kilometraje actual: <strong>{{ number_format((float) $vehiculo->kilometraje) }} km</strong></p>
                    <p class="text-sm text-slate-700 dark:text-zinc-200">Servicio configurado: <strong>{{ number_format($mantenimiento->proximo_mantenimiento_km) }} km</strong></p>
                    @if ($vehiculo->kilometros_restantes > 0)
                        <p class="mt-1 text-sm font-medium text-amber-800 dark:text-amber-200">Faltan {{ number_format($vehiculo->kilometros_restantes) }} km.</p>
                    @elseif ($vehiculo->kilometros_restantes < 0)
                        <p class="mt-1 text-sm font-medium text-red-700 dark:text-red-300">Superado por {{ number_format(abs($vehiculo->kilometros_restantes)) }} km.</p>
                    @else
                        <p class="mt-1 text-sm font-medium text-orange-700 dark:text-orange-300">Se alcanzó el kilometraje programado.</p>
                    @endif
                </article>
            @endforeach
        </div>
    @endif
</section>
