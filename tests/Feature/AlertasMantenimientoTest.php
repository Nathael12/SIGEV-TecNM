<?php

namespace Tests\Feature;

use App\Models\ConfiguracionAlerta;
use App\Models\Mantenimiento;
use App\Models\Vehiculo;
use App\Services\GenerarAlertasMantenimiento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AlertasMantenimientoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-13 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function mantenimiento(string $fecha, string $estado = 'pendiente'): Mantenimiento
    {
        $vehiculo = Vehiculo::create([
            'numero_economico' => 'VEH-'.uniqid(), 'placas' => 'ABC-'.uniqid(),
            'marca' => 'Marca', 'modelo' => 'Modelo', 'anio' => 2024, 'tipo vehiculo' => 'Sedan',
            'color' => 'Blanco', 'tipo combustible' => 'Gasolina Magna', 'kilometraje actual' => 1000,
            'fecha_adquisicion' => '2024-01-01', 'estado' => 'Disponible',
        ]);

        return Mantenimiento::create([
            'fecha_mantenimiento' => '2026-10-01', 'kilometraje_mantenimiento' => 1000,
            'categoria_mantenimiento' => 'Preventivo', 'descripcion_mantenimiento' => 'Servicio',
            'monto_mantenimiento' => 100, 'proximo_mantenimiento_fecha' => $fecha,
            'proximo_mantenimiento_km' => 2000, 'vehiculo_id' => $vehiculo->id_vehiculo,
            'estado_mantenimiento' => $estado,
        ]);
    }

    public function test_crea_alerta_proxima_dentro_del_umbral(): void
    {
        ConfiguracionAlerta::create(['entidad' => 'mantenimiento', 'dias_anticipacion' => 15, 'activo' => true]);
        $mantenimiento = $this->mantenimiento('2026-11-20');

        app(GenerarAlertasMantenimiento::class)->ejecutar();

        $this->assertDatabaseHas('alertas_mantenimiento', ['mantenimiento_id' => $mantenimiento->id_mantenimiento, 'tipo' => 'proxima', 'dias_anticipacion' => 15]);
    }

    public function test_crea_alerta_vencida(): void
    {
        ConfiguracionAlerta::create(['entidad' => 'mantenimiento', 'dias_anticipacion' => 15, 'activo' => true]);
        $mantenimiento = $this->mantenimiento('2026-11-10');

        app(GenerarAlertasMantenimiento::class)->ejecutar();

        $this->assertDatabaseHas('alertas_mantenimiento', ['mantenimiento_id' => $mantenimiento->id_mantenimiento, 'tipo' => 'vencida', 'dias_anticipacion' => 0]);
    }

    public function test_ignora_mantenimientos_realizados_o_cancelados(): void
    {
        ConfiguracionAlerta::create(['entidad' => 'mantenimiento', 'dias_anticipacion' => 15, 'activo' => true]);
        $realizado = $this->mantenimiento('2026-11-10', 'realizado');
        $cancelado = $this->mantenimiento('2026-11-10', 'cancelado');

        app(GenerarAlertasMantenimiento::class)->ejecutar();

        $this->assertDatabaseMissing('alertas_mantenimiento', ['mantenimiento_id' => $realizado->id_mantenimiento]);
        $this->assertDatabaseMissing('alertas_mantenimiento', ['mantenimiento_id' => $cancelado->id_mantenimiento]);
    }

    public function test_no_duplica_alertas_en_ejecuciones_repetidas(): void
    {
        ConfiguracionAlerta::create(['entidad' => 'mantenimiento', 'dias_anticipacion' => 15, 'activo' => true]);
        $this->mantenimiento('2026-11-20');
        $generador = app(GenerarAlertasMantenimiento::class);

        $this->assertSame(1, $generador->ejecutar());
        $this->assertSame(0, $generador->ejecutar());
        $this->assertDatabaseCount('alertas_mantenimiento', 1);
    }

    public function test_cambiar_umbral_cambia_el_momento_de_generacion(): void
    {
        ConfiguracionAlerta::create(['entidad' => 'mantenimiento', 'dias_anticipacion' => 3, 'activo' => true]);
        $mantenimiento = $this->mantenimiento('2026-11-20');
        $generador = app(GenerarAlertasMantenimiento::class);
        $generador->ejecutar();
        $this->assertDatabaseCount('alertas_mantenimiento', 0);

        ConfiguracionAlerta::query()->update(['dias_anticipacion' => 15]);
        $generador->ejecutar();

        $this->assertDatabaseHas('alertas_mantenimiento', ['mantenimiento_id' => $mantenimiento->id_mantenimiento, 'dias_anticipacion' => 15]);
    }

    public function test_actualiza_la_fecha_de_una_alerta_existente_si_cambia_la_programacion(): void
    {
        ConfiguracionAlerta::create(['entidad' => 'mantenimiento', 'dias_anticipacion' => 15, 'activo' => true]);
        $mantenimiento = $this->mantenimiento('2026-11-20');
        $generador = app(GenerarAlertasMantenimiento::class);
        $generador->ejecutar();

        $mantenimiento->update(['proximo_mantenimiento_fecha' => '2026-11-21']);
        $generador->ejecutar();

        $this->assertDatabaseCount('alertas_mantenimiento', 1);
        $this->assertDatabaseHas('alertas_mantenimiento', [
            'mantenimiento_id' => $mantenimiento->id_mantenimiento,
            'fecha_mantenimiento' => '2026-11-21',
            'estado' => 'activa',
        ]);
    }

    public function test_reemplaza_alertas_activas_si_la_fecha_sale_del_periodo_de_anticipacion(): void
    {
        ConfiguracionAlerta::create(['entidad' => 'mantenimiento', 'dias_anticipacion' => 15, 'activo' => true]);
        $mantenimiento = $this->mantenimiento('2026-11-20');
        $generador = app(GenerarAlertasMantenimiento::class);
        $generador->ejecutar();

        $mantenimiento->update(['proximo_mantenimiento_fecha' => '2026-11-30']);
        $generador->ejecutar();

        $this->assertDatabaseHas('alertas_mantenimiento', [
            'mantenimiento_id' => $mantenimiento->id_mantenimiento,
            'estado' => 'reemplazada',
        ]);
    }

    public function test_reactiva_la_alerta_si_cambia_una_programacion_que_ya_se_atendio(): void
    {
        ConfiguracionAlerta::create(['entidad' => 'mantenimiento', 'dias_anticipacion' => 15, 'activo' => true]);
        $mantenimiento = $this->mantenimiento('2026-11-20');
        $generador = app(GenerarAlertasMantenimiento::class);
        $generador->ejecutar();
        $alerta = $mantenimiento->alertas()->firstOrFail();
        $alerta->update(['estado' => 'atendida', 'vista_en' => now()]);

        $mantenimiento->update(['proximo_mantenimiento_fecha' => '2026-11-21']);
        $generador->ejecutar();

        $this->assertDatabaseCount('alertas_mantenimiento', 1);
        $this->assertDatabaseHas('alertas_mantenimiento', [
            'mantenimiento_id' => $mantenimiento->id_mantenimiento,
            'fecha_mantenimiento' => '2026-11-21',
            'estado' => 'activa',
            'vista_en' => null,
        ]);
    }

    public function test_usa_el_umbral_activo_mas_cercano_y_mantiene_una_sola_alerta_activa(): void
    {
        foreach ([15, 7, 3] as $dias) {
            ConfiguracionAlerta::create(['entidad' => 'mantenimiento', 'dias_anticipacion' => $dias, 'activo' => true]);
        }
        $mantenimiento = $this->mantenimiento('2026-11-20');

        app(GenerarAlertasMantenimiento::class)->ejecutar();

        $this->assertDatabaseCount('alertas_mantenimiento', 1);
        $this->assertDatabaseHas('alertas_mantenimiento', [
            'mantenimiento_id' => $mantenimiento->id_mantenimiento,
            'dias_anticipacion' => 7,
            'estado' => 'activa',
        ]);
    }
}
