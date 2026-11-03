<x-layouts.app title="Ajustes del sistema">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Ajustes del sistema</flux:heading>
        <flux:separator variant="subtle" />
    </div>

    {{-- Card --}}
    <section class="mx-auto w-full max-w-5xl overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <form action="{{ url('/admin/ajustes') }}" method="POST">
            @csrf
            {{-- Body --}}
            <div class="p-5 sm:p-6">

                <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">

                    <div class="min-w-0">
                        <flux:label>Nombre de la Empresa <span class="ml-1 text-red-500" title="Campo obligatorio">*</span></flux:label>
                        <flux:input name="nombre" icon="building-office" placeholder="Nombre Comercial" required />
                        <flux:error name="nombre" />
                    </div>

                    <div class="min-w-0">
                        <flux:label>Descripción</flux:label>
                        <flux:input name="descripcion" icon="document-text"
                            placeholder="Breve reseña de la empresa..." />
                        <flux:error name="descripcion" />
                    </div>

                    <div class="min-w-0">
                        <flux:label>Dirección <span class="ml-1 text-red-500" title="Campo obligatorio">*</span></flux:label>
                        <flux:input name="direccion" icon="map-pin" placeholder="Calle, Ciudad, País" required />
                        <flux:error name="direccion" />
                    </div>

                    <div class="min-w-0">
                        <flux:label>Teléfono <span class="ml-1 text-red-500" title="Campo obligatorio">*</span></flux:label>
                        <flux:input name="telefono" icon="phone" placeholder="+00 000 000" required />
                        <flux:error name="telefono" />
                    </div>

                    <div class="min-w-0">
                        <flux:label>Email de Contacto <span class="ml-1 text-red-500" title="Campo obligatorio">*</span></flux:label>
                        <flux:input name="email" type="email" icon="envelope" placeholder="Email de contacto" required />
                        <flux:error name="email" />
                    </div>

                    <div class="min-w-0">
                        <flux:label>Divisa <span class="ml-1 text-red-500" title="Campo obligatorio">*</span></flux:label>
                        <flux:select placeholder="Selecciona una divisa..." name="divisa" required>
                            <flux:select.option value="BOB">Bolivianos</flux:select.option>
                        </flux:select>
                    </div>

                    <div class="min-w-0">
                        <flux:label>Tasa de Interés Mensual (%)</flux:label>
                        <flux:input name="interes" type="number" step="0.01" icon="receipt-percent" placeholder="10.00" />
                        <flux:error name="interes" />
                    </div>

                    <div class="min-w-0">
                        <flux:label>Tasa de Mora (%)</flux:label>
                        <flux:input name="mora" type="number" step="0.01" icon="clock" placeholder="2.00" />
                        <flux:error name="mora" />
                    </div>

                    <div class="min-w-0">
                        <flux:label>Logo Institucional</flux:label>

                        <div class="flex items-center gap-6">
                            <div class="relative group">
                                <div
                                    class="h-20 w-20 rounded-2xl border-2 border-slate-100 overflow-hidden bg-slate-50 flex items-center justify-center shadow-inner">
                                    <img id="image-preview" src="#" alt="Preview"
                                        class="hidden h-full w-full object-cover">
                                    <flux:icon id="placeholder-icon" name="photo" class="text-slate-300 h-8 w-8" />
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <div class="flex items-center gap-3">
                                    <input type="file" name="logo" id="logo-input" class="hidden"
                                        accept="image/*">

                                    <label for="logo-input"
                                        class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                                        <flux:icon name="cloud-arrow-up" variant="micro" />
                                        <span>Seleccionar Logo</span>
                                    </label>
                                </div>

                                <span id="file-chosen" class="text-sm text-slate-400 italic ml-1">Ningún archivo seleccionado</span>
                            </div>
                        </div>

                        <flux:error name="logo" />
                    </div>

                    <div class="min-w-0">
                        <flux:label>Sitio Web</flux:label>
                        <flux:input name="web" icon="globe-alt" placeholder="www.empresa.com" />
                        <flux:error name="web" />
                    </div>

                </div>

            </div>

            {{-- Footer --}}
            <div class="border-t border-zinc-200 bg-zinc-50 px-5 py-4 dark:border-zinc-700 dark:bg-zinc-800 sm:px-6">
                <div class="flex flex-wrap justify-end gap-3">
                    <a href="{{ url('/login') }}"
                        class="inline-flex items-center rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400 focus:ring-offset-2 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-700 dark:focus:ring-offset-zinc-800">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </a>
                    <flux:button variant="primary" type="submit" class="cursor-pointer" color="blue">
                        <i class="fas fa-save"></i> Guardar
                    </flux:button>
                </div>
            </div>

        </form>
    </section>

    {{-- Card --}}
    <script>
        const actualBtn = document.getElementById('logo-input');
        const fileChosen = document.getElementById('file-chosen');
        const preview = document.getElementById('image-preview');
        const placeholderIcon = document.getElementById('placeholder-icon');

        actualBtn.addEventListener('change', function() {
            const file = this.files[0];

            if (file) {
                // Actualizar texto del nombre
                fileChosen.textContent = file.name;
                fileChosen.classList.remove('text-slate-400');
                fileChosen.classList.add('text-indigo-600', 'font-medium');

                // Lógica de Previsualización
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    placeholderIcon.classList.add('hidden');
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</x-layouts.app>
