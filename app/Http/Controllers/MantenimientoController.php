<?php

namespace App\Http\Controllers;

use App\Models\Mantenimiento;
use App\Models\Vehiculo;
use App\Support\AlertasMantenimiento as ConsultaAlertasMantenimiento;
use Illuminate\Http\Request;

class MantenimientoController extends Controller
{
    public function index(Request $request)
    {
        $consulta = Mantenimiento::with('vehiculo')->orderByDesc('fecha_mantenimiento')->orderByDesc('id_mantenimiento');

        $mantenimientos = $consulta->get();

        return view('admin.mantenimientos.index', [
            'mantenimientos' => $mantenimientos,
            'alertasMantenimiento' => ConsultaAlertasMantenimiento::obtener(),
        ]);
    }

    public function create()
    {
        $vehiculos = Vehiculo::orderBy('numero_economico')->get();

        return view('admin.mantenimientos.create', compact('vehiculos'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'vehiculo_id' => ['required', 'integer', 'exists:vehiculos,id_vehiculo'],
            'fecha_mantenimiento' => ['required', 'date'],
            'kilometraje_mantenimiento' => ['required', 'integer', 'min:0'],
            'categoria_mantenimiento' => ['required', 'string', 'max:45'],
            'descripcion_mantenimiento' => ['required', 'string', 'max:255'],
            'monto_mantenimiento' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'proximo_mantenimiento_fecha' => ['required', 'date', 'after_or_equal:fecha_mantenimiento'],
            'proximo_mantenimiento_km' => ['required', 'integer', 'min:0'],
        ], [
            'required' => 'Este campo es obligatorio.',
            'integer' => 'Escribe un número entero.',
            'numeric' => 'Escribe un monto válido.',
            'min' => 'El valor no puede ser negativo.',
            'vehiculo_id.exists' => 'El vehículo seleccionado no existe.',
            'proximo_mantenimiento_fecha.after_or_equal' => 'La fecha del próximo mantenimiento no puede ser anterior a la fecha del servicio.',
        ]);

        Mantenimiento::create($datos);

        return redirect()->route('admin.mantenimientos.index')->with('mensaje', 'Mantenimiento registrado correctamente');
    }
}
