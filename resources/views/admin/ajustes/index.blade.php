<x-layouts.app title="Ajustes del sistema">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Ajustes del sistema</flux:heading>
        <br>
        <flux:separator variant="subtle" />
    </div>

    {{-- Card --}}
    <div class="bg-white dark:bg-neutral-800 border-t border-gray-200 dark:border-gray-700 rounded-lg shadow-lg transition-all duration-300 hover:shadow-xl">
        
        <form action="">
            {{-- Body --}}
            <div class="p-6">
                <p>contenido</p>
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 dark:bg-neutral-700 border-t border-gray-200 dark:border-gray-700 rounded-b-lg p-6 text-left">
                <div class="flex space-x-3">
                    <a href="{{ url('/login') }}"
                       class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 focus:ring-offset-1 transition-all duration-200 inline-flex items-center">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </a>
                    <flux:button variant="primary" type="submit" class="cursor-pointer" color="blue">
                        <i class="fas fa-save"></i> Guardar
                    </flux:button>
                </div>
            </div>
        </form>

    </div>
    {{-- Card --}}
</x-layouts.app>