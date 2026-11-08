<x-layouts.app :title="__('Inicio')">
    <div class="min-h-screen bg-slate-50 px-4 py-6 dark:bg-zinc-950 sm:px-6 sm:py-8 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-7">
            <header class="relative isolate overflow-hidden rounded-3xl bg-gradient-to-br from-blue-700 via-blue-800 to-indigo-950 px-6 py-7 text-white shadow-lg shadow-blue-950/10 sm:px-8 sm:py-9">
                <div class="absolute -right-16 -top-24 -z-10 size-72 rounded-full border-[36px] border-white/5"></div>
                <div class="absolute -bottom-24 right-44 -z-10 size-52 rounded-full bg-cyan-400/10 blur-2xl"></div>
                <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-2xl">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-wide text-blue-100">
                            <span class="size-1.5 rounded-full bg-emerald-300"></span>
                            SIGEV-TecNM · Panel administrativo
                        </span>
                        <h1 class="mt-4 text-3xl font-bold tracking-tight sm:text-4xl">Bienvenido, {{ $rolUsuario }}</h1>
                        <p class="mt-2 max-w-xl text-sm leading-6 text-blue-100/90 sm:text-base">Aquí tienes un resumen del estado de tu flotilla vehicular.</p>
                    </div>
                    <div class="flex w-fit items-center gap-3 rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur-sm">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-white/10 text-blue-100">
                            <flux:icon name="calendar-days" class="size-5" />
                        </span>
                        <div>
                            <p class="text-xs font-medium capitalize text-blue-100">{{ $dashboardDate }}</p>
                            <p id="dashboard-clock" class="mt-0.5 text-lg font-semibold tabular-nums tracking-wide text-white" aria-live="off">--:--:--</p>
                        </div>
                    </div>
                </div>
            </header>

            <section aria-label="Indicadores de vehículos" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($indicadoresVehiculos as $indicador)
                    <article class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-6">
                        <span @class([
                            'flex size-12 shrink-0 items-center justify-center rounded-2xl ring-1',
                            'bg-blue-50 text-blue-700 ring-blue-100 dark:bg-blue-950 dark:text-blue-300 dark:ring-blue-900' => $indicador['color'] === 'blue',
                            'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-950 dark:text-emerald-300 dark:ring-emerald-900' => $indicador['color'] === 'emerald',
                            'bg-indigo-50 text-indigo-700 ring-indigo-100 dark:bg-indigo-950 dark:text-indigo-300 dark:ring-indigo-900' => $indicador['color'] === 'indigo',
                            'bg-amber-50 text-amber-700 ring-amber-100 dark:bg-amber-950 dark:text-amber-300 dark:ring-amber-900' => $indicador['color'] === 'amber',
                            'bg-orange-50 text-orange-700 ring-orange-100 dark:bg-orange-950 dark:text-orange-300 dark:ring-orange-900' => $indicador['color'] === 'orange',
                            'bg-slate-100 text-slate-700 ring-slate-200 dark:bg-zinc-800 dark:text-zinc-300 dark:ring-zinc-700' => $indicador['color'] === 'slate',
                        ])>
                            <flux:icon :name="$indicador['icono']" class="size-5" />
                        </span>
                        <div class="min-w-0">
                            <p class="text-3xl font-bold leading-none tracking-tight text-slate-900 dark:text-white">{{ $indicador['cantidad'] }}</p>
                            <h2 class="mt-2 truncate text-sm font-semibold text-slate-600 dark:text-zinc-300">{{ $indicador['titulo'] }}</h2>
                        </div>
                    </article>
                @endforeach
            </section>

            <x-alertas-documentos />

            <section aria-label="Flotilla de vehículos" class="space-y-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Flotilla vehicular</h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-zinc-400">{{ $vehiculos->count() }} {{ $vehiculos->count() === 1 ? 'vehículo registrado' : 'vehículos registrados' }} en la flotilla.</p>
                    </div>
                    @can('ver formulario crear vehiculo')
                        <a href="{{ route('admin.vehiculos.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                            <flux:icon name="plus" class="size-4" /> Registrar vehículo
                        </a>
                    @endcan
                </div>

                @forelse ($vehiculos as $vehiculo)
                    @if ($loop->first)
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @endif
                    <div>
                            <article class="group w-full overflow-hidden rounded-2xl border border-slate-200 bg-white text-left shadow-sm transition duration-200 hover:-translate-y-1 hover:border-blue-300 hover:shadow-xl hover:shadow-blue-950/10 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-blue-700">
                                <div class="relative aspect-[16/10] overflow-hidden bg-slate-100 dark:bg-zinc-800">
                                    @if ($vehiculo->fotografia_url)
                                        <img src="{{ $vehiculo->fotografia_url }}" alt="{{ $vehiculo->marca }} {{ $vehiculo->modelo }}" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                        <div style="display: none" class="h-full w-full flex-col items-center justify-center gap-3 bg-gradient-to-br from-slate-100 via-blue-50 to-slate-200 text-slate-400 dark:from-zinc-800 dark:via-slate-900 dark:to-zinc-950">
                                            <flux:icon name="truck" class="size-16" />
                                            <span class="text-xs font-medium">Fotografía no disponible</span>
                                        </div>
                                    @else
                                        <div class="flex h-full w-full flex-col items-center justify-center gap-3 bg-gradient-to-br from-slate-100 via-blue-50 to-slate-200 text-slate-400 dark:from-zinc-800 dark:via-slate-900 dark:to-zinc-950">
                                            <flux:icon name="truck" class="size-16" />
                                            <span class="text-xs font-medium">Sin fotografía</span>
                                        </div>
                                    @endif
                                    <span @class([
                                        'absolute right-3 top-3 rounded-full px-3 py-1 text-xs font-semibold shadow-sm',
                                        'bg-emerald-100 text-emerald-800' => $vehiculo->estado === 'Disponible',
                                        'bg-blue-100 text-blue-800' => $vehiculo->estado === 'Asignado',
                                        'bg-amber-100 text-amber-800' => $vehiculo->estado === 'En mantenimiento',
                                        'bg-orange-100 text-orange-800' => $vehiculo->estado === 'Fuera de servicio',
                                        'bg-slate-200 text-slate-700' => $vehiculo->estado === 'Baja' || ! in_array($vehiculo->estado, ['Disponible', 'Asignado', 'En mantenimiento', 'Fuera de servicio', 'Baja'], true),
                                    ])>{{ $vehiculo->estado }}</span>
                                </div>
                                <div class="space-y-3 p-5">
                                    <div>
                                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $vehiculo->marca }} {{ $vehiculo->modelo }}</h3>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3 border-t border-slate-100 pt-3 dark:border-zinc-800">
                                        <div><p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Número económico</p><p class="mt-1 truncate text-sm font-semibold text-slate-800 dark:text-zinc-100">{{ $vehiculo->numero_economico }}</p></div>
                                        <div><p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Placas</p><p class="mt-1 truncate text-sm font-semibold text-slate-800 dark:text-zinc-100">{{ $vehiculo->placas }}</p></div>
                                    </div>
                                </div>
                            </article>
                    </div>
                    @if ($loop->last)
                        </div>
                    @endif
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center dark:border-zinc-700 dark:bg-zinc-900">
                        <span class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-300"><flux:icon name="truck" class="size-8" /></span>
                        <h3 class="mt-5 text-lg font-semibold text-slate-900 dark:text-white">Todavía no hay vehículos registrados</h3>
                        <p class="mt-2 text-sm text-slate-500 dark:text-zinc-400">Cuando registres un vehículo, aparecerá aquí como una tarjeta.</p>
                        @can('ver formulario crear vehiculo')
                            <a href="{{ route('admin.vehiculos.create') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"><flux:icon name="plus" class="size-4" /> Registrar primer vehículo</a>
                        @endcan
                    </div>
                @endforelse
            </section>
        </div>
    </div>

    <script>
        (() => {
            const clock = document.getElementById('dashboard-clock');
            const updateClock = () => {
                if (clock) clock.textContent = new Intl.DateTimeFormat(undefined, {
                    hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false,
                }).format(new Date());
            };
            updateClock();
            window.setInterval(updateClock, 1000);
        })();
    </script>
</x-layouts.app>
