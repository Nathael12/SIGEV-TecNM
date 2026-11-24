<x-layouts.app :title="__('Inicio')">
    @php
        // Colores por estado (se usan en la gráfica y en las insignias)
        $coloresEstado = [
            'Disponible' => '#10b981',
            'Asignado' => '#2563eb',
            'En mantenimiento' => '#f59e0b',
            'Fuera de servicio' => '#ef4444',
            'Baja' => '#94a3b8',
        ];
        $badgeEstado = [
            'Disponible' => 'bg-emerald-500 text-white',
            'Asignado' => 'bg-blue-600 text-white',
            'En mantenimiento' => 'bg-amber-500 text-white',
            'Fuera de servicio' => 'bg-red-500 text-white',
            'Baja' => 'bg-slate-500 text-white',
        ];

        // Gradiente cónico para la gráfica de dona (sin librerías externas)
        $acumulado = 0;
        $paradas = [];
        foreach ($distribucionVehiculos as $fila) {
            $parte = $totalVehiculos > 0 ? ($fila['total'] / $totalVehiculos) * 100 : 0;
            if ($parte > 0) {
                $paradas[] = $coloresEstado[$fila['estado']].' '.round($acumulado, 2).'% '.round($acumulado + $parte, 2).'%';
            }
            $acumulado += $parte;
        }
        $gradiente = count($paradas) ? 'conic-gradient('.implode(', ', $paradas).')' : 'conic-gradient(#e2e8f0 0% 100%)';
    @endphp

    <div class="min-h-full bg-slate-50 px-4 py-5 dark:bg-zinc-950 sm:px-6 lg:px-8"
         x-data="{ vehiculo: null, filtro: 'todos', q: '' }"
         @keydown.escape.window="vehiculo = null">
        <div class="mx-auto max-w-7xl space-y-5">

            {{-- ENCABEZADO --}}
            <header class="flex flex-col gap-3 rounded-2xl bg-gradient-to-r from-blue-900 to-blue-700 px-5 py-4 text-white shadow-sm sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[.16em] text-blue-200">SIGEV-TecNM · Panel administrativo</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight">Bienvenido, {{ $rolUsuario }}</h1>
                    <p class="mt-1 text-sm text-blue-100">Resumen del estado de la flotilla vehicular.</p>
                </div>
                <div class="flex items-center gap-3 rounded-xl border border-white/15 bg-white/10 px-3 py-2">
                    <flux:icon name="calendar-days" class="size-5 text-blue-100" />
                    <div>
                        <p class="text-xs capitalize text-blue-100">{{ $dashboardDate }}</p>
                        <p id="dashboard-clock" class="text-base font-semibold tabular-nums">--:--:--</p>
                    </div>
                </div>
            </header>

            @if (session('mensaje'))
                <div role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('mensaje') }}</div>
            @endif

            {{-- CONTENIDO PRINCIPAL: FLOTILLA (izquierda) + PANEL LATERAL (derecha) --}}
            <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">

                {{-- ========== FLOTILLA (PROTAGONISTA) ========== --}}
                <section aria-label="Flotilla de vehículos" class="space-y-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Flotilla vehicular</h2>
                            <p class="text-sm text-slate-600 dark:text-zinc-400">Selecciona una tarjeta para consultar sus detalles.</p>
                        </div>
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                            <div class="relative">
                                <flux:icon name="magnifying-glass" class="pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                                <input type="search" x-model="q" placeholder="Buscar marca, placas o N.º"
                                       class="w-full rounded-lg border border-slate-300 bg-white py-2 pl-8 pr-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white sm:w-56">
                            </div>
                            @can('ver formulario crear vehiculo')
                                <a href="{{ route('admin.vehiculos.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-700 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-blue-800">
                                    <flux:icon name="plus" class="size-4" /> Registrar vehículo
                                </a>
                            @endcan
                        </div>
                    </div>

                    {{-- Filtros por estado --}}
                    <div class="flex flex-wrap gap-2" role="tablist" aria-label="Filtrar por estado">
                        <button type="button" @click="filtro = 'todos'"
                                :class="filtro === 'todos' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-white text-slate-700 hover:bg-slate-100 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800'"
                                class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold transition dark:border-zinc-800">
                            Todos · {{ $totalVehiculos }}
                        </button>
                        @foreach ($distribucionVehiculos as $fila)
                            @if ($fila['total'] > 0)
                                <button type="button" @click="filtro = @js($fila['estado'])"
                                        :class="filtro === @js($fila['estado']) ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-white text-slate-700 hover:bg-slate-100 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800'"
                                        class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold transition dark:border-zinc-800">
                                    <span class="size-2 rounded-full" style="background: {{ $coloresEstado[$fila['estado']] }}"></span>
                                    {{ $fila['estado'] }} · {{ $fila['total'] }}
                                </button>
                            @endif
                        @endforeach
                    </div>

                    @forelse ($vehiculos as $vehiculo)
                        @if ($loop->first)<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 2xl:grid-cols-3">@endif

                        <button type="button"
                                x-show="(filtro === 'todos' || filtro === @js($vehiculo->estado)) && @js(mb_strtolower($vehiculo->marca.' '.$vehiculo->modelo.' '.$vehiculo->placas.' '.$vehiculo->numero_economico)).includes(q.toLowerCase().trim())"
                                @click="vehiculo = @js([
                                    'numero' => $vehiculo->numero_economico,
                                    'marca' => $vehiculo->marca,
                                    'modelo' => $vehiculo->modelo,
                                    'anio' => $vehiculo->anio,
                                    'placas' => $vehiculo->placas,
                                    'estado' => $vehiculo->estado,
                                    'tipo' => $vehiculo->{'tipo vehiculo'},
                                    'kilometraje' => $vehiculo->{'kilometraje actual'},
                                    'color' => $vehiculo->color,
                                    'combustible' => $vehiculo->{'tipo combustible'},
                                    'fecha' => $vehiculo->fecha_adquisicion?->format('d/m/Y'),
                                    'fotografia' => $vehiculo->fotografia_url,
                                    'mantenimiento' => $vehiculo->ultimoMantenimiento?->categoria_mantenimiento,
                                    'descripcion' => $vehiculo->ultimoMantenimiento?->descripcion_mantenimiento,
                                ])"
                                class="group relative flex w-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white text-left shadow-sm transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-blue-700">

                            {{-- Foto grande con degradado y datos encima --}}
                            <span class="relative block aspect-[4/3] w-full overflow-hidden bg-slate-200 dark:bg-zinc-800">
                                @if ($vehiculo->fotografia_url)
                                    <img src="{{ $vehiculo->fotografia_url }}"
                                         alt="Fotografía de {{ $vehiculo->marca }} {{ $vehiculo->modelo }}"
                                         loading="lazy"
                                         class="size-full object-cover transition duration-500 group-hover:scale-105"
                                         onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden'); this.nextElementSibling.classList.add('flex')">
                                    <span class="hidden size-full items-center justify-center text-slate-400"><flux:icon name="truck" class="size-14" /></span>
                                @else
                                    <span class="flex size-full items-center justify-center text-slate-400"><flux:icon name="truck" class="size-14" /></span>
                                @endif

                                <span class="pointer-events-none absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-slate-950/80 via-slate-950/30 to-transparent"></span>

                                <span @class([
                                    'absolute left-3 top-3 rounded-full px-2.5 py-1 text-[11px] font-semibold shadow',
                                    $badgeEstado[$vehiculo->estado] ?? 'bg-slate-500 text-white',
                                ])>{{ $vehiculo->estado }}</span>

                                <span class="absolute inset-x-0 bottom-0 block p-4 text-white">
                                    <span class="block truncate text-lg font-bold leading-tight drop-shadow">{{ $vehiculo->marca }} {{ $vehiculo->modelo }}</span>
                                    <span class="mt-0.5 block text-xs text-white/80">N.º {{ $vehiculo->numero_economico }} · {{ $vehiculo->anio }}</span>
                                </span>
                            </span>

                            {{-- Pie de la tarjeta --}}
                            <span class="flex items-center justify-between gap-2 px-4 py-3">
                                <span class="rounded-md bg-slate-100 px-2 py-1 font-mono text-xs font-semibold tracking-wider text-slate-700 dark:bg-zinc-800 dark:text-zinc-200">{{ $vehiculo->placas }}</span>
                                <span class="text-xs font-medium text-blue-700 group-hover:underline dark:text-blue-300">Ver ficha →</span>
                            </span>
                        </button>

                        @if ($loop->last)</div>@endif
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                            <flux:icon name="truck" class="mx-auto size-8 text-slate-400" />
                            <h3 class="mt-2 font-semibold text-slate-900 dark:text-white">Todavía no hay vehículos registrados</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">Los vehículos registrados aparecerán aquí.</p>
                        </div>
                    @endforelse
                </section>

                {{-- ========== PANEL LATERAL: GRÁFICA + ALERTAS ========== --}}
                <aside class="space-y-4 xl:sticky xl:top-4">
                    <section aria-labelledby="distribucion-title" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 id="distribucion-title" class="font-bold text-slate-900 dark:text-white">Estadística de Vehículos</h2>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-zinc-400">{{ $totalVehiculos }} vehículos por estado</p>
                            </div>
                            <span class="rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-800 dark:bg-blue-950 dark:text-blue-200">Flotilla</span>
                        </div>

                        {{-- Dona --}}
                        <div class="relative mx-auto mt-4 size-44" role="img" aria-label="Gráfica de dona de vehículos por estado">
                            <div class="size-full rounded-full" style="background: {{ $gradiente }}"></div>
                            <div class="absolute inset-5 flex flex-col items-center justify-center rounded-full bg-white dark:bg-zinc-900">
                                <span class="text-3xl font-bold leading-none text-slate-900 dark:text-white">{{ $totalVehiculos }}</span>
                                <span class="mt-1 text-[11px] text-slate-500 dark:text-zinc-400">vehículos</span>
                            </div>
                        </div>

                        {{-- Leyenda con barras --}}
                        <div class="mt-5 space-y-2.5">
                            @foreach ($distribucionVehiculos as $fila)
                                @php($porcentaje = $totalVehiculos > 0 ? round(($fila['total'] / $totalVehiculos) * 100) : 0)
                                <div class="text-xs">
                                    <div class="mb-1 flex items-center justify-between">
                                        <span class="flex items-center gap-2 font-medium text-slate-700 dark:text-zinc-300">
                                            <span class="size-2.5 rounded-full" style="background: {{ $coloresEstado[$fila['estado']] }}"></span>
                                            {{ $fila['estado'] }}
                                        </span>
                                        <span class="font-semibold tabular-nums text-slate-800 dark:text-zinc-200">{{ $fila['total'] }} <span class="font-normal text-slate-400">({{ $porcentaje }}%)</span></span>
                                    </div>
                                    <div class="h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-zinc-800">
                                        <div class="h-full rounded-full transition-all" style="width: {{ $porcentaje }}%; background: {{ $coloresEstado[$fila['estado']] }}"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    {{-- ALERTAS: encabezado fijo, contenido con scroll interno --}}
                    @php($totalAlertas = $alertasMantenimiento->count())
                    <x-tarjeta-alertas
                        id="alertas-mantenimiento"
                        titulo="Alertas de mantenimiento"
                        subtitulo="Vencimientos y servicios próximos"
                        icono="bell-alert"
                        clase-icono="bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-300"
                        :contador="$totalAlertas"
                        :etiqueta-contador="$totalAlertas === 1 ? 'activa' : 'activas'">
                        <x-alertas-mantenimiento :alertas="$alertasMantenimiento" />

                        @if ($totalAlertas === 0)
                            <div class="flex flex-col items-center gap-2 rounded-xl bg-emerald-50/60 px-4 py-8 text-center dark:bg-emerald-950/20">
                                <span class="flex size-11 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-300"><flux:icon name="check-circle" class="size-6" /></span>
                                <p class="text-sm font-semibold text-slate-800 dark:text-zinc-100">Todo en orden</p>
                                <p class="text-xs text-slate-500 dark:text-zinc-400">No hay mantenimientos pendientes de atención.</p>
                            </div>
                        @endif
                    </x-tarjeta-alertas>

                    <x-alertas-documentos />
                </aside>
            </div>

        </div>

        {{-- MODAL DE DETALLE (compacto, sin scroll) --}}
        <div x-cloak x-show="vehiculo" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4" @click.self="vehiculo = null" role="presentation">
            <section role="dialog" aria-modal="true" aria-labelledby="vehicle-dialog-title" class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-2xl dark:bg-zinc-900" @click.stop>
                <template x-if="vehiculo">
                    <div>
                        <div class="relative overflow-hidden rounded-t-2xl bg-slate-100 dark:bg-zinc-800">
                            <template x-if="vehiculo.fotografia">
                                <img :src="vehiculo.fotografia" :alt="'Fotografía de ' + vehiculo.marca + ' ' + vehiculo.modelo" class="h-44 w-full object-cover sm:h-48">
                            </template>
                            <template x-if="!vehiculo.fotografia">
                                <div class="flex h-44 items-center justify-center text-slate-400 sm:h-48"><flux:icon name="truck" class="size-14" /></div>
                            </template>
                            <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-slate-950/85 to-transparent"></div>
                            <button type="button" @click="vehiculo = null" aria-label="Cerrar detalles" class="absolute right-2.5 top-2.5 rounded-full bg-black/45 p-1.5 text-white backdrop-blur hover:bg-black/70">
                                <flux:icon name="x-mark" class="size-4" />
                            </button>
                            <div class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-2 px-4 pb-3 text-white">
                                <div class="min-w-0">
                                    <h2 id="vehicle-dialog-title" class="truncate text-lg font-bold leading-tight"><span x-text="vehiculo.marca"></span> <span x-text="vehiculo.modelo"></span></h2>
                                    <p class="text-xs text-white/80">N.º <span x-text="vehiculo.numero"></span> · <span x-text="vehiculo.anio"></span></p>
                                </div>
                                <span class="shrink-0 rounded-full bg-white/20 px-2.5 py-0.5 text-[11px] font-semibold backdrop-blur" x-text="vehiculo.estado"></span>
                            </div>
                        </div>

                        <dl class="grid grid-cols-3 gap-x-3 gap-y-3 px-4 py-4 text-xs">
                            <div><dt class="text-[11px] text-slate-500">Placas</dt><dd class="truncate font-semibold text-slate-900 dark:text-white" x-text="vehiculo.placas"></dd></div>
                            <div><dt class="text-[11px] text-slate-500">Tipo</dt><dd class="truncate font-semibold text-slate-900 dark:text-white" x-text="vehiculo.tipo || 'No registrado'"></dd></div>
                            <div><dt class="text-[11px] text-slate-500">Kilometraje</dt><dd class="truncate font-semibold text-slate-900 dark:text-white"><span x-text="Number(vehiculo.kilometraje).toLocaleString('es-MX')"></span> km</dd></div>
                            <div><dt class="text-[11px] text-slate-500">Color</dt><dd class="truncate font-semibold text-slate-900 dark:text-white" x-text="vehiculo.color || 'No registrado'"></dd></div>
                            <div><dt class="text-[11px] text-slate-500">Combustible</dt><dd class="truncate font-semibold text-slate-900 dark:text-white" x-text="vehiculo.combustible || 'No registrado'"></dd></div>
                            <div><dt class="text-[11px] text-slate-500">Adquisición</dt><dd class="truncate font-semibold text-slate-900 dark:text-white" x-text="vehiculo.fecha || 'No registrada'"></dd></div>
                            <div class="col-span-3 rounded-lg bg-slate-50 px-3 py-2 dark:bg-zinc-800">
                                <dt class="text-[11px] text-slate-500">Último mantenimiento</dt>
                                <dd class="line-clamp-2 font-semibold text-slate-900 dark:text-white" x-text="vehiculo.mantenimiento ? vehiculo.mantenimiento + (vehiculo.descripcion ? ' · ' + vehiculo.descripcion : '') : 'Sin mantenimiento registrado'"></dd>
                            </div>
                        </dl>
                    </div>
                </template>
            </section>
        </div>
    </div>

    <script>
        (() => {
            const clock = document.getElementById('dashboard-clock');
            const updateClock = () => {
                if (clock) clock.textContent = new Intl.DateTimeFormat('es-MX', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }).format(new Date());
            };
            updateClock();
            window.setInterval(updateClock, 1000);
        })();
    </script>
</x-layouts.app>
