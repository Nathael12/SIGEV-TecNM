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
                'descripcion' => 'Sistema de Gestión Vehicular del Instituto Tecnológico Superior de Hopelchén, diseñado para facilitar la administración y el control con los vehículos.',
                'direccion' => 'Carretera Federal, Campeche-Hopelchen Km. 83, 24600 Hopelchén, Camp.',
                'telefono' => '981 125 3486',
                'email' => 'webmaster@hopelchen.tecnm.mx',
                'logo' => null,
                'web' => 'https://hopelchen.tecnm.mx',
            ]
        );
    }
}
