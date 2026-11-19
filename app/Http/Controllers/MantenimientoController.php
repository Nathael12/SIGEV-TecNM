<?php

namespace App\Http\Controllers;

use App\Models\AlertaMantenimiento;
use App\Models\Mantenimiento;
use App\Models\Vehiculo;
use App\Support\AlertasMantenimiento as ConsultaAlertasMantenimiento;
use App\Services\GenerarAlertasMantenimiento;
use Illuminate\Http\Request;

class MantenimientoController extends Controller
{
    public function index(Request $request, GenerarAlertasMantenimiento $generadorAlertas)
    {
        $generadorAlertas->ejecutar();
        $consulta = Mantenimiento::with([
            'vehiculo.ultimoMantenimiento',
            'alertas' => fn ($consulta) => $consulta->orderByDesc('id'),
        ])->orderByDesc('fecha_mantenimiento')->orderByDesc('id_mantenimiento');

        $mantenimientos = $consulta->get();

        return view('admin.mantenimientos.index', [
            'mantenimientos' => $mantenimientos,
            'alertasMantenimiento' => ConsultaAlertasMantenimiento::obtener(),
            'alertasFecha' => AlertaMantenimiento::with('mantenimiento.vehiculo')
                ->where('estado', 'activa')
                ->orderByRaw("CASE WHEN tipo = 'vencida' THEN 0 ELSE 1 END")
                ->orderBy('fecha_mantenimiento')
                ->get(),
        ]);
    }

    public function create()
    {
        $vehiculos = Vehiculo::with('ultimaAsignacion')->orderBy('numero_economico')->get();
        $kilometrajes = $vehiculos->mapWithKeys(
            fn (Vehiculo $vehiculo) => [$vehiculo->getKey() => $vehiculo->kilometraje_vigente]
        );

        return view('admin.mantenimientos.create', compact('vehiculos', 'kilometrajes'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas(), $this->mensajes());

        $vehiculo = Vehiculo::with('ultimaAsignacion')->findOrFail($datos['vehiculo_id']);
        $kilometraje = $vehiculo->kilometraje_vigente;

        if ($kilometraje === null) {
            return back()->withInput()->withErrors([
                'vehiculo_id' => 'Kilometraje no disponible para el vehículo seleccionado.',
            ]);
        }

        $datos['kilometraje_mantenimiento'] = $kilometraje;

        Mantenimiento::create($datos);

        return redirect()->route('admin.mantenimientos.index')->with('mensaje', 'Mantenimiento registrado correctamente');
    }

    public function edit(Mantenimiento $mantenimiento)
    {
        $vehiculos = Vehiculo::with('ultimaAsignacion')->orderBy('numero_economico')->get();
        $kilometrajes = $vehiculos->mapWithKeys(
            fn (Vehiculo $vehiculo) => [$vehiculo->getKey() => $vehiculo->kilometraje_vigente]
        );

        return view('admin.mantenimientos.edit', compact('mantenimiento', 'vehiculos', 'kilometrajes'));
    }

    public function update(Request $request, Mantenimiento $mantenimiento)
    {
        $datos = $request->validate($this->reglas(), $this->mensajes());

        // El kilometraje histórico se conserva si solo se editan datos del mismo vehículo.
        if ((int) $datos['vehiculo_id'] !== (int) $mantenimiento->vehiculo_id) {
            $vehiculo = Vehiculo::with('ultimaAsignacion')->findOrFail($datos['vehiculo_id']);
            $kilometraje = $vehiculo->kilometraje_vigente;

            if ($kilometraje === null) {
                return back()->withInput()->withErrors([
                    'vehiculo_id' => 'Kilometraje no disponible para el vehículo seleccionado.',
                ]);
            }

            $datos['kilometraje_mantenimiento'] = $kilometraje;
        }

        $mantenimiento->update($datos);

        return redirect()->route('admin.mantenimientos.index')->with('mensaje', 'Mantenimiento actualizado correctamente');
    }

    public function destroy(Mantenimiento $mantenimiento)
    {
        $mantenimiento->delete();

        return redirect()->route('admin.mantenimientos.index')->with('mensaje', 'Mantenimiento eliminado correctamente');
    }

    private function reglas(): array
    {
        return [
            'vehiculo_id' => ['required', 'integer', 'exists:vehiculos,id_vehiculo'],
            'fecha_mantenimiento' => ['required', 'date'],
            'categoria_mantenimiento' => ['required', 'string', 'max:45'],
            'descripcion_mantenimiento' => ['required', 'string', 'max:255'],
            'monto_mantenimiento' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'proximo_mantenimiento_fecha' => ['nullable', 'date', 'after_or_equal:fecha_mantenimiento'],
            'proximo_mantenimiento_km' => ['nullable', 'integer', 'min:0'],
        ];
    }

    private function mensajes(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'integer' => 'Escribe un número entero.',
            'numeric' => 'Escribe un monto válido.',
            'min' => 'El valor no puede ser negativo.',
            'vehiculo_id.exists' => 'El vehículo seleccionado no existe.',
            'proximo_mantenimiento_fecha.after_or_equal' => 'La fecha del próximo mantenimiento no puede ser anterior a la fecha del servicio.',
        ];
    }
}
