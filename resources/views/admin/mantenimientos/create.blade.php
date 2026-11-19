<x-layouts.app title="Registrar mantenimiento">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Registrar mantenimiento</flux:heading>
        <br><flux:separator variant="subtle" />
    </div>

    <div class="rounded-lg border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <form action="{{ route('admin.mantenimientos.store') }}" method="POST" autocomplete="off">
            @csrf
            <div class="grid grid-cols-1 gap-6 p-6 sm:grid-cols-2 lg:grid-cols-3">
                <div class="sm:col-span-2 lg:col-span-3">
                    <flux:label>Vehículo <span class="text-red-500">(*)</span></flux:label>
                    <flux:select name="vehiculo_id" required>
                        <flux:select.option value="">Selecciona un vehículo</flux:select.option>
                        @foreach ($vehiculos as $vehiculo)
                            <flux:select.option value="{{ $vehiculo->getKey() }}" :selected="(string) old('vehiculo_id') === (string) $vehiculo->getKey()">{{ $vehiculo->numero_economico }} — {{ $vehiculo->marca }} {{ $vehiculo->modelo }} — {{ $vehiculo->placas }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="vehiculo_id" />
                </div>
                <div><flux:label>Fecha del mantenimiento <span class="text-red-500">(*)</span></flux:label><!-- <flux:input name="fecha_mantenimiento" type="date" value="{{ old('fecha_mantenimiento') }}" required /> --><flux:input name="fecha_mantenimiento" type="date" value="{{ old('fecha_mantenimiento') }}" required placeholder="AAAA-MM-DD" /><flux:error name="fecha_mantenimiento" /></div>
                <div><flux:label>Kilometraje del vehículo</flux:label><flux:input id="kilometraje-mantenimiento" type="text" readonly placeholder="Selecciona un vehículo" /></div>
                <div><flux:label>Categoría <span class="text-red-500">(*)</span></flux:label><!-- <flux:input name="categoria_mantenimiento" maxlength="45" value="{{ old('categoria_mantenimiento') }}" required /> --><flux:input name="categoria_mantenimiento" maxlength="45" value="{{ old('categoria_mantenimiento') }}" required placeholder="Ej. Preventivo" /><flux:error name="categoria_mantenimiento" /></div>
                <div class="sm:col-span-2 lg:col-span-3"><flux:label>Descripción del servicio <span class="text-red-500">(*)</span></flux:label><!-- <flux:textarea name="descripcion_mantenimiento" maxlength="255" required>{{ old('descripcion_mantenimiento') }}</flux:textarea> --><flux:textarea name="descripcion_mantenimiento" maxlength="255" required placeholder="Ej. Cambio de aceite y revision de filtros">{{ old('descripcion_mantenimiento') }}</flux:textarea><flux:error name="descripcion_mantenimiento" /></div>
                <div><flux:label>Monto <span class="text-red-500">(*)</span></flux:label><!-- <flux:input name="monto_mantenimiento" type="number" min="0" max="99999999.99" step="0.01" value="{{ old('monto_mantenimiento') }}" required /> --><flux:input name="monto_mantenimiento" type="number" min="0" max="99999999.99" step="0.01" value="{{ old('monto_mantenimiento') }}" required placeholder="Ej. 1500.00" /><flux:error name="monto_mantenimiento" /></div>
                <div><flux:label>Fecha del próximo mantenimiento <span class="text-gray-500">(opcional)</span></flux:label><!-- <flux:input name="proximo_mantenimiento_fecha" type="date" value="{{ old('proximo_mantenimiento_fecha') }}" /> --><flux:input name="proximo_mantenimiento_fecha" type="date" value="{{ old('proximo_mantenimiento_fecha') }}" placeholder="AAAA-MM-DD" /><flux:error name="proximo_mantenimiento_fecha" /></div>
                <div><flux:label>Kilometraje del próximo mantenimiento <span class="text-gray-500">(opcional)</span></flux:label><!-- <flux:input name="proximo_mantenimiento_km" type="number" min="0" step="1" value="{{ old('proximo_mantenimiento_km') }}" /> --><flux:input name="proximo_mantenimiento_km" type="number" min="0" step="1" value="{{ old('proximo_mantenimiento_km') }}" placeholder="Ej. 90000" /><flux:error name="proximo_mantenimiento_km" /></div>
            </div>
            <div class="flex justify-end gap-2 px-6 pb-6">
                <a href="{{ route('admin.mantenimientos.index') }}" class="inline-flex items-center rounded bg-gray-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-gray-600">Cancelar</a>
                <flux:button variant="primary" type="submit" color="blue">Guardar mantenimiento</flux:button>
            </div>
        </form>
    </div>
    <script>
        (() => {
        const inicializarKilometraje = () => {
            const kilometrajes = @json($kilometrajes);
            const vehiculoSelect = document.querySelector('select[name="vehiculo_id"]');
            const kilometrajeInput = document.getElementById('kilometraje-mantenimiento');

            if (!vehiculoSelect || !kilometrajeInput) return;

            const actualizarKilometraje = () => {
                const kilometraje = kilometrajes[vehiculoSelect.value];
                kilometrajeInput.value = !vehiculoSelect.value
                    ? ''
                    : (kilometraje === null || kilometraje === undefined
                        ? 'Kilometraje no disponible'
                        : `${new Intl.NumberFormat('es-MX').format(kilometraje)} km`);
            };

            if (vehiculoSelect.dataset.kilometrajeBound !== 'true') {
                vehiculoSelect.addEventListener('change', actualizarKilometraje);
                vehiculoSelect.dataset.kilometrajeBound = 'true';
            }
            actualizarKilometraje();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', inicializarKilometraje, { once: true });
        } else {
            inicializarKilometraje();
        }
        document.addEventListener('livewire:navigated', inicializarKilometraje);
        })();
    </script>
</x-layouts.app>
