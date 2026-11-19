<?php

namespace Database\Seeders;

use App\Models\Ajuste;
use Illuminate\Database\Seeder;

class AjusteSeeder extends Seeder
{
    public function run(): void
    {
        Ajuste::firstOrCreate(
            [],
            [
                'nombre' => 'SIGEV-TecNM',
                'descripcion' => 'Sistema de Gestión Vehicular del Tecnológico Nacional de México, diseñado para facilitar la administración, el control y el seguimiento de la información relacionada con los vehículos institucionales.',
                'direccion' => 'Carretera Federal, Campeche-Hopelchen Km. 83, 24600 Hopelchén, Camp.',
                'telefono' => '981 125 3486',
                'email' => 'webmaster@hopelchen.tecnm.mx',
                'logo' => null,
                'web' => 'https://hopelchen.tecnm.mx',
            ]
        );
    }
}
