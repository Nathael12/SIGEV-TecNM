<?php

namespace App\Services;

use App\Models\AlertaMantenimiento;
use App\Models\Mantenimiento;
use Illuminate\Support\Carbon;

class GenerarAlertasMantenimiento
{
    public function ejecutar(): int
    {
        $hoy = Carbon::today();
        $umbralFecha = (int) config('maintenance.days_alert', 20);
        $generadas = 0;

        Mantenimiento::query()->where('estado_mantenimiento', 'pendiente')
            ->whereNotNull('proximo_mantenimiento_fecha')
            ->whereDate('proximo_mantenimiento_fecha', '<=', $hoy->copy()->addDays($umbralFecha))
            ->each(function (Mantenimiento $mantenimiento) use ($hoy, $umbralFecha, &$generadas) {
                $dias = $hoy->diffInDays($mantenimiento->proximo_mantenimiento_fecha, false);
                $umbral = $dias < 0 ? 0 : $umbralFecha;

                AlertaMantenimiento::where('mantenimiento_id', $mantenimiento->id_mantenimiento)
                    ->where('estado', 'activa')
                    ->where('dias_anticipacion', '!=', $umbral)
                    ->update(['estado' => 'reemplazada']);

                if ($dias < 0) {
                    AlertaMantenimiento::where('mantenimiento_id', $mantenimiento->id_mantenimiento)
                        ->where('estado', 'activa')->where('tipo', 'proxima')->update(['estado' => 'reemplazada']);
                }

                $alerta = AlertaMantenimiento::firstOrCreate(
                    ['mantenimiento_id' => $mantenimiento->id_mantenimiento, 'dias_anticipacion' => $umbral],
                    ['tipo' => $dias < 0 ? 'vencida' : 'proxima', 'fecha_mantenimiento' => $mantenimiento->proximo_mantenimiento_fecha, 'estado' => 'activa']
                );
                $generadas += $alerta->wasRecentlyCreated ? 1 : 0;
            });

        return $generadas;
    }
}
