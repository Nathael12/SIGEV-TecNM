<x-layouts.app title="Reportes">

    {{-- Cuadrícula propia: no depende de que Tailwind compile clases nuevas --}}
    <style>
        .grid-reportes {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 20px;
        }

        .grid-reportes>div {
            min-width: 0;
        }

        @media (min-width: 640px) {
            .grid-reportes {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1024px) {
            .grid-reportes {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
    </style>

    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Reportes</flux:heading>
        <p style="font-size:14px;color:#94a3b8;margin-top:4px;">
            Consulta la información de la flotilla y expórtala a PDF o Excel.
        </p>
        <br>
        <flux:separator variant="subtle" />
    </div>

    <div class="grid-reportes">
        @foreach ($reportes as $reporte)
            <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800"
                style="padding:24px;display:flex;flex-direction:column;gap:12px;">

                <div style="display:flex;align-items:center;gap:12px;">
                    <span
                        style="display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;border-radius:10px;background-color:#dbeafe;color:#1d4ed8;font-size:18px;">
                        <i class="fas {{ $reporte['icono'] }}"></i>
                    </span>
                    <flux:heading size="lg" level="2">{{ $reporte['titulo'] }}</flux:heading>
                </div>

                <p style="font-size:14px;color:#94a3b8;flex:1;">{{ $reporte['descripcion'] }}</p>

                <div>
                    @if ($reporte['disponible'])
                        <a href="{{ $reporte['url'] }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded transition">
                            <i class="fas fa-chart-bar mr-2"></i> Ver reporte
                        </a>
                    @else
                        <span
                            class="inline-flex items-center px-4 py-2 bg-gray-500 text-white text-xs font-semibold rounded"
                            style="opacity:.6;cursor:not-allowed;">
                            <i class="fas fa-hourglass-half mr-2"></i> Próximamente
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.app>