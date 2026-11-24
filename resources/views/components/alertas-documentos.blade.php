{{--
    Alertas de vencimiento de documentos (RF-14).
    Componente independiente: se usa en el Inicio con <x-alertas-documentos />
    Guardar en: resources/views/components/alertas-documentos.blade.php
--}}
@can('ver documentos')
    @php
        $limite = now()->startOfDay()->addDays(\App\Models\Documento::DIAS_ALERTA);

        // Documentos vencidos o que vencen dentro de los próximos días de alerta
        $alertas = \App\Models\Documento::with('vehiculo')
            ->whereDate('vigencia_fin', '<=', $limite)
            ->orderBy('vigencia_fin')
            ->get();

        $vencidos = $alertas->filter(fn ($d) => $d->estado_vigencia === 'Vencido')->count();
        $porVencer = $alertas->count() - $vencidos;
        $maximo = 5;
        $puedeVerDetalle = auth()->user()->can('ver detalle documento');

        $textoDias = function ($documento) {
            $dias = (int) now()->startOfDay()->diffInDays($documento->vigencia_fin, false);

            if ($dias < 0) {
                return 'Venció hace ' . abs($dias) . ($dias === -1 ? ' día' : ' días');
            }

            if ($dias === 0) {
                return 'Vence hoy';
            }

            return 'Vence en ' . $dias . ($dias === 1 ? ' día' : ' días');
        };
    @endphp

    <x-tarjeta-alertas
        id="alertas-documentos"
        titulo="Documentos de vehículos"
        subtitulo="Alertas de vigencia (30 días antes del vencimiento)"
        icono="document-text"
        clase-icono="bg-sky-50 text-sky-600 dark:bg-sky-950 dark:text-sky-300"
        :contador="$alertas->count()"
        :etiqueta-contador="$alertas->count() === 1 ? 'activa' : 'activas'">

        <div class="grid grid-cols-2 gap-2">
            <article class="flex min-w-0 items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600 dark:bg-red-950 dark:text-red-300">
                    <flux:icon name="x-circle" class="size-5" />
                </span>
                <div class="min-w-0">
                    <p class="text-xl font-bold leading-none tracking-tight text-slate-900 dark:text-white">{{ $vencidos }}</p>
                    <h3 class="mt-1 text-xs font-semibold text-slate-600 dark:text-zinc-300">Vencidos</h3>
                </div>
            </article>
            <article class="flex min-w-0 items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-300">
                    <flux:icon name="exclamation-triangle" class="size-5" />
                </span>
                <div class="min-w-0">
                    <p class="text-xl font-bold leading-none tracking-tight text-slate-900 dark:text-white">{{ $porVencer }}</p>
                    <h3 class="mt-1 text-xs font-semibold text-slate-600 dark:text-zinc-300">Por vencer</h3>
                </div>
            </article>
        </div>

        @forelse ($alertas->take($maximo) as $documento)
            @php($vencido = $documento->estado_vigencia === 'Vencido')
            <article role="status" class="maintenance-alert {{ $vencido ? 'maintenance-alert--red' : 'maintenance-alert--amber' }} flex-wrap">
                <div class="min-w-0 flex-1 space-y-1">
                    <div class="flex flex-wrap items-center gap-x-1.5 gap-y-0.5 leading-tight">
                        <strong>{{ $documento->vehiculo->numero_economico }} · {{ $documento->vehiculo->placas }}</strong>
                    </div>
                    <div class="flex flex-col gap-0.5 text-xs sm:flex-row sm:flex-wrap sm:gap-x-2">
                        <span class="font-medium {{ $vencido ? 'text-red-900 dark:text-red-200' : 'text-amber-900 dark:text-amber-200' }}">
                            {{ $documento->vigencia_fin->format('d/m/Y') }} · {{ $textoDias($documento) }}
                        </span>
                        <span class="text-slate-600 dark:text-zinc-300">{{ $documento->descripcion_documento }}</span>
                    </div>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2 self-end sm:self-center">
                    <span @class([
                        'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                        'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-200' => $vencido,
                        'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200' => ! $vencido,
                    ])>{{ $documento->estado_vigencia }}</span>
                    <a href="{{ $puedeVerDetalle ? url('/admin/documento/' . $documento->id_documento) : url('/admin/documentos?vehiculo=' . $documento->vehiculo_id) }}"
                        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800">
                        <i class="fas fa-eye mr-2"></i> Revisar
                    </a>
                </div>
            </article>
        @empty
            <div class="flex flex-col items-center gap-2 rounded-xl bg-emerald-50/60 px-4 py-8 text-center dark:bg-emerald-950/20">
                <span class="flex size-11 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-300"><flux:icon name="check-circle" class="size-6" /></span>
                <p class="text-sm font-semibold text-slate-800 dark:text-zinc-100">Todo en orden</p>
                <p class="text-xs text-slate-500 dark:text-zinc-400">No hay documentos por vencer ni vencidos.</p>
            </div>
        @endforelse

        @if ($alertas->count() > $maximo)
            <p class="maintenance-alert border-slate-200 bg-slate-50 text-slate-600 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-300">
                y {{ $alertas->count() - $maximo }} más. Revísalos desde la Documentación de cada vehículo.
            </p>
        @endif
    </x-tarjeta-alertas>
@endcan
