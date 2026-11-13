<?php

namespace App\Http\Controllers;

use App\Models\AlertaMantenimiento;
use App\Models\Vehiculo;
use App\Services\GenerarAlertasMantenimiento;
use Illuminate\Support\Carbon;

class AdminController extends Controller
{
    public function index(GenerarAlertasMantenimiento $generadorAlertas)
    {
        $generadorAlertas->ejecutar();

        $rolUsuario = auth()->user()->getRoleNames()->implode(', ');
        $rolUsuario = $rolUsuario !== '' ? $rolUsuario : 'Usuario';

        $conteosPorEstado = Vehiculo::query()
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $indicadoresVehiculos = [
            ['titulo' => 'Total de vehículos', 'cantidad' => Vehiculo::count(), 'icono' => 'truck', 'color' => 'blue'],
            ['titulo' => 'Disponible', 'cantidad' => $conteosPorEstado->get('Disponible', 0), 'icono' => 'check-circle', 'color' => 'emerald'],
            ['titulo' => 'Asignado', 'cantidad' => $conteosPorEstado->get('Asignado', 0), 'icono' => 'user', 'color' => 'indigo'],
            ['titulo' => 'En mantenimiento', 'cantidad' => $conteosPorEstado->get('En mantenimiento', 0), 'icono' => 'wrench-screwdriver', 'color' => 'amber'],
            ['titulo' => 'Fuera de servicio', 'cantidad' => $conteosPorEstado->get('Fuera de servicio', 0), 'icono' => 'exclamation-triangle', 'color' => 'orange'],
            ['titulo' => 'Baja', 'cantidad' => $conteosPorEstado->get('Baja', 0), 'icono' => 'archive-box', 'color' => 'slate'],
        ];

        $vehiculos = Vehiculo::orderBy('numero_economico')->get();
        $alertasMantenimiento = AlertaMantenimiento::with('mantenimiento.vehiculo')
            ->where('estado', 'activa')->orderByRaw("CASE WHEN tipo = 'vencida' THEN 0 ELSE 1 END")
            ->orderBy('fecha_mantenimiento')->get();

        return view('admin.index', [
            'dashboardDate' => Carbon::now()->locale('es')->translatedFormat('l, d \d\e F \d\e Y'),
            'rolUsuario' => $rolUsuario,
            'indicadoresVehiculos' => $indicadoresVehiculos,
            'vehiculos' => $vehiculos,
            'alertasMantenimiento' => $alertasMantenimiento,
        ]);
    }
}
