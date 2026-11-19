<?php

namespace App\Support;

use App\Models\Vehiculo;
use Illuminate\Support\Collection;

class AlertasMantenimiento
{
    public static function evaluarKilometraje(float $actual, int $limite): ?array
    {
        $restantes = $limite - $actual;

        if ($restantes > (int) config('maintenance.kilometers_alert', 500)) {
            return null;
        }

        return [
            'condicion' => $restantes <= 0 ? 'Vencido' : 'Próximo',
            'kilometros_restantes' => $restantes,
        ];
    }

    public static function obtener(): Collection
    {
        return Vehiculo::with('ultimoMantenimiento')
            ->get()
            ->filter(function (Vehiculo $vehiculo) {
                $mantenimiento = $vehiculo->ultimoMantenimiento;

                return $mantenimiento && $mantenimiento->proximo_mantenimiento_km !== null && self::evaluarKilometraje(
                    (float) $vehiculo->{'kilometraje actual'},
                    (int) $mantenimiento->proximo_mantenimiento_km
                ) !== null;
            })
            ->map(function (Vehiculo $vehiculo) {
                $mantenimiento = $vehiculo->ultimoMantenimiento;
                if ($mantenimiento->proximo_mantenimiento_km === null) {
                    return $vehiculo;
                }

                $evaluacion = self::evaluarKilometraje(
                    (float) $vehiculo->{'kilometraje actual'},
                    (int) $mantenimiento->proximo_mantenimiento_km
                );
                $vehiculo->setAttribute('condicion_mantenimiento', $evaluacion['condicion']);
                $vehiculo->setAttribute('kilometros_restantes', $evaluacion['kilometros_restantes']);

                return $vehiculo;
            })
            ->sortBy(fn (Vehiculo $vehiculo) => $vehiculo->kilometros_restantes)
            ->values();
    }
}
