<?php

namespace App\Http\Controllers;

use App\Models\AlertaMantenimiento;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class AlertaMantenimientoController extends Controller
{
    public function atender(AlertaMantenimiento $alerta)
    {
        Gate::authorize('atender alertas mantenimiento');

        $mantenimiento = $alerta->mantenimiento;
        if (! $mantenimiento || ! $mantenimiento->proximo_mantenimiento_fecha) {
            throw ValidationException::withMessages([
                'alerta' => 'Registra la fecha del próximo mantenimiento para poder atender esta alerta.',
            ]);
        }

        if ($alerta->estado !== 'atendida') {
            $mantenimiento->alertas()
                ->where('estado', 'activa')
                ->update(['estado' => 'atendida', 'vista_en' => now()]);
        }

        return redirect()->back()->with('mensaje', 'Alerta marcada como atendida.');
    }
}
