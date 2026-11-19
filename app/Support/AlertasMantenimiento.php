<?php

namespace App\Support;

use App\Models\Vehiculo;
use Illuminate\Support\Collection;

class AlertasMantenimiento
{
    public static function evaluarKilometraje(float $actual, mixed $limite): ?array
    {
        if ($limite === null || (is_string($limite) && trim($limite) === '') || ! is_numeric($limite)) {
            return null;
        }

        $limite = (float) $limite;
        if (! is_finite($limite) || $limite < 0) {
            return null;
        }

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
            ->map(function (Vehiculo $vehiculo) {
                $mantenimiento = $vehiculo->ultimoMantenimiento;
                if (! $mantenimiento) {
                    return null;
                }

                $evaluacion = self::evaluarKilometraje(
                    (float) $vehiculo->{'kilometraje actual'},
                    $mantenimiento->getRawOriginal('proximo_mantenimiento_km')
                );

                if ($evaluacion === null) {
                    return null;
                }

                $vehiculo->setAttribute('condicion_mantenimiento', $evaluacion['condicion']);
                $vehiculo->setAttribute('kilometros_restantes', $evaluacion['kilometros_restantes']);

                return $vehiculo;
            })
            ->filter()
            ->sortBy(fn (Vehiculo $vehiculo) => $vehiculo->kilometros_restantes)
            ->values();
    }
}
