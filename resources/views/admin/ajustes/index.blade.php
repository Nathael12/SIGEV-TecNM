<x-layouts.app title="Ajustes del sistema">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Ajustes del sistema</flux:heading>
        <flux:separator variant="subtle" />
    </div>

    {{-- Card --}}
    <section class="mx-auto w-full max-w-5xl overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <form action="{{ url('/admin/ajustes') }}" method="POST" enctype="multipart/form-data">
            @csrf
            {{-- Body --}}
            <div class="p-5 sm:p-6">

                <div class="grid grid-cols-1 items-start gap-x-6 gap-y-5 sm:grid-cols-2">
                    <div class="flex min-w-0 flex-col gap-y-5">
                        <div class="min-w-0">
                        <flux:label>Nombre de la empresa<span class="ml-1 text-red-500" title="Campo obligatorio">*</span></flux:label>
                        <flux:input name="nombre" icon="building-office" placeholder="Nombre Comercial" :value="old('nombre', $ajuste->nombre)" maxlength="255" class="w-full min-w-0" required />
                        <flux:error name="nombre" />
                    </div>

                        <div class="min-w-0">
                        <flux:label>Dirección <span class="ml-1 text-red-500" title="Campo obligatorio">*</span></flux:label>
                        <div class="relative min-w-0">
                            <flux:icon name="map-pin" class="pointer-events-none absolute left-3 top-3 h-5 w-5 text-zinc-400 dark:text-zinc-500" />
                            <flux:textarea name="direccion" rows="auto" maxlength="255" placeholder="Calle, Ciudad, País"
                                resize="none" class="w-full min-w-0 ps-10 [overflow-wrap:anywhere]" required>{{ old('direccion', $ajuste->direccion) }}</flux:textarea>
                        </div>
                        <flux:error name="direccion" />
                    </div>

                        <div class="min-w-0">
                        <flux:label>Email de Contacto <span class="ml-1 text-red-500" title="Campo obligatorio">*</span></flux:label>
                        <flux:input name="email" type="email" icon="envelope" placeholder="Email de contacto" :value="old('email', $ajuste->email)" maxlength="255" class="w-full min-w-0" required />
                        <flux:error name="email" />
                    </div>

                        <div class="min-w-0">
                        <flux:label>Sitio Web</flux:label>
                        <flux:input name="web" icon="globe-alt" placeholder="www.empresa.com" :value="old('web', $ajuste->web)" maxlength="255" class="w-full min-w-0" />
                        <flux:error name="web" />
                    </div>
                    </div>
                    <div class="flex min-w-0 flex-col gap-y-5">
                        <div class="min-w-0">
                        <flux:label>Descripción</flux:label>
                        <div class="relative min-w-0">
                            <flux:icon name="document-text" class="pointer-events-none absolute left-3 top-3 h-5 w-5 text-zinc-400 dark:text-zinc-500" />
                            <flux:textarea name="descripcion" rows="4" maxlength="255"
                                placeholder="Breve reseña de la empresa..." resize="none" class="w-full min-w-0 ps-10 [overflow-wrap:anywhere]">{{ old('descripcion', $ajuste->descripcion) }}</flux:textarea>
                        </div>
                        <flux:error name="descripcion" />
                    </div>

                        <div class="min-w-0">
                        <flux:label>Teléfono <span class="ml-1 text-red-500" title="Campo obligatorio">*</span></flux:label>
                        <flux:input name="telefono" icon="phone" placeholder="+00 000 000" :value="old('telefono', $ajuste->telefono)" maxlength="20" class="w-full min-w-0" required />
                        <flux:error name="telefono" />
                    </div>

                        <div class="min-w-0">
                        <flux:label>Logo Institucional</flux:label>

                        <div class="flex items-center gap-6">
                            <div class="relative group">
                                <div
                                    class="h-20 w-20 rounded-2xl border-2 border-slate-100 overflow-hidden bg-slate-50 flex items-center justify-center shadow-inner">
                                    <img id="image-preview" @if ($logoUrl) src="{{ $logoUrl }}" @endif alt="Vista previa del logo institucional"
                                        @class(['h-full w-full object-cover', 'hidden' => ! $logoUrl])>
                                    <flux:icon id="placeholder-icon" name="photo" @class(['text-slate-300 h-8 w-8', 'hidden' => $logoUrl]) />
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <div class="flex items-center gap-3">
                                    <input type="file" name="logo" id="logo-input" class="hidden"
                                        accept="image/*">

                                    <label for="logo-input" id="logo-label"
                                        class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                                        <flux:icon name="cloud-arrow-up" variant="micro" />
                                        <span>{{ $logoUrl ? 'Cambiar logo' : 'Seleccionar logo' }}</span>
                                    </label>
                                </div>

                                <span id="file-chosen" class="text-sm text-slate-400 italic ml-1">{{ $logoUrl ? 'Logo actual' : 'Ningún archivo seleccionado' }}</span>
                            </div>
                        </div>

                        <flux:error name="logo" />
                    </div>
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
        const logoLabel = document.getElementById('logo-label').querySelector('span');

        actualBtn.addEventListener('change', function() {
            const file = this.files[0];

            if (file) {
                // Actualizar texto del nombre
                fileChosen.textContent = file.name;
                fileChosen.classList.remove('text-slate-400');
                fileChosen.classList.add('text-indigo-600', 'font-medium');
                logoLabel.textContent = 'Cambiar logo';

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
