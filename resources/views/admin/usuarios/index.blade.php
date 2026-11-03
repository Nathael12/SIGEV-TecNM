<x-layouts.app title="Usuarios del sistema">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Usuarios del sistema</flux:heading>
        <br>
        <flux:separator variant="subtle" />
    </div>

    <div class="flex justify-end">
        <a href="{{ url('/admin/usuarios/create') }}"
            class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded transition">
            <i class="fas fa-plus mr-2"></i>
            Crear nuevo
        </a>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 mt-6">
        <table class="w-full min-w-full border-collapse" style="width: 100%;">
            <thead class="bg-gray-50 dark:bg-zinc-900 text-center">
                <tr>
                    <th
                        class="px-6 py-3 border-x border-b border-gray-200 dark:border-zinc-700 text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Nro</th>
                    <th
                        class="px-6 py-3 border-x border-b border-gray-200 dark:border-zinc-700 text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Rol</th>
                    <th
                        class="px-6 py-3 border-x border-b border-gray-200 dark:border-zinc-700 text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Nombres y apellidos</th>
                    <th
                        class="px-6 py-3 border-x border-b border-gray-200 dark:border-zinc-700 text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Correo</th>
                    <th
                        class="px-6 py-3 border-x border-b border-gray-200 dark:border-zinc-700 text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Teléfono</th>
                    <th
                        class="px-6 py-3 border-x border-b border-gray-200 dark:border-zinc-700 text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Estado</th>
                    <th
                        class="px-6 py-3 border-x border-b border-gray-200 dark:border-zinc-700 text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-zinc-800">
                @forelse ($usuarios as $usuario)
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition">
                        {{-- Nro --}}
                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            {{ $loop->iteration }}</td>

                        {{-- Rol --}}
                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            {{ $usuario->roles->pluck('name')->join(', ') }}</td>

                        {{-- Nombres y apellidos --}}
                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ $usuario->nombre_usuario }} {{ $usuario->apellido_paterno }}
                            {{ $usuario->apellido_materno }}</td>

                        {{-- Correo --}}
                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ $usuario->correo_usuario }}</td>

                        {{-- Teléfono --}}
                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center">
                            {{ $usuario->telefono_usuario }}</td>

                        {{-- Estado: depende de si está eliminado (deleted_at) --}}
                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-sm text-center">
                            @if ($usuario->deleted_at)
                                <span
                                    class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">Inactivo</span>
                            @else
                                <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full"
                                    style="color: #15803d; background-color: #dcfce7;">Activo</span>
                            @endif
                        </td>

                        {{-- Acciones --}}
                        <td
                            class="px-3 py-2 border border-gray-200 dark:border-zinc-700 whitespace-nowrap text-center">
                            <div class="flex justify-center gap-2">

                                @if ($usuario->deleted_at)
                                    {{-- Usuario eliminado: solo Restaurar --}}
                                    <form action="{{ url('/admin/usuario/' . $usuario->id . '/restaurar') }}"
                                        method="post"
                                        onsubmit="confirmarAccion(event, this, '¿Desea restaurar este registro?', 'Restaurar', '#a68d1e')">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-semibold rounded transition">
                                            <i class="fas fa-undo-alt mr-2"></i> Restaurar
                                        </button>
                                    </form>
                                @else
                                    {{-- Ver --}}
                                    <a href="{{ url('/admin/usuario/' . $usuario->id) }}"
                                        class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-xs font-semibold rounded transition">
                                        <i class="fas fa-eye mr-2"></i> Ver
                                    </a>

                                    {{-- Editar --}}
                                    <a href="{{ url('/admin/usuario/' . $usuario->id . '/edit') }}"
                                        class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold rounded transition">
                                        <i class="fas fa-pencil-alt mr-2"></i> Editar
                                    </a>

                                    {{-- Eliminar --}}
                                    <form action="{{ url('/admin/usuario/' . $usuario->id) }}" method="post"
                                        onsubmit="confirmarAccion(event, this, '¿Desea eliminar este registro?', 'Eliminar', '#a5161d')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-xs font-semibold rounded transition">
                                            <i class="fas fa-trash-alt mr-2"></i> Eliminar
                                        </button>
                                    </form>
                                @endif

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7"
                            class="px-3 py-6 border border-gray-200 dark:border-zinc-700 text-center text-sm text-gray-500">
                            No hay usuarios registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Script único de confirmación (sirve para eliminar y restaurar) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmarAccion(event, form, titulo, textoBoton, colorBoton) {
            event.preventDefault();

            Swal.fire({
                title: titulo,
                icon: 'question',
                showDenyButton: true,
                confirmButtonText: textoBoton,
                confirmButtonColor: colorBoton,
                denyButtonColor: '#270a0a',
                denyButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>

</x-layouts.app>