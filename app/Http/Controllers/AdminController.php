<?php

namespace App\Http\Controllers;

use App\Models\AlertaMantenimiento;
use App\Models\Mantenimiento;
use App\Models\Vehiculo;
use App\Support\AlertasMantenimiento as ConsultaAlertasMantenimiento;
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

        $totalVehiculos = (int) $conteosPorEstado->sum();
        $mantenimientosPendientes = Mantenimiento::where('estado_mantenimiento', 'pendiente')->count();
        $alertasMantenimiento = AlertaMantenimiento::with('mantenimiento.vehiculo')
            ->where('estado', 'activa')
            ->orderByRaw("CASE WHEN tipo = 'vencida' THEN 0 ELSE 1 END")
            ->orderBy('fecha_mantenimiento')
            ->get();
        $mantenimientosProximos = $alertasMantenimiento->where('tipo', 'proxima')->count();

        $indicadoresVehiculos = [
            ['titulo' => 'Total de vehículos', 'cantidad' => $totalVehiculos, 'icono' => 'truck', 'color' => 'blue'],
            ['titulo' => 'Disponibles', 'cantidad' => (int) $conteosPorEstado->get('Disponible', 0), 'icono' => 'check-circle', 'color' => 'emerald'],
            ['titulo' => 'Asignados', 'cantidad' => (int) $conteosPorEstado->get('Asignado', 0), 'icono' => 'key', 'color' => 'indigo'],
            ['titulo' => 'En mantenimiento', 'cantidad' => (int) $conteosPorEstado->get('En mantenimiento', 0), 'icono' => 'wrench-screwdriver', 'color' => 'amber'],
            ['titulo' => 'Fuera de servicio', 'cantidad' => (int) $conteosPorEstado->get('Fuera de servicio', 0), 'icono' => 'exclamation-triangle', 'color' => 'orange'],
            ['titulo' => 'Mantenimientos pendientes', 'cantidad' => $mantenimientosPendientes, 'icono' => 'wrench-screwdriver', 'color' => 'slate'],
            ['titulo' => 'Mantenimientos próximos', 'cantidad' => $mantenimientosProximos, 'icono' => 'calendar-days', 'color' => 'indigo'],
        ];

        $distribucionVehiculos = collect(['Disponible', 'Asignado', 'En mantenimiento', 'Fuera de servicio', 'Baja'])
            ->map(fn ($estado) => ['estado' => $estado, 'total' => (int) $conteosPorEstado->get($estado, 0)]);
        $vehiculos = Vehiculo::with('ultimoMantenimiento')->orderBy('numero_economico')->get();
        $alertasKilometraje = ConsultaAlertasMantenimiento::obtener();
        return view('admin.index', [
            'dashboardDate' => Carbon::now()->locale('es')->translatedFormat('l, d \\d\\e F \\d\\e Y'),
            'rolUsuario' => $rolUsuario,
            'indicadoresVehiculos' => $indicadoresVehiculos,
            'distribucionVehiculos' => $distribucionVehiculos,
            'totalVehiculos' => $totalVehiculos,
            'vehiculos' => $vehiculos,
            'alertasKilometraje' => $alertasKilometraje,
            'alertasMantenimiento' => $alertasMantenimiento,
        ]);
    }
}


