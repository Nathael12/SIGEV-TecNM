@php
    $ajusteLogo = \App\Models\Ajuste::query()->first();
    $nombreEmpresa = filled($ajusteLogo?->nombre)
        ? trim($ajusteLogo->nombre)
        : config('app.name', 'SIGEV-TecNM');
    $logoEmpresaUrl = $ajusteLogo?->logo_url ?: asset('images/logo/logo_hopelchen.png');
@endphp

<div class="flex min-w-0 items-center gap-2">
    <div class="flex size-9 shrink-0 items-center justify-center overflow-hidden rounded-md bg-accent-content text-accent-foreground">
        @if ($logoEmpresaUrl)
            <img src="{{ $logoEmpresaUrl }}" alt="Logo de {{ $nombreEmpresa }}" class="h-full w-full bg-white object-contain p-0.5">
        @else
            <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
        @endif
    </div>
    <div class="grid min-w-0 flex-1 text-left text-sm">
        <span class="truncate leading-none font-semibold">{{ $nombreEmpresa }}</span>
    </div>
</div>
