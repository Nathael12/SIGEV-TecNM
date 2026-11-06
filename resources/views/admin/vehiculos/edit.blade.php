<x-layouts.app title="Editar vehículo">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Editar vehículo</flux:heading>
        <br><flux:separator variant="subtle" />
    </div>

    <div class="rounded-lg border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <form action="{{ route('admin.vehiculos.update', $vehiculo->getKey()) }}" method="POST" autocomplete="off">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-6 p-6 sm:grid-cols-2 lg:grid-cols-3">
                <div><flux:label>Número económico <span class="text-red-500">(*)</span></flux:label><flux:input name="numero_economico" value="{{ old('numero_economico', $vehiculo->numero_economico) }}" icon="hashtag" required /><flux:error name="numero_economico" /></div>
                <div><flux:label>Placas <span class="text-red-500">(*)</span></flux:label><flux:input name="placas" value="{{ old('placas', $vehiculo->placas) }}" icon="identification" required /><flux:error name="placas" /></div>
                <div><flux:label>Marca <span class="text-red-500">(*)</span></flux:label><flux:input name="marca" value="{{ old('marca', $vehiculo->marca) }}" icon="truck" required /><flux:error name="marca" /></div>
                <div><flux:label>Modelo <span class="text-red-500">(*)</span></flux:label><flux:input name="modelo" value="{{ old('modelo', $vehiculo->modelo) }}" icon="truck" required /><flux:error name="modelo" /></div>
                <div><flux:label>Año <span class="text-red-500">(*)</span></flux:label><flux:input name="anio" type="number" min="1901" max="{{ now()->year + 1 }}" value="{{ old('anio', $vehiculo->anio) }}" required /><flux:error name="anio" /></div>
                <div><flux:label>Tipo <span class="text-red-500">(*)</span></flux:label><flux:input name="tipo" value="{{ old('tipo', $vehiculo->tipo) }}" icon="squares-2x2" required /><flux:error name="tipo" /></div>
                <div><flux:label>Color <span class="text-red-500">(*)</span></flux:label><flux:input name="color" value="{{ old('color', $vehiculo->color) }}" icon="swatch" required /><flux:error name="color" /></div>
                <div><flux:label>Combustible <span class="text-red-500">(*)</span></flux:label><flux:input name="combustible" value="{{ old('combustible', $vehiculo->combustible) }}" icon="bolt" required /><flux:error name="combustible" /></div>
                <div><flux:label>Kilometraje <span class="text-red-500">(*)</span></flux:label><flux:input name="kilometraje" type="number" min="0" max="99999999.99" step="0.01" value="{{ old('kilometraje', $vehiculo->kilometraje) }}" required /><flux:error name="kilometraje" /></div>
                <div><flux:label>Fecha de adquisición <span class="text-red-500">(*)</span></flux:label><flux:input name="fecha_adquisicion" type="date" value="{{ old('fecha_adquisicion', $vehiculo->fecha_adquisicion->format('Y-m-d')) }}" required /><flux:error name="fecha_adquisicion" /></div>
                <div>
                    <flux:label>Estado <span class="text-red-500">(*)</span></flux:label>
                    <flux:select name="estado" required>
                        <flux:select.option value="Activo" :selected="old('estado', $vehiculo->estado) === 'Activo'">Activo</flux:select.option>
                        <flux:select.option value="Inactivo" :selected="old('estado', $vehiculo->estado) === 'Inactivo'">Inactivo</flux:select.option>
                    </flux:select>
                    <flux:error name="estado" />
                </div>
            </div>
            <div class="flex justify-end gap-2 px-6 pb-6">
                <a href="{{ route('admin.vehiculos.index') }}" class="inline-flex items-center rounded bg-gray-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-gray-600">Cancelar</a>
                <flux:button variant="primary" type="submit" color="blue">Actualizar vehículo</flux:button>
            </div>
        </form>
    </div>
</x-layouts.app>
