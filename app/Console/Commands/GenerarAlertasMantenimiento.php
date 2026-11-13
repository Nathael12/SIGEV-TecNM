<?php

namespace App\Console\Commands;

use App\Services\GenerarAlertasMantenimiento as Generador;
use Illuminate\Console\Command;

class GenerarAlertasMantenimiento extends Command
{
    protected $signature = 'alertas:mantenimiento';

    protected $description = 'Genera alertas por fechas próximas o vencidas de mantenimiento';

    public function handle(Generador $generador): int
    {
        $total = $generador->ejecutar();
        $this->info("Alertas nuevas generadas: {$total}");

        return self::SUCCESS;
    }
}
