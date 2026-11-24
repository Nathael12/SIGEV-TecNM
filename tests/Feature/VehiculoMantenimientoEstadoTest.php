<?php

namespace Tests\Feature;

use App\Models\Mantenimiento;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehiculoMantenimientoEstadoTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_mantenimiento_abierto_pone_al_vehiculo_en_mantenimiento(): void
    {
        $vehiculo = $this->crearVehiculo();
        $this->crearMantenimiento($vehiculo);

        $this->assertTrue($vehiculo->sincronizarEstadoMantenimiento());
        $this->assertDatabaseHas('vehiculos', [
            'id_vehiculo' => $vehiculo->id_vehiculo,
            'estado' => 'En mantenimiento',
        ]);
    }

    public function test_finalizar_uno_de_varios_mantenimientos_no_libera_el_vehiculo(): void
    {
        $vehiculo = $this->crearVehiculo();
        $primero = $this->crearMantenimiento($vehiculo);
        $segundo = $this->crearMantenimiento($vehiculo);

        $vehiculo->sincronizarEstadoMantenimiento();
        $primero->update(['estado_mantenimiento' => 'realizado']);
        $vehiculo->refresh()->sincronizarEstadoMantenimiento();
        $this->assertDatabaseHas('vehiculos', [
            'id_vehiculo' => $vehiculo->id_vehiculo,
            'estado' => 'En mantenimiento',
        ]);

        $segundo->update(['estado_mantenimiento' => 'realizado']);
        $vehiculo->refresh()->sincronizarEstadoMantenimiento();
        $this->assertDatabaseHas('vehiculos', [
            'id_vehiculo' => $vehiculo->id_vehiculo,
            'estado' => 'Disponible',
        ]);
    }

    public function test_no_sobrescribe_estados_ajenos_al_mantenimiento(): void
    {
        foreach (['Asignado', 'Baja', 'Fuera de servicio'] as $estado) {
            $vehiculo = $this->crearVehiculo($estado);
            $this->crearMantenimiento($vehiculo);

            $this->assertFalse($vehiculo->sincronizarEstadoMantenimiento());
            $this->assertDatabaseHas('vehiculos', [
                'id_vehiculo' => $vehiculo->id_vehiculo,
                'estado' => $estado,
            ]);
        }
    }

    private function crearVehiculo(string $estado = 'Disponible'): Vehiculo
    {
        $id = uniqid();

        return Vehiculo::create([
            'numero_economico' => 'VEH-'.$id,
            'placas' => 'ABC-'.$id,
            'marca' => 'Marca',
            'modelo' => 'Modelo',
            'anio' => 2024,
            'tipo vehiculo' => 'Sedan',
            'color' => 'Blanco',
            'tipo combustible' => 'Gasolina Magna',
            'kilometraje actual' => 1000,
            'fecha_adquisicion' => '2024-01-01',
            'estado' => $estado,
        ]);
    }

    private function crearMantenimiento(Vehiculo $vehiculo): Mantenimiento
    {
        return Mantenimiento::create([
            'fecha_mantenimiento' => '2026-11-01',
            'kilometraje_mantenimiento' => 1000,
            'categoria_mantenimiento' => 'Preventivo',
            'descripcion_mantenimiento' => 'Servicio en proceso',
            'monto_mantenimiento' => 100,
            'proximo_mantenimiento_fecha' => '2027-11-01',
            'proximo_mantenimiento_km' => 2000,
            'vehiculo_id' => $vehiculo->id_vehiculo,
            'estado_mantenimiento' => 'pendiente',
        ]);
    }
}
