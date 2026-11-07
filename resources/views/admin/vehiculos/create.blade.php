<x-layouts.app title="Registrar vehículo">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Registrar vehículo</flux:heading>
        <br><flux:separator variant="subtle" />
    </div>

    <div class="rounded-lg border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <form action="{{ route('admin.vehiculos.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off">
            @csrf
            <div class="grid grid-cols-1 gap-6 p-6 sm:grid-cols-2 lg:grid-cols-3">
                <div><flux:label>Número económico <span class="text-red-500">(*)</span></flux:label><flux:input name="numero_economico" value="{{ old('numero_economico') }}" icon="hashtag" required /><flux:error name="numero_economico" /></div>
                <div><flux:label>Placas <span class="text-red-500">(*)</span></flux:label><flux:input name="placas" value="{{ old('placas') }}" icon="identification" required /><flux:error name="placas" /></div>
                <div><flux:label>Marca <span class="text-red-500">(*)</span></flux:label><flux:input name="marca" value="{{ old('marca') }}" icon="truck" required /><flux:error name="marca" /></div>
                <div><flux:label>Modelo <span class="text-red-500">(*)</span></flux:label><flux:input name="modelo" value="{{ old('modelo') }}" icon="truck" required /><flux:error name="modelo" /></div>
                <div><flux:label>Año <span class="text-red-500">(*)</span></flux:label><flux:input name="anio" type="number" min="1901" max="{{ now()->year + 1 }}" value="{{ old('anio') }}" required /><flux:error name="anio" /></div>
                <div><flux:label>Tipo <span class="text-red-500">(*)</span></flux:label><flux:input name="tipo" value="{{ old('tipo') }}" icon="squares-2x2" required /><flux:error name="tipo" /></div>
                <div><flux:label>Color <span class="text-red-500">(*)</span></flux:label><flux:input name="color" value="{{ old('color') }}" icon="swatch" required /><flux:error name="color" /></div>
                <div><flux:label>Combustible <span class="text-red-500">(*)</span></flux:label><flux:input name="combustible" value="{{ old('combustible') }}" icon="bolt" required /><flux:error name="combustible" /></div>
                <div><flux:label>Kilometraje <span class="text-red-500">(*)</span></flux:label><flux:input name="kilometraje" type="number" min="0" max="99999999.99" step="0.01" value="{{ old('kilometraje') }}" required /><flux:error name="kilometraje" /></div>
                <div><flux:label>Fecha de adquisición <span class="text-red-500">(*)</span></flux:label><flux:input name="fecha_adquisicion" type="date" value="{{ old('fecha_adquisicion') }}" required /><flux:error name="fecha_adquisicion" /></div>
                <div>
                    <flux:label>Estado <span class="text-red-500">(*)</span></flux:label>
                    <flux:select name="estado" required>
                        @foreach ($estados as $estado)
                            <flux:select.option value="{{ $estado }}" :selected="old('estado', 'Disponible') === $estado">{{ $estado }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="estado" />
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <flux:label>Fotografía del vehículo <span class="text-red-500">(*)</span></flux:label>
                    <div class="mt-2 flex flex-col items-start gap-5 sm:flex-row sm:items-center">
                        <div class="relative">
                            <div class="flex h-32 w-52 items-center justify-center overflow-hidden rounded-2xl border-2 border-slate-100 bg-slate-50 shadow-inner dark:border-zinc-700 dark:bg-zinc-900">
                                <img id="fotografia-preview" src="#" alt="Vista previa de la fotografía del vehículo" class="hidden h-full w-full object-cover">
                                <flux:icon id="fotografia-placeholder" name="photo" class="size-10 text-slate-300" />
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <input id="fotografia" name="fotografia" type="file" class="hidden" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            <label for="fotografia" id="fotografia-label" class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                                <flux:icon name="cloud-arrow-up" variant="micro" />
                                <span>Seleccionar fotografía</span>
                            </label>
                            <span id="fotografia-nombre" class="ml-1 text-sm italic text-slate-400">Ningún archivo seleccionado</span>
                            <p class="text-xs text-slate-500 dark:text-zinc-400">JPG, JPEG, PNG o WEBP. Máximo 5 MB.</p>
                        </div>
                    </div>
                    <flux:error name="fotografia" />
                </div>
            </div>
            <div class="flex justify-end gap-2 px-6 pb-6">
                <a href="{{ route('admin.vehiculos.index') }}" class="inline-flex items-center rounded bg-gray-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-gray-600">Cancelar</a>
                <flux:button variant="primary" type="submit" color="blue">Guardar vehículo</flux:button>
            </div>
        </form>
    </div>
</x-layouts.app>

<script>
    (() => {
        const input = document.getElementById('fotografia');
        const preview = document.getElementById('fotografia-preview');
        const placeholder = document.getElementById('fotografia-placeholder');
        const nombre = document.getElementById('fotografia-nombre');
        const label = document.getElementById('fotografia-label').querySelector('span');

        input?.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file) return;

            nombre.textContent = file.name;
            nombre.classList.remove('text-slate-400');
            nombre.classList.add('font-medium', 'text-indigo-600');
            label.textContent = 'Cambiar fotografía';

            const reader = new FileReader();
            reader.onload = (event) => {
                preview.src = event.target.result;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        });
    })();
</script>
