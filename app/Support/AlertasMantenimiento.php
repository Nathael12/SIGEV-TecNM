<?php

namespace App\Support;

use App\Models\Vehiculo;
use Illuminate\Support\Collection;

class AlertasMantenimiento
{
    public static function obtener(): Collection
    {
        $kilometrosAlerta = (int) config('maintenance.kilometers_alert', 500);

        return Vehiculo::with('ultimoMantenimiento')
            ->get()
            ->filter(function (Vehiculo $vehiculo) use ($kilometrosAlerta) {
                $mantenimiento = $vehiculo->ultimoMantenimiento;

                return $mantenimiento && $vehiculo->kilometraje >= $mantenimiento->proximo_mantenimiento_km - $kilometrosAlerta;
            })
            ->map(function (Vehiculo $vehiculo) {
                $mantenimiento = $vehiculo->ultimoMantenimiento;
                $actual = (float) $vehiculo->kilometraje;
                $limite = (int) $mantenimiento->proximo_mantenimiento_km;
                $vehiculo->setAttribute('condicion_mantenimiento', $actual > $limite ? 'Vencido' : ($actual >= $limite ? 'Requiere mantenimiento' : 'Próximo'));
                $vehiculo->setAttribute('kilometros_restantes', $limite - $actual);

                return $vehiculo;
            })
            ->sortBy(fn (Vehiculo $vehiculo) => $vehiculo->ultimoMantenimiento->proximo_mantenimiento_km - $vehiculo->kilometraje)
            ->values();
    }
}
