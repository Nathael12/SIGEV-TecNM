<?php

namespace App\Services;

use App\Models\AlertaMantenimiento;
use App\Models\ConfiguracionAlerta;
use App\Models\Mantenimiento;
use Illuminate\Support\Carbon;

class GenerarAlertasMantenimiento
{
    public function ejecutar(): int
    {
        $hoy = Carbon::today();
        $configuraciones = ConfiguracionAlerta::query()->where('entidad', 'mantenimiento');
        $umbrales = (clone $configuraciones)->where('activo', true)
            ->pluck('dias_anticipacion')->map(fn ($dias) => (int) $dias)->unique()->sort()->values();

        // Conserva el umbral de configuración existente cuando aún no hay reglas en la tabla.
        if ($umbrales->isEmpty() && ! (clone $configuraciones)->exists()) {
            $umbrales = collect([(int) config('maintenance.days_alert', 20)]);
        }

        if ($umbrales->isEmpty()) {
            AlertaMantenimiento::where('estado', 'activa')->update(['estado' => 'reemplazada']);

            return 0;
        }

        $maximoDias = $umbrales->max();
        $generadas = 0;

        Mantenimiento::query()->where('estado_mantenimiento', 'pendiente')
            ->whereNotNull('proximo_mantenimiento_fecha')
            ->whereDate('proximo_mantenimiento_fecha', '<=', $hoy->copy()->addDays($maximoDias))
            ->each(function (Mantenimiento $mantenimiento) use ($hoy, $umbrales, &$generadas) {
                $dias = $hoy->diffInDays($mantenimiento->proximo_mantenimiento_fecha, false);
                $umbral = $dias < 0 ? 0 : $umbrales->first(fn ($umbral) => $umbral >= $dias);

                if ($umbral === null) {
                    return;
                }

                AlertaMantenimiento::where('mantenimiento_id', $mantenimiento->id_mantenimiento)
                    ->where('estado', 'activa')
                    ->where('dias_anticipacion', '!=', $umbral)
                    ->update(['estado' => 'reemplazada']);

                $tipo = $dias < 0 ? 'vencida' : 'proxima';
                $alerta = AlertaMantenimiento::firstOrCreate(
                    [
                        'mantenimiento_id' => $mantenimiento->id_mantenimiento,
                        'dias_anticipacion' => $umbral,
                    ],
                    [
                        'tipo' => $tipo,
                        'fecha_mantenimiento' => $mantenimiento->proximo_mantenimiento_fecha,
                        'estado' => 'activa',
                    ]
                );
                $alertaNueva = $alerta->wasRecentlyCreated;
                $fechaProgramada = $mantenimiento->proximo_mantenimiento_fecha->toDateString();
                $fechaCambio = ! $alertaNueva && $alerta->fecha_mantenimiento?->toDateString() !== $fechaProgramada;
                $tipoCambio = ! $alertaNueva && $alerta->tipo !== $tipo;
                $reactivada = $alerta->estado === 'reemplazada';

                // Una alerta atendida sigue cerrada mientras la programación no cambie.
                if ($alerta->estado === 'atendida' && ! $fechaCambio && ! $tipoCambio) {
                    return;
                }

                if ($alertaNueva) {
                    $generadas++;
                } elseif ($fechaCambio || $tipoCambio || $reactivada) {
                    $alerta->tipo = $tipo;
                    $alerta->fecha_mantenimiento = $mantenimiento->proximo_mantenimiento_fecha;
                    $alerta->estado = 'activa';
                    $alerta->vista_en = null;
                    $alerta->save();
                    $generadas++;
                }
            });

        // Cierra avisos que ya no corresponden a una programación pendiente vigente.
        AlertaMantenimiento::query()->where('estado', 'activa')
            ->whereDoesntHave('mantenimiento', function ($consulta) use ($hoy, $maximoDias) {
                $consulta->where('estado_mantenimiento', 'pendiente')
                    ->whereNotNull('proximo_mantenimiento_fecha')
                    ->whereDate('proximo_mantenimiento_fecha', '<=', $hoy->copy()->addDays($maximoDias));
            })
            ->update(['estado' => 'reemplazada']);

        return $generadas;
    }
}
