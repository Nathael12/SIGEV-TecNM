<x-layouts.app title="Roles del sistema">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Roles del sistema</flux:heading>
        <flux:separator variant="subtle" />
    </div>

    <div class="mx-auto mt-5 flex w-full max-w-6xl justify-end">
        <a href="{{ url('/admin/roles/create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900">
            <i class="fas fa-plus mr-2"></i>
            Crear nuevo
        </a>
    </div>

    <div class="mx-auto mt-4 w-full max-w-6xl overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 sm:px-6">
                        Nro</th>
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 sm:px-6">
                        Rol</th>
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 sm:px-6">
                        Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">

                @foreach ($roles as $rol)
                    <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/70">
                        <td class="whitespace-nowrap px-4 py-4 text-sm text-zinc-500 dark:text-zinc-400 sm:px-6">{{ $loop->iteration }}</td>
                        <td class="whitespace-nowrap px-4 py-4 text-sm font-medium text-zinc-900 dark:text-zinc-100 sm:px-6">{{ $rol->name }}</td>

                        <td class="whitespace-nowrap px-4 py-4 sm:px-6">
                            <div class="flex flex-wrap gap-2">
                            <a href=""
                                class="inline-flex items-center gap-2 rounded-md bg-zinc-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-zinc-700">
                                <i class="fas fa-eye mr-2"></i>
                                Ver
                            </a>
                            <a href=""
                                class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-emerald-700">
                                <i class="fas fa-pencil-alt mr-2"></i>
                                Editar
                            </a>
                            </div>


                        </td>
                    </tr>
                @endforeach

            </tbody>
        </table>
        </div>
    </div>
    <div class="py-4 flex justify-center">

    </div>

</x-layouts.app>
