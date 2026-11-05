<x-layouts.app title="Editar usuario">

    {{-- Cuadrícula propia: no depende de que Tailwind compile clases nuevas --}}
    <style>
        .grid-usuarios {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 24px 16px;
        }

        .grid-usuarios>div {
            min-width: 0;
        }

        @media (min-width: 640px) {
            .grid-usuarios {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .grid-usuarios .campo-doble {
                grid-column: span 2 / span 2;
            }
        }

        @media (min-width: 1024px) {
            .grid-usuarios {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
    </style>

    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Editar usuario</flux:heading>
        <br>
        <flux:separator variant="subtle" />
    </div>

    <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
        <form action="{{ url('/admin/usuario/' . $usuario->id) }}" method="POST" autocomplete="off">
            @csrf
            @method('PUT')

            <div class="p-6">
                <div class="grid-usuarios">

                    {{-- Fila 1 --}}

                    {{-- Nombre(s) --}}
                    <div>
                        <flux:label>Nombre(s) <span class="text-red-500" title="Campo obligatorio">(*)</span>
                        </flux:label>
                        <flux:input name="nombre_usuario"
                            value="{{ old('nombre_usuario', $usuario->nombre_usuario) }}" icon="user"
                            placeholder="Ej. Juan Carlos" autocomplete="off" required />
                        <flux:error name="nombre_usuario" />
                    </div>

                    {{-- Apellido paterno --}}
                    <div>
                        <flux:label>Apellido paterno <span class="text-red-500" title="Campo obligatorio">(*)</span>
                        </flux:label>
                        <flux:input name="apellido_paterno"
                            value="{{ old('apellido_paterno', $usuario->apellido_paterno) }}" icon="identification"
                            placeholder="Ej. Pérez" autocomplete="off" required />
                        <flux:error name="apellido_paterno" />
                    </div>

                    {{-- Apellido materno --}}
                    <div>
                        <flux:label>Apellido materno <span class="text-red-500" title="Campo obligatorio">(*)</span>
                        </flux:label>
                        <flux:input name="apellido_materno"
                            value="{{ old('apellido_materno', $usuario->apellido_materno) }}" icon="identification"
                            placeholder="Ej. López" autocomplete="off" required />
                        <flux:error name="apellido_materno" />
                    </div>

                    {{-- Teléfono --}}
                    <div>
                        <flux:label>Teléfono <span class="text-red-500" title="Campo obligatorio">(*)</span>
                        </flux:label>
                        <flux:input name="telefono_usuario"
                            value="{{ old('telefono_usuario', $usuario->telefono_usuario) }}" icon="phone"
                            placeholder="Ej. 9961234567" maxlength="10" inputmode="numeric" autocomplete="off"
                            required />
                        <flux:error name="telefono_usuario" />
                    </div>

                    {{-- Fila 2 --}}

                    {{-- Correo (ocupa 2 columnas) --}}
                    <div class="campo-doble">
                        <flux:label>Correo electrónico <span class="text-red-500" title="Campo obligatorio">(*)</span>
                        </flux:label>
                        <flux:input name="correo_usuario" type="email"
                            value="{{ old('correo_usuario', $usuario->correo_usuario) }}" icon="envelope"
                            placeholder="ejemplo@correo.com" autocomplete="off" required />
                        <flux:error name="correo_usuario" />
                    </div>

                    {{-- Rol --}}
                    <div>
                        <flux:label>Rol <span class="text-red-500" title="Campo obligatorio">(*)</span></flux:label>
                        <flux:select placeholder="Selecciona un rol..." name="rol" required>
                            @foreach ($roles as $rol)
                                <flux:select.option value="{{ $rol->name }}"
                                    :selected="old('rol', $usuario->roles->first()?->name) == $rol->name">
                                    {{ $rol->name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="rol" />
                    </div>

                    {{-- Estado (solo lectura: se cambia con Eliminar / Restaurar) --}}
                    <div>
                        <flux:label>Estado No Editable</flux:label>
                        <flux:select name="estado" disabled>
                            <flux:select.option value="Activo"
                                :selected="$usuario->estado == 'Activo'">Activo</flux:select.option>
                            <flux:select.option value="Inactivo"
                                :selected="$usuario->estado == 'Inactivo'">Inactivo</flux:select.option>
                        </flux:select>
                    </div>

                    {{-- Fila 3: contraseña opcional --}}

                    {{-- Nueva contraseña (ocupa 2 columnas) --}}
                    <div class="campo-doble">
                        <flux:label>Nueva contraseña</flux:label>
                        <flux:input name="password" type="password" icon="lock-closed"
                            placeholder="Déjalo vacío para conservar la actual" autocomplete="new-password"
                            viewable />
                        <flux:error name="password" />
                    </div>

                    {{-- Confirmar nueva contraseña (ocupa 2 columnas) --}}
                    <div class="campo-doble">
                        <flux:label>Confirmar nueva contraseña</flux:label>
                        <flux:input name="password_confirmation" type="password" icon="shield-check"
                            placeholder="Repite la nueva contraseña" autocomplete="new-password" viewable />
                        <flux:error name="password_confirmation" />
                    </div>

                </div>

                {{-- Botones --}}
                <div class="flex justify-end gap-2" style="margin-top: 32px;">
                    <a href="{{ url('/admin/usuarios') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-xs font-semibold rounded transition">
                        <i class="fas fa-arrow-left mr-2"></i> Cancelar
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold rounded transition">
                        <i class="fas fa-save mr-2"></i> Actualizar
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>