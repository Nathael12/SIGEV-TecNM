<?php

namespace App\Console\Commands;

use App\Models\Mantenimiento;
use App\Models\Vehiculo;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LimpiarMantenimientosDuplicados extends Command
{
    protected $signature = 'mantenimientos:limpiar-duplicados {--delete : Elimina los duplicados listados} {--force : Omite la confirmación interactiva}';

    protected $description = 'Detecta y, bajo confirmación, elimina duplicados recientes de mantenimientos';

    public function handle(): int
    {
        $duplicados = $this->detectarDuplicados();

        if ($duplicados === []) {
            $this->info('No se encontraron duplicados de mantenimiento en la ventana de 30 segundos.');

            return self::SUCCESS;
        }

        $this->warn('Se conservará el primer registro de cada grupo y se eliminarán estos duplicados:');
        $this->table(
            ['Conservar ID', 'Eliminar ID', 'Vehículo', 'Fecha', 'Kilometraje', 'Categoría', 'Monto', 'Creado'],
            array_map(fn (array $fila) => [
                $fila['conservar_id'],
                $fila['eliminar_id'],
                $fila['vehiculo'],
                $fila['fecha'],
                $fila['kilometraje'],
                $fila['categoria'],
                $fila['monto'],
                $fila['creado'],
            ], $duplicados)
        );

        if (! $this->option('delete')) {
            $this->comment('Vista previa solamente. Ejecuta con --delete para borrar los IDs listados.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('¿Eliminar únicamente los registros listados y sincronizar sus vehículos?')) {
            $this->comment('No se eliminó ningún registro.');

            return self::SUCCESS;
        }

        $idsEliminar = array_column($duplicados, 'eliminar_id');
        $vehiculosIds = collect($duplicados)->pluck('vehiculo_id')->unique()->sort()->values();

        DB::transaction(function () use ($idsEliminar, $vehiculosIds): void {
            $vehiculos = Vehiculo::whereIn('id_vehiculo', $vehiculosIds)
                ->orderBy('id_vehiculo')
                ->lockForUpdate()
                ->get();

            Mantenimiento::whereIn('id_mantenimiento', $idsEliminar)->delete();

            foreach ($vehiculos as $vehiculo) {
                $vehiculo->sincronizarEstadoMantenimiento();
            }
        });

        $this->info('Duplicados eliminados: '.count($idsEliminar).'. Estados de vehículos sincronizados.');

        return self::SUCCESS;
    }

    /** @return array<int, array<string, mixed>> */
    private function detectarDuplicados(): array
    {
        $registros = Mantenimiento::with('vehiculo')
            ->orderBy('vehiculo_id')
            ->orderBy('fecha_mantenimiento')
            ->orderBy('kilometraje_mantenimiento')
            ->orderBy('categoria_mantenimiento')
            ->orderBy('monto_mantenimiento')
            ->orderBy('created_at')
            ->orderBy('id_mantenimiento')
            ->get();

        $grupos = $registros->groupBy(fn (Mantenimiento $mantenimiento) => implode('|', [
            $mantenimiento->vehiculo_id,
            $mantenimiento->fecha_mantenimiento->toDateString(),
            $mantenimiento->kilometraje_mantenimiento,
            mb_strtolower(trim($mantenimiento->categoria_mantenimiento)),
            number_format((float) $mantenimiento->monto_mantenimiento, 2, '.', ''),
        ]));

        $duplicados = [];

        foreach ($grupos as $grupo) {
            $cluster = collect();
            $registroAnterior = null;

            foreach ($grupo as $registro) {
                if ($registroAnterior && Carbon::parse($registroAnterior->created_at)->diffInSeconds($registro->created_at) > 30) {
                    $this->agregarCopias($duplicados, $cluster);
                    $cluster = collect();
                }

                $cluster->push($registro);
                $registroAnterior = $registro;
            }

            $this->agregarCopias($duplicados, $cluster);
        }

        return $duplicados;
    }

    /** @param array<int, array<string, mixed>> $duplicados */
    private function agregarCopias(array &$duplicados, $cluster): void
    {
        if ($cluster->count() < 2) {
            return;
        }

        $conservar = $cluster->first();
        foreach ($cluster->skip(1) as $copia) {
            $duplicados[] = [
                'conservar_id' => $conservar->id_mantenimiento,
                'eliminar_id' => $copia->id_mantenimiento,
                'vehiculo_id' => $conservar->vehiculo_id,
                'vehiculo' => $conservar->vehiculo?->numero_economico ?? 'ID '.$conservar->vehiculo_id,
                'fecha' => $conservar->fecha_mantenimiento->toDateString(),
                'kilometraje' => $conservar->kilometraje_mantenimiento,
                'categoria' => $conservar->categoria_mantenimiento,
                'monto' => number_format((float) $conservar->monto_mantenimiento, 2, '.', ''),
                'creado' => $copia->created_at?->toDateTimeString(),
            ];
        }
    }
}
