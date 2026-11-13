<?php

namespace Database\Seeders;

use App\Models\ConfiguracionAlerta;
use Illuminate\Database\Seeder;

class ConfiguracionAlertasSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([15, 7, 3] as $dias) {
            ConfiguracionAlerta::firstOrCreate(['entidad' => 'mantenimiento', 'dias_anticipacion' => $dias], ['activo' => true]);
        }
    }
}
