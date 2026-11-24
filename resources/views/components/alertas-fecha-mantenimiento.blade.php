@props(['alertas'])

<x-sweetalert-flash />

@if ($alertas->isNotEmpty())
    <section aria-label="Alertas por fecha de mantenimiento" class="space-y-2">
        @foreach ($alertas->take(3) as $alerta)
            @php($diasRestantes = (int) now()->startOfDay()->diffInDays($alerta->fecha_mantenimiento, false))
            <x-alerta-mantenimiento tipo="fecha" :alerta="$alerta" :dias-restantes="$diasRestantes" />
        @endforeach
        @if ($alertas->count() > 3)
            <details class="space-y-2">
                <summary class="w-fit cursor-pointer py-1 text-xs font-semibold text-amber-400 hover:text-amber-300">Ver todas ({{ $alertas->count() }})</summary>
                <div class="space-y-2">
                    @foreach ($alertas->skip(3) as $alerta)
                        @php($diasRestantes = (int) now()->startOfDay()->diffInDays($alerta->fecha_mantenimiento, false))
                        <x-alerta-mantenimiento tipo="fecha" :alerta="$alerta" :dias-restantes="$diasRestantes" />
                    @endforeach
                </div>
            </details>
        @endif
    </section>
@endif
