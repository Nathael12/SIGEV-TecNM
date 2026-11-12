<x-layouts.app title="Ficha del vehículo">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Ficha del vehículo</flux:heading>
        <br><flux:separator variant="subtle" />
    </div>
    <div class="rounded-lg border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        @if (session('mensaje'))
            <div class="m-6 mb-0 rounded-lg bg-green-100 px-4 py-3 text-sm text-green-800">{{ session('mensaje') }}</div>
        @endif
        <div class="p-6 pb-0">
            @if ($fotografiaUrl)
                <img src="{{ $fotografiaUrl }}" alt="Fotografía de {{ $vehiculo->marca }} {{ $vehiculo->modelo }}" class="max-h-96 w-full rounded-2xl border border-slate-200 bg-slate-50 object-contain dark:border-zinc-700 dark:bg-zinc-900">
            @else
                <div class="flex min-h-48 w-full flex-col items-center justify-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 text-slate-400 dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:icon name="truck" class="size-12" />
                    <span class="text-sm">Fotografía no disponible</span>
                </div>
            @endif
        </div>
        <div class="grid grid-cols-1 gap-6 p-6 sm:grid-cols-2 lg:grid-cols-3">
            <div><flux:label>Número económico</flux:label><flux:input icon="hashtag" value="{{ $vehiculo->numero_economico }}" readonly /></div>
            <div><flux:label>Placas</flux:label><flux:input icon="identification" value="{{ $vehiculo->placas }}" readonly /></div>
            <div><flux:label>Marca</flux:label><flux:input icon="truck" value="{{ $vehiculo->marca }}" readonly /></div>
            <div><flux:label>Modelo</flux:label><flux:input icon="truck" value="{{ $vehiculo->modelo }}" readonly /></div>
            <div><flux:label>Año</flux:label><flux:input value="{{ $vehiculo->anio }}" readonly /></div>
            <div><flux:label>Tipo de vehículo</flux:label><flux:input icon="squares-2x2" value="{{ $vehiculo->{'tipo vehiculo'} }}" readonly /></div>
            <div><flux:label>Color</flux:label><flux:input icon="swatch" value="{{ $vehiculo->color }}" readonly /></div>
            <div><flux:label>Tipo de combustible</flux:label><flux:input icon="bolt" value="{{ $vehiculo->{'tipo combustible'} }}" readonly /></div>
            <div><flux:label>Kilometraje actual</flux:label><flux:input value="{{ number_format((float) $vehiculo->{'kilometraje actual'}, 2) }} km" readonly /></div>
            <div><flux:label>Fecha de adquisición</flux:label><flux:input icon="calendar-days" value="{{ $vehiculo->fecha_adquisicion->format('d/m/Y') }}" readonly /></div>
            <div><flux:label>Estado</flux:label><flux:input :icon="$vehiculo->estado === 'Activo' ? 'check-circle' : 'no-symbol'" value="{{ $vehiculo->estado }}" readonly /></div>
        </div>
        <div class="flex justify-end gap-2 px-6 pb-6">
            <a href="{{ route('admin.vehiculos.index') }}" class="inline-flex items-center rounded bg-gray-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-gray-600"><i class="fas fa-arrow-left mr-2"></i> Volver al listado</a>
            <a href="{{ route('admin.vehiculos.edit', $vehiculo->getKey()) }}" class="inline-flex items-center rounded bg-amber-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-amber-600"><i class="fas fa-pen mr-2"></i> Editar vehículo</a>
        </div>
    </div>
</x-layouts.app>
