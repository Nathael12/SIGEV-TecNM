<x-layouts.app title="Crear rol">
    <div class="relative mb-6 w-full">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ url('/admin') }}">Inicio</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ url('/admin/roles') }}">Listado de roles</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Crear rol</flux:breadcrumbs.item>
        </flux:breadcrumbs>
        <flux:heading size="xl" level="1" class="mt-4">Crear un nuevo rol</flux:heading>
        <flux:separator variant="subtle" class="mt-4" />
    </div>

    <div class="mx-auto mt-5 w-full max-w-6xl overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <form action="{{ url('/admin/roles/create') }}" method="POST">
            @csrf
            <div class="p-6 sm:p-8">
                <div class="max-w-2xl">
                    <flux:label>Nombre del rol <span class="text-red-500" title="Campo obligatorio">(*)</span></flux:label>
                    <flux:input name="name" icon="shield-check" placeholder="Ej. Administrador" required />
                    <flux:error name="name" />
                </div>
            </div>

            <div class="flex flex-wrap gap-3 border-t border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <a href="{{ url('/admin/roles') }}"
                    class="inline-flex items-center rounded-lg border border-zinc-300 bg-white px-5 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-300 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-700">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </a>
                <flux:button variant="primary" type="submit" class="cursor-pointer px-5" color="blue">
                    <i class="fas fa-save mr-2"></i> Guardar
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts.app>
