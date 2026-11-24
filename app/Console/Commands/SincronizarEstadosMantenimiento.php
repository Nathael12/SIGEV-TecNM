<?php

namespace App\Console\Commands;

use App\Models\Vehiculo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SincronizarEstadosMantenimiento extends Command
{
    protected $signature = 'vehiculos:sincronizar-estados-mantenimiento';

    protected $description = 'Recalcula el estado de los vehículos según sus mantenimientos abiertos';

    public function handle(): int
    {
        $actualizados = 0;

        Vehiculo::query()->orderBy('id_vehiculo')->chunkById(
            100,
            function ($vehiculos) use (&$actualizados): void {
                foreach ($vehiculos as $vehiculo) {
                    $actualizados += DB::transaction(function () use ($vehiculo): int {
                        $vehiculoBloqueado = Vehiculo::lockForUpdate()->find($vehiculo->getKey());

                        return $vehiculoBloqueado?->sincronizarEstadoMantenimiento() ? 1 : 0;
                    });
                }
            },
            'id_vehiculo',
            'id_vehiculo'
        );

        $this->info("Estados de vehículos corregidos: {$actualizados}");

        return self::SUCCESS;
    }
}
