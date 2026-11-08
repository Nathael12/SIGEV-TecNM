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
        $hayDocumentos = \App\Models\Documento::exists();
        $maximo = 5;
        $puedeVerDetalle = auth()->user()->can('ver detalle documento');

        $estilos = [
            'Vencido' => 'background-color:#fee2e2;color:#b91c1c',
            'Por vencer' => 'background-color:#fef3c7;color:#b45309',
        ];

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

    @if ($hayDocumentos)
        <section aria-label="Alertas de documentos" class="space-y-4">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Documentos de vehículos</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-zinc-400">
                    Alertas de vigencia (se avisa {{ \App\Models\Documento::DIAS_ALERTA }} días antes del vencimiento).
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <article
                    class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-6">
                    <span
                        style="display:flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:16px;flex-shrink:0;background-color:#fee2e2;color:#b91c1c;">
                        <flux:icon name="x-circle" class="size-5" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-3xl font-bold leading-none tracking-tight text-slate-900 dark:text-white">
                            {{ $vencidos }}</p>
                        <h3 class="mt-2 truncate text-sm font-semibold text-slate-600 dark:text-zinc-300">Documentos
                            vencidos</h3>
                    </div>
                </article>

                <article
                    class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-6">
                    <span
                        style="display:flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:16px;flex-shrink:0;background-color:#fef3c7;color:#b45309;">
                        <flux:icon name="exclamation-triangle" class="size-5" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-3xl font-bold leading-none tracking-tight text-slate-900 dark:text-white">
                            {{ $porVencer }}</p>
                        <h3 class="mt-2 truncate text-sm font-semibold text-slate-600 dark:text-zinc-300">Documentos
                            por vencer</h3>
                    </div>
                </article>
            </div>

            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-6">
                @forelse ($alertas->take($maximo) as $documento)
                    <div
                        style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;padding:12px 0;{{ $loop->first ? '' : 'border-top:1px solid rgba(148,163,184,.25);' }}">
                        <div style="min-width:0;">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                {{ $documento->vehiculo->numero_economico }} · {{ $documento->vehiculo->placas }}
                            </p>
                            <p class="text-sm text-slate-600 dark:text-zinc-400">
                                {{ $documento->descripcion_documento }}
                            </p>
                        </div>
                        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                            <span class="text-sm text-slate-600 dark:text-zinc-400">
                                {{ $documento->vigencia_fin->format('d/m/Y') }} · {{ $textoDias($documento) }}
                            </span>
                            <span style="{{ $estilos[$documento->estado_vigencia] }}"
                                class="inline-block px-3 py-1 text-xs font-semibold rounded-full">
                                {{ $documento->estado_vigencia }}
                            </span>
                            <a href="{{ $puedeVerDetalle ? url('/admin/documento/' . $documento->id_documento) : url('/admin/documentos?vehiculo=' . $documento->vehiculo_id) }}"
                                class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded transition">
                                <i class="fas fa-eye mr-2"></i> Revisar
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-600 dark:text-zinc-400" style="display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-check-circle" style="color:#15803d"></i>
                        Todos los documentos están vigentes.
                    </p>
                @endforelse

                @if ($alertas->count() > $maximo)
                    <p class="text-sm text-slate-600 dark:text-zinc-400"
                        style="margin-top:12px;border-top:1px solid rgba(148,163,184,.25);padding-top:12px;">
                        y {{ $alertas->count() - $maximo }} más. Revísalos desde la Documentación de cada vehículo.
                    </p>
                @endif
            </div>
        </section>
    @endif
@endcan