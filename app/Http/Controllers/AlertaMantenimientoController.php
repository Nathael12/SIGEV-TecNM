<?php

namespace App\Http\Controllers;

use App\Models\AlertaMantenimiento;

class AlertaMantenimientoController extends Controller
{
    public function atender(AlertaMantenimiento $alerta)
    {
        $alerta->update(['estado' => 'atendida', 'vista_en' => now()]);

        return redirect()->route('admin.index')->with('mensaje', 'Alerta marcada como atendida.');
    }
}
