@props([
    'id',
    'titulo',
    'subtitulo',
    'icono',
    'claseIcono',
    'contador' => 0,
    'etiquetaContador' => 'activas',
])

<section aria-labelledby="{{ $id }}-title" class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    <header class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 dark:border-zinc-800">
        <div class="flex min-w-0 items-center gap-2.5">
            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl {{ $claseIcono }}">
                <flux:icon :name="$icono" class="size-5" />
            </span>
            <div class="min-w-0">
                <h2 id="{{ $id }}-title" class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $titulo }}</h2>
                <p class="truncate text-[11px] text-slate-500 dark:text-zinc-400">{{ $subtitulo }}</p>
            </div>
        </div>
        <span @class([
            'shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold',
            'bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-200' => $contador > 0,
            'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-200' => $contador === 0,
        ])>{{ $contador }} {{ $etiquetaContador }}</span>
    </header>

    <div class="max-h-[22rem] space-y-2 overflow-y-auto p-3 [scrollbar-width:thin]">
        {{ $slot }}
    </div>
</section>
