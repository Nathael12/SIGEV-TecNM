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
                        <h1 class="mt-4 text-3xl font-bold tracking-tight sm:text-4xl">Bienvenido, Administrador</h1>
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

            @php
                $stats = [
                    ['title' => 'Total de vehículos', 'icon' => 'truck', 'tone' => 'blue', 'hint' => 'Inventario de la flotilla'],
                    ['title' => 'Vehículos en ruta', 'icon' => 'map', 'tone' => 'green', 'hint' => 'Unidades actualmente activas'],
                    ['title' => 'En mantenimiento', 'icon' => 'wrench-screwdriver', 'tone' => 'amber', 'hint' => 'Unidades en servicio técnico'],
                    ['title' => 'Fuera de servicio', 'icon' => 'exclamation-triangle', 'tone' => 'rose', 'hint' => 'Unidades no disponibles'],
                ];
                $tones = [
                    'blue' => ['icon' => 'bg-blue-50 text-blue-700 ring-blue-100 dark:bg-blue-950 dark:text-blue-300 dark:ring-blue-900', 'bar' => 'bg-blue-500'],
                    'green' => ['icon' => 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-950 dark:text-emerald-300 dark:ring-emerald-900', 'bar' => 'bg-emerald-500'],
                    'amber' => ['icon' => 'bg-amber-50 text-amber-700 ring-amber-100 dark:bg-amber-950 dark:text-amber-300 dark:ring-amber-900', 'bar' => 'bg-amber-500'],
                    'rose' => ['icon' => 'bg-rose-50 text-rose-700 ring-rose-100 dark:bg-rose-950 dark:text-rose-300 dark:ring-rose-900', 'bar' => 'bg-rose-500'],
                ];
            @endphp

            <section aria-label="Estadísticas de la flotilla" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($stats as $stat)
                    <article class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg hover:shadow-slate-900/5 dark:border-zinc-800 dark:bg-zinc-900 sm:p-6">
                        <span class="absolute inset-x-0 top-0 h-1 {{ $tones[$stat['tone']]['bar'] }} opacity-80"></span>
                        <div class="flex items-start justify-between gap-3">
                            <span class="flex size-12 items-center justify-center rounded-2xl ring-1 {{ $tones[$stat['tone']]['icon'] }} transition-transform duration-200 group-hover:scale-105">
                                <flux:icon :name="$stat['icon']" class="size-5" />
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-2.5 py-1 text-[11px] font-medium text-slate-500 dark:bg-zinc-800 dark:text-zinc-400">
                                <span class="size-1.5 rounded-full {{ $tones[$stat['tone']]['bar'] }}"></span>
                                Sin datos
                            </span>
                        </div>
                        <p class="mt-6 text-4xl font-bold leading-none tracking-tight text-slate-900 dark:text-white">—</p>
                        <h2 class="mt-3 text-sm font-semibold text-slate-800 dark:text-zinc-100">{{ $stat['title'] }}</h2>
                        <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">{{ $stat['hint'] }}</p>
                    </article>
                @endforeach
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
