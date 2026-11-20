<?php

namespace App\Services;

use App\Models\AlertaMantenimiento;
use App\Models\ConfiguracionAlerta;
use App\Models\Mantenimiento;
use App\Models\Vehiculo;
use App\Support\AlertasMantenimiento as EvaluadorKilometraje;
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
            AlertaMantenimiento::where('condicion', 'fecha')->where('estado', 'activa')->update(['estado' => 'reemplazada']);

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
                    ->where('condicion', 'fecha')
                    ->where('estado', 'activa')
                    ->where('dias_anticipacion', '!=', $umbral)
                    ->update(['estado' => 'reemplazada']);

                $tipo = $dias < 0 ? 'vencida' : 'proxima';
                $alerta = AlertaMantenimiento::firstOrCreate(
                    [
                        'condicion' => 'fecha',
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
                if ($alerta->estado === 'atendida') {
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
        AlertaMantenimiento::query()->where('condicion', 'fecha')->where('estado', 'activa')
            ->whereDoesntHave('mantenimiento', function ($consulta) use ($hoy, $maximoDias) {
                $consulta->where('estado_mantenimiento', 'pendiente')
                    ->whereNotNull('proximo_mantenimiento_fecha')
                    ->whereDate('proximo_mantenimiento_fecha', '<=', $hoy->copy()->addDays($maximoDias));
            })
            ->update(['estado' => 'reemplazada']);

        return $generadas;
    }

    /** Persiste la condición de kilometraje usando el mismo umbral que la alerta original. */
    public function sincronizarKilometraje(): void
    {
        Vehiculo::with('ultimoMantenimiento')->get()->each(function (Vehiculo $vehiculo): void {
            $mantenimiento = $vehiculo->ultimoMantenimiento;
            if (! $mantenimiento) {
                return;
            }

            $evaluacion = EvaluadorKilometraje::evaluarKilometraje(
                (float) $vehiculo->{'kilometraje actual'},
                $mantenimiento->getRawOriginal('proximo_mantenimiento_km')
            );
            $alerta = AlertaMantenimiento::firstOrNew([
                'mantenimiento_id' => $mantenimiento->id_mantenimiento,
                'condicion' => 'kilometraje',
                'dias_anticipacion' => 0,
            ]);

            if ($evaluacion === null) {
                if ($alerta->exists && $alerta->estado === 'activa') {
                    $alerta->update(['estado' => 'reemplazada']);
                }
                return;
            }

            // Una notificación atendida no se reactiva por la misma programación.
            if ($alerta->exists && $alerta->estado === 'atendida') {
                return;
            }

            $alerta->fill([
                'tipo' => $evaluacion['condicion'] === 'Vencido' ? 'vencida' : 'proxima',
                'fecha_mantenimiento' => $mantenimiento->proximo_mantenimiento_fecha ?? $mantenimiento->fecha_mantenimiento,
                'estado' => 'activa',
            ])->save();
        });
    }
}
