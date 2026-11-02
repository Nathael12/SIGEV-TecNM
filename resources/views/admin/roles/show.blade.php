<x-layouts.app title="Crear rol">
    <div class="relative mb-6 w-full">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ url('/admin') }}">Inicio</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ url('/admin/roles') }}">Listado de roles</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Datos de rol: {{ $rol->name }} </flux:breadcrumbs.item>
        </flux:breadcrumbs>
        <flux:heading size="xl" level="1" class="mt-4">Crear un nuevo rol</flux:heading>
        <flux:separator variant="subtle" class="mt-4" />
    </div>

    <div class="mx-auto mt-5 w-full max-w-6xl overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
       
            @csrf
            <div class="p-6 sm:p-8">
                <div class="max-w-2xl">
                    <flux:label>Nombre del rol </flux:label>
                    <p><i class="fas fa-shield-alt"></i>{{ $rol->name }}</p>
                </div>
            </div>

             <div class="p-6 sm:p-8">
                <div class="max-w-2xl">
                    <flux:label>Fecha y hora de registro </flux:label>
                    <p><i class="fas fa-clock"></i>{{ $rol->created_at }}</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 border-t border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <a href="{{ url('/admin/roles') }}"
                    class="inline-flex items-center rounded-lg border border-zinc-300 bg-white px-5 py-3.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-300 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-700">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver al listado
                </a>
               
            </div>
        
    </div>
</x-layouts.app>
