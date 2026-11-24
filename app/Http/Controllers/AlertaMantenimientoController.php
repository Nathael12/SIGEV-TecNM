<?php

namespace App\Http\Controllers;

use App\Models\AlertaMantenimiento;
use App\Models\Mantenimiento;
use App\Models\Vehiculo;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
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

        $estadoVehiculoFinal = null;
        $hayOtroMantenimientoAbierto = false;

        try {
            DB::transaction(function () use ($alerta, $mantenimiento, &$estadoVehiculoFinal, &$hayOtroMantenimientoAbierto) {
                $mantenimientoActual = Mantenimiento::lockForUpdate()->findOrFail($mantenimiento->getKey());
                $vehiculo = Vehiculo::lockForUpdate()->findOrFail($mantenimientoActual->vehiculo_id);

                $mantenimientoActual->update(['estado_mantenimiento' => 'realizado']);
                $mantenimientoActual->alertas()
                    ->where('estado', 'activa')
                    ->update(['estado' => 'atendida', 'vista_en' => now()]);
                $alertaActual = AlertaMantenimiento::lockForUpdate()->findOrFail($alerta->getKey());
                $alertaActual->update(['estado' => 'atendida', 'vista_en' => now()]);

                $vehiculo->sincronizarEstadoMantenimiento();
                $estadoVehiculoFinal = $vehiculo->estado;
                $hayOtroMantenimientoAbierto = $vehiculo->mantenimientos()
                    ->whereIn('estado_mantenimiento', ['pendiente', 'en_proceso'])
                    ->exists();
            });
        } catch (QueryException $exception) {
            report($exception);

            return redirect()->back()->with('error',
                'No se pudo atender la alerta: '.$exception->getMessage());
        }

        if ($hayOtroMantenimientoAbierto) {
            return redirect()->back()
                ->with('mensaje', 'Mantenimiento finalizado. El vehículo continúa En mantenimiento porque tiene otro servicio abierto.')
                ->with('tipo_mensaje', 'info');
        }

        if ($estadoVehiculoFinal !== 'Disponible') {
            return redirect()->back()
                ->with('mensaje', 'Mantenimiento finalizado. El vehículo conserva el estado '.$estadoVehiculoFinal.' por otra condición.')
                ->with('tipo_mensaje', 'info');
        }

        return redirect()->back()->with('mensaje', 'Alerta atendida y mantenimiento finalizado. El vehículo quedó Disponible.');
    }

    public function destroy(AlertaMantenimiento $alerta)
    {
        Gate::authorize('atender alertas mantenimiento');

        try {
            DB::transaction(function () use ($alerta) {
                $alertaActual = AlertaMantenimiento::lockForUpdate()->findOrFail($alerta->getKey());
                $mantenimiento = Mantenimiento::lockForUpdate()->find($alertaActual->mantenimiento_id);

                if ($mantenimiento) {
                    $vehiculo = Vehiculo::lockForUpdate()->find($mantenimiento->vehiculo_id);
                    $alertaActual->delete();
                    $vehiculo?->sincronizarEstadoMantenimiento();

                    return;
                }

                $alertaActual->delete();
            });
        } catch (QueryException $exception) {
            report($exception);

            return redirect()->back()->with('error', 'No se pudo eliminar la alerta: '.$exception->getMessage());
        }

        return redirect()->back()->with('mensaje', 'Alerta eliminada. El mantenimiento no fue modificado.');
    }

}
