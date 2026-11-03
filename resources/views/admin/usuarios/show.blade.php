<x-layouts.app title="Ver usuario">

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
        <flux:heading size="xl" level="1">Datos del usuario</flux:heading>
        <br>
        <flux:separator variant="subtle" />
    </div>

    <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
        <div class="p-6">
            <div class="grid-usuarios">

                {{-- Fila 1 --}}

                <div>
                    <flux:label>Nombre(s)</flux:label>
                    <flux:input icon="user" value="{{ $usuario->nombre_usuario }}" readonly />
                </div>

                <div>
                    <flux:label>Apellido paterno</flux:label>
                    <flux:input icon="identification" value="{{ $usuario->apellido_paterno }}" readonly />
                </div>

                <div>
                    <flux:label>Apellido materno</flux:label>
                    <flux:input icon="identification" value="{{ $usuario->apellido_materno }}" readonly />
                </div>

                <div>
                    <flux:label>Teléfono</flux:label>
                    <flux:input icon="phone" value="{{ $usuario->telefono_usuario }}" readonly />
                </div>

                {{-- Fila 2 --}}

                <div class="campo-doble">
                    <flux:label>Correo electrónico</flux:label>
                    <flux:input icon="envelope" value="{{ $usuario->correo_usuario }}" readonly />
                </div>

                <div>
                    <flux:label>Rol</flux:label>
                    <flux:input icon="shield-check"
                        value="{{ $usuario->roles->pluck('name')->join(', ') ?: 'Sin rol' }}" readonly />
                </div>

                <div>
                    <flux:label>Estado</flux:label>
                    <flux:input :icon="$usuario->estado === 'Activo' ? 'check-circle' : 'no-symbol'"
                        value="{{ $usuario->estado }}" readonly />
                </div>

                {{-- Fila 3 --}}

                <div class="campo-doble">
                    <flux:label>Fecha de registro</flux:label>
                    <flux:input icon="calendar-days"
                        value="{{ $usuario->created_at?->format('d/m/Y H:i') }}" readonly />
                </div>

                <div class="campo-doble">
                    <flux:label>Última actualización</flux:label>
                    <flux:input icon="clock" value="{{ $usuario->updated_at?->format('d/m/Y H:i') }}"
                        readonly />
                </div>

            </div>

            {{-- Botones --}}
            <div class="flex justify-end gap-2 mt-8">
                <a href="{{ url('/admin/usuarios') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-xs font-semibold rounded transition">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>