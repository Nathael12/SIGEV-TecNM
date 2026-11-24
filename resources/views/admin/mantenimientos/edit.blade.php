<x-layouts.app title="Editar mantenimiento">
    <x-sweetalert-flash />
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Editar mantenimiento</flux:heading>
        <br><flux:separator variant="subtle" />
    </div>

    <div class="rounded-lg border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <form action="{{ route('admin.mantenimientos.update', $mantenimiento) }}" method="POST" autocomplete="off" data-submit-loading="1" data-loading-text="Guardando cambios...">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-6 p-6 sm:grid-cols-2 lg:grid-cols-3">
                @php($vehiculoSeleccionado = old('vehiculo_id', $mantenimiento->vehiculo_id))
                @php($kilometrajeMostrado = (string) $vehiculoSeleccionado === (string) $mantenimiento->vehiculo_id ? $mantenimiento->kilometraje_mantenimiento : $kilometrajes->get($vehiculoSeleccionado))
                <div class="sm:col-span-2 lg:col-span-3">
                    <flux:label>Vehículo <span class="text-red-500">(*)</span></flux:label>
                    <flux:select name="vehiculo_id" required>
                        <flux:select.option value="">Selecciona un vehículo</flux:select.option>
                        @foreach ($vehiculos as $vehiculo)
                            <flux:select.option value="{{ $vehiculo->getKey() }}" :selected="(string) old('vehiculo_id', $mantenimiento->vehiculo_id) === (string) $vehiculo->getKey()">{{ $vehiculo->numero_economico }} — {{ $vehiculo->marca }} {{ $vehiculo->modelo }} — {{ $vehiculo->placas }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="vehiculo_id" />
                </div>
                <div>
                    <flux:label>Estado del mantenimiento</flux:label>
                    <flux:select name="estado_mantenimiento" required>
                        <flux:select.option value="pendiente" :selected="old('estado_mantenimiento', $mantenimiento->estado_mantenimiento) === 'pendiente'">Pendiente</flux:select.option>
                        <flux:select.option value="realizado" :selected="in_array(old('estado_mantenimiento', $mantenimiento->estado_mantenimiento), ['realizado', 'finalizado'], true)">Finalizado</flux:select.option>
                        <flux:select.option value="cancelado" :selected="old('estado_mantenimiento', $mantenimiento->estado_mantenimiento) === 'cancelado'">Cancelado</flux:select.option>
                    </flux:select>
                    <flux:error name="estado_mantenimiento" />
                </div>
                <div><flux:label>Fecha del mantenimiento <span class="text-red-500">(*)</span></flux:label><flux:input name="fecha_mantenimiento" type="date" value="{{ old('fecha_mantenimiento', $mantenimiento->fecha_mantenimiento->format('Y-m-d')) }}" required /><flux:error name="fecha_mantenimiento" /></div>
                <div><flux:label>Kilometraje del vehículo</flux:label><flux:input id="kilometraje-mantenimiento" type="text" value="{{ $kilometrajeMostrado !== null ? number_format((int) $kilometrajeMostrado) . ' km' : 'Kilometraje no disponible' }}" readonly /></div>
                <div><flux:label>Categoría <span class="text-red-500">(*)</span></flux:label><flux:input name="categoria_mantenimiento" maxlength="45" value="{{ old('categoria_mantenimiento', $mantenimiento->categoria_mantenimiento) }}" required /><flux:error name="categoria_mantenimiento" /></div>
                <div class="sm:col-span-2 lg:col-span-3"><flux:label>Descripción del servicio <span class="text-red-500">(*)</span></flux:label><flux:textarea name="descripcion_mantenimiento" maxlength="255" required placeholder="Ej. Cambio de aceite y revision de filtros">{{ old('descripcion_mantenimiento', $mantenimiento->descripcion_mantenimiento) }}</flux:textarea><flux:error name="descripcion_mantenimiento" /></div>
                <div><flux:label>Monto <span class="text-red-500">(*)</span></flux:label><flux:input name="monto_mantenimiento" type="number" min="0" max="99999999.99" step="0.01" value="{{ old('monto_mantenimiento', $mantenimiento->monto_mantenimiento) }}" required /><flux:error name="monto_mantenimiento" /></div>
                <div><flux:label>Fecha del próximo mantenimiento <span class="text-gray-500">(opcional)</span></flux:label><flux:input name="proximo_mantenimiento_fecha" type="date" value="{{ old('proximo_mantenimiento_fecha', $mantenimiento->proximo_mantenimiento_fecha?->format('Y-m-d')) }}" /><flux:error name="proximo_mantenimiento_fecha" /></div>
                <div><flux:label>Kilometraje del próximo mantenimiento <span class="text-gray-500">(opcional)</span></flux:label><flux:input name="proximo_mantenimiento_km" type="number" min="0" step="1" value="{{ old('proximo_mantenimiento_km', $mantenimiento->proximo_mantenimiento_km) }}" /><flux:error name="proximo_mantenimiento_km" /></div>
            </div>
            <div class="flex justify-end gap-2 px-6 pb-6">
                <a href="{{ route('admin.mantenimientos.index') }}" class="inline-flex items-center rounded bg-gray-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-gray-600">Cancelar</a>
                <flux:button variant="primary" type="submit" color="blue">Guardar cambios</flux:button>
            </div>
        </form>
    </div>
    <script>
        (() => {
        const inicializarKilometraje = () => {
            const kilometrajes = @json($kilometrajes);
            const vehiculoOriginal = @json((string) $mantenimiento->vehiculo_id);
            const kilometrajeOriginal = @json((int) $mantenimiento->kilometraje_mantenimiento);
            const vehiculoSelect = document.querySelector('select[name="vehiculo_id"]');
            const kilometrajeInput = document.getElementById('kilometraje-mantenimiento');

            if (!vehiculoSelect || !kilometrajeInput) return;

            const actualizarKilometraje = () => {
                if (vehiculoSelect.value === vehiculoOriginal) {
                    kilometrajeInput.value = `${new Intl.NumberFormat('es-MX').format(kilometrajeOriginal)} km`;
                    return;
                }

                const kilometraje = kilometrajes[vehiculoSelect.value];
                kilometrajeInput.value = kilometraje === null || kilometraje === undefined
                    ? 'Kilometraje no disponible'
                    : `${new Intl.NumberFormat('es-MX').format(kilometraje)} km`;
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
