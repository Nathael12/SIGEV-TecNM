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
        $umbrales = ConfiguracionAlerta::query()
            ->where('entidad', 'mantenimiento')->where('activo', true)
            ->pluck('dias_anticipacion')->all();
        $generadas = 0;

        Mantenimiento::query()->where('estado_mantenimiento', 'pendiente')
            ->whereNotNull('proximo_mantenimiento_fecha')
            ->whereDate('proximo_mantenimiento_fecha', '<=', $hoy->copy()->addDays(max($umbrales ?: [0])))
            ->each(function (Mantenimiento $mantenimiento) use ($hoy, $umbrales, &$generadas) {
                $dias = $hoy->diffInDays($mantenimiento->proximo_mantenimiento_fecha, false);
                $umbral = $dias < 0 ? 0 : collect($umbrales)->filter(fn ($valor) => $dias <= $valor)->min();
                if ($umbral === null) {
                    return;
                }

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
