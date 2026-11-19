<?php

namespace Tests\Unit;

use App\Models\Mantenimiento;
use App\Support\AlertasMantenimiento;
use Tests\TestCase;

class AlertasMantenimientoKilometrajeTest extends TestCase
{
    public function test_no_calcula_alertas_si_el_kilometraje_programado_es_nulo_o_vacio(): void
    {
        $this->assertNull(AlertasMantenimiento::evaluarKilometraje(1000, null));

        foreach (['', '   '] as $valorVacio) {
            $mantenimiento = new Mantenimiento();
            $mantenimiento->setRawAttributes(['proximo_mantenimiento_km' => $valorVacio]);
            $mantenimiento->syncOriginal();

            $this->assertNull(AlertasMantenimiento::evaluarKilometraje(
                1000,
                $mantenimiento->getRawOriginal('proximo_mantenimiento_km')
            ));
        }
    }

    public function test_calcula_alertas_solo_con_un_limite_numerico_registrado(): void
    {
        config(['maintenance.kilometers_alert' => 500]);

        $this->assertNull(AlertasMantenimiento::evaluarKilometraje(1000, ''));
        $this->assertNull(AlertasMantenimiento::evaluarKilometraje(1000, '2000'));

        $mantenimiento = new Mantenimiento();
        $mantenimiento->setRawAttributes(['proximo_mantenimiento_km' => '10000']);
        $mantenimiento->syncOriginal();

        $proximo = AlertasMantenimiento::evaluarKilometraje(
            9500,
            $mantenimiento->getRawOriginal('proximo_mantenimiento_km')
        );
        $vencido = AlertasMantenimiento::evaluarKilometraje(10000, 10000);
        $superado = AlertasMantenimiento::evaluarKilometraje(10100, 10000);

        $this->assertSame(500.0, (float) $proximo['kilometros_restantes']);
        $this->assertSame('Vencido', $vencido['condicion']);
        $this->assertSame('Vencido', $superado['condicion']);
    }
}
