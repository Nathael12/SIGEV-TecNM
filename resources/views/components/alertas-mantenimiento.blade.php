<section aria-label="Alertas de mantenimiento" class="space-y-2">
    @if ($alertas->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
            No hay alertas por kilometraje. Se mostrarán cuando falten {{ number_format(config('maintenance.kilometers_alert', 500)) }} km o menos para el próximo mantenimiento.
        </div>
    @else
        <div class="space-y-2">
            @foreach ($alertas->take(3) as $vehiculo)
                <x-alerta-mantenimiento tipo="kilometraje" :vehiculo="$vehiculo" />
            @endforeach
            @if ($alertas->count() > 3)
                <details class="space-y-2">
                    <summary class="w-fit cursor-pointer py-1 text-xs font-semibold text-amber-400 hover:text-amber-300">Ver todas ({{ $alertas->count() }})</summary>
                    <div class="space-y-2">
                        @foreach ($alertas->skip(3) as $vehiculo)
                            <x-alerta-mantenimiento tipo="kilometraje" :vehiculo="$vehiculo" />
                        @endforeach
                    </div>
                </details>
            @endif
        </div>
    @endif
</section>
