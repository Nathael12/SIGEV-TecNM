<?php

namespace App\Http\Controllers;

use App\Exports\AsignacionesExport;
use App\Exports\FlotillaExport;
use App\Exports\MantenimientosExport;
use App\Models\Ajuste;
use App\Models\AsignacionVehiculo;
use App\Models\Mantenimiento;
use App\Models\Vehiculo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    /**
     * Estados posibles de un vehículo (los mismos del módulo de vehículos y del dashboard).
     */
    public const ESTADOS_VEHICULO = [
        'Disponible',
        'Asignado',
        'En mantenimiento',
        'Fuera de servicio',
        'Baja',
    ];

    /**
     * Pantalla principal de reportes: una tarjeta por cada reporte.
     * Cuando un reporte esté listo, se cambia 'disponible' a true.
     */
    public function index()
    {
        $reportes = [
            [
                'titulo'      => 'Listado de flotilla',
                'descripcion' => 'Todos los vehículos de la flotilla con su estado actual y su kilometraje.',
                'icono'       => 'fa-truck',
                'url'         => url('/admin/reportes/flotilla'),
                'disponible'  => true, // RF-30
            ],
            [
                'titulo'      => 'Historial de asignaciones',
                'descripcion' => 'Salidas y devoluciones de un vehículo: solicitante, fechas, destino y kilómetros recorridos.',
                'icono'       => 'fa-route',
                'url'         => url('/admin/reportes/asignaciones'),
                'disponible'  => true, // RF-32
            ],
            [
                'titulo'      => 'Historial de mantenimientos',
                'descripcion' => 'Mantenimientos realizados a un vehículo, con fecha y detalle.',
                'icono'       => 'fa-tools',
                'url'         => url('/admin/reportes/mantenimientos'),
                'disponible'  => true, // RF-31
            ],
        ];

        return view('admin.reportes.index', compact('reportes'));
    }

    // =====================================================================
    // RF-30 · Listado de flotilla con su estado
    // =====================================================================

    /**
     * Vista previa del listado de flotilla (con filtros y resumen por estado).
     */
    public function flotilla(Request $request)
    {
        $filtros = $this->filtrosFlotilla($request);

        $vehiculos = $this->consultaFlotilla($filtros)
            ->paginate(20)
            ->withQueryString();

        // Totales de toda la flotilla por estado (no dependen del filtro aplicado)
        $totales = Vehiculo::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $totalFlotilla = $totales->sum();
        $estados = self::ESTADOS_VEHICULO;

        return view('admin.reportes.flotilla', compact('vehiculos', 'filtros', 'totales', 'totalFlotilla', 'estados'));
    }

    /**
     * Filtros válidos del listado de flotilla (lo que no sea válido se ignora).
     */
    private function filtrosFlotilla(Request $request): array
    {
        $estado = $request->query('estado');
        $buscar = trim((string) $request->query('buscar'));

        return [
            'estado' => in_array($estado, self::ESTADOS_VEHICULO, true) ? $estado : null,
            'buscar' => $buscar === '' ? null : $buscar,
        ];
    }

    /**
     * Consulta única del listado de flotilla. La vista previa la usa ahora y
     * las exportaciones a PDF y Excel la reutilizarán, para que siempre coincidan.
     */
    private function consultaFlotilla(array $filtros)
    {
        return Vehiculo::query()
            ->with('ultimaAsignacion')
            ->when($filtros['estado'], fn ($q, $estado) => $q->where('estado', $estado))
            ->when($filtros['buscar'], function ($q, $texto) {
                $q->where(function ($q) use ($texto) {
                    $q->where('numero_economico', 'like', "%{$texto}%")
                        ->orWhere('placas', 'like', "%{$texto}%")
                        ->orWhere('marca', 'like', "%{$texto}%")
                        ->orWhere('modelo', 'like', "%{$texto}%");
                });
            })
            ->orderBy('numero_economico');
    }

    // =====================================================================
    // RF-32 · Historial de asignaciones de un vehículo
    // RF-33 · Filtros por vehículo y por periodo
    // =====================================================================

    /**
     * Vista previa del historial de asignaciones (filtro por vehículo y periodo).
     */
    public function asignaciones(Request $request)
    {
        $filtros = $this->filtrosHistorial($request);
        $base = $this->consultaAsignaciones($filtros);

        $asignaciones = (clone $base)->paginate(20)->withQueryString();

        // Totales del conjunto filtrado completo (no solo de la página actual)
        $total = (clone $base)->reorder()->count();
        $activas = (clone $base)->reorder()->whereNull('fecha_regreso')->count();

        $totales = [
            'asignaciones' => $total,
            'activas'      => $activas,
            'finalizadas'  => $total - $activas,
            // Kilómetros recorridos: solo cuentan las asignaciones ya devueltas
            'km'           => (int) (clone $base)->reorder()
                ->whereNotNull('kilometraje_final')
                ->sum(DB::raw('kilometraje_final - kilometraje_inicial')),
        ];

        $vehiculos = Vehiculo::orderBy('numero_economico')->get();
        $vehiculoSeleccionado = $filtros['vehiculo']
            ? $vehiculos->firstWhere('id_vehiculo', $filtros['vehiculo'])
            : null;

        return view('admin.reportes.asignaciones', compact(
            'asignaciones', 'filtros', 'totales', 'vehiculos', 'vehiculoSeleccionado'
        ));
    }

    /**
     * Filtros válidos de los historiales (asignaciones y mantenimientos): vehículo y periodo.
     * El periodo se aplica sobre la fecha de salida (asignaciones) o del servicio (mantenimientos).
     */
    private function filtrosHistorial(Request $request): array
    {
        $datos = $request->validate([
            'vehiculo' => ['nullable', 'integer', 'exists:vehiculos,id_vehiculo'],
            'desde'    => ['nullable', 'date'],
            'hasta'    => ['nullable', 'date', 'after_or_equal:desde'],
        ], [
            'vehiculo.exists'      => 'El vehículo seleccionado no existe.',
            'date'                 => 'Escribe una fecha válida.',
            'hasta.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.',
        ]);

        return [
            'vehiculo' => $datos['vehiculo'] ?? null,
            'desde'    => $datos['desde'] ?? null,
            'hasta'    => $datos['hasta'] ?? null,
        ];
    }

    /**
     * Consulta única del historial de asignaciones (la reutilizarán el PDF y el Excel).
     * Orden cronológico: de la salida más antigua a la más reciente.
     */
    private function consultaAsignaciones(array $filtros)
    {
        return AsignacionVehiculo::query()
            ->with(['vehiculo', 'solicitante'])
            ->when($filtros['vehiculo'], fn ($q, $id) => $q->where('vehiculo_id', $id))
            ->when($filtros['desde'], fn ($q, $fecha) => $q->whereDate('fecha_salida', '>=', $fecha))
            ->when($filtros['hasta'], fn ($q, $fecha) => $q->whereDate('fecha_salida', '<=', $fecha))
            ->orderBy('fecha_salida')
            ->orderBy('id_asignacion');
    }

    // =====================================================================
    // RF-31 · Historial de mantenimientos de un vehículo
    // RF-33 · Filtros por vehículo y por periodo (los mismos del historial de asignaciones)
    // =====================================================================

    /**
     * Vista previa del historial de mantenimientos (filtro por vehículo y periodo).
     */
    public function mantenimientos(Request $request)
    {
        $filtros = $this->filtrosHistorial($request);
        $base = $this->consultaMantenimientos($filtros);

        $mantenimientos = (clone $base)->paginate(20)->withQueryString();

        // Totales del conjunto filtrado completo (no solo de la página actual)
        $ultimo = (clone $base)->reorder()->max('fecha_mantenimiento');

        $totales = [
            'mantenimientos' => (clone $base)->reorder()->count(),
            'costo'          => (float) (clone $base)->reorder()->sum('monto_mantenimiento'),
            'ultimo'         => $ultimo ? Carbon::parse($ultimo) : null,
        ];

        $vehiculos = Vehiculo::orderBy('numero_economico')->get();
        $vehiculoSeleccionado = $filtros['vehiculo']
            ? $vehiculos->firstWhere('id_vehiculo', $filtros['vehiculo'])
            : null;

        return view('admin.reportes.mantenimientos', compact(
            'mantenimientos', 'filtros', 'totales', 'vehiculos', 'vehiculoSeleccionado'
        ));
    }

    /**
     * Consulta única del historial de mantenimientos (la reutilizan el PDF y el Excel).
     * Orden cronológico: del servicio más antiguo al más reciente.
     */
    private function consultaMantenimientos(array $filtros)
    {
        return Mantenimiento::query()
            ->with('vehiculo')
            ->when($filtros['vehiculo'], fn ($q, $id) => $q->where('vehiculo_id', $id))
            ->when($filtros['desde'], fn ($q, $fecha) => $q->whereDate('fecha_mantenimiento', '>=', $fecha))
            ->when($filtros['hasta'], fn ($q, $fecha) => $q->whereDate('fecha_mantenimiento', '<=', $fecha))
            ->orderBy('fecha_mantenimiento')
            ->orderBy('id_mantenimiento');
    }

    // =====================================================================
    // RF-34 · Exportación a PDF y Excel
    // Usan las mismas consultas y filtros que la vista previa, pero sin paginar.
    // =====================================================================

    public function flotillaPdf(Request $request)
    {
        $filtros = $this->filtrosFlotilla($request);
        $vehiculos = $this->consultaFlotilla($filtros)->get();

        if ($vehiculos->isEmpty()) {
            return $this->sinRegistros();
        }

        return Pdf::loadView('admin.reportes.pdf.flotilla', [
            'titulo'      => 'Listado de flotilla',
            'vehiculos'   => $vehiculos,
            'filtros'     => $filtros,
            'totales'     => $vehiculos->groupBy('estado')->map->count(),
            'estados'     => self::ESTADOS_VEHICULO,
            'institucion' => $this->nombreInstitucion(),
            'generado'    => now(),
            'usuario'     => $this->nombreUsuarioActual(),
        ])->setPaper('letter', 'landscape')
            ->download($this->nombreArchivo('flotilla', 'pdf'));
    }

    public function flotillaExcel(Request $request)
    {
        $filtros = $this->filtrosFlotilla($request);
        $vehiculos = $this->consultaFlotilla($filtros)->get();

        if ($vehiculos->isEmpty()) {
            return $this->sinRegistros();
        }

        return Excel::download(new FlotillaExport($vehiculos), $this->nombreArchivo('flotilla', 'xlsx'));
    }

    public function asignacionesPdf(Request $request)
    {
        $filtros = $this->filtrosHistorial($request);
        $asignaciones = $this->consultaAsignaciones($filtros)->get();

        if ($asignaciones->isEmpty()) {
            return $this->sinRegistros();
        }

        $activas = $asignaciones->whereNull('fecha_regreso')->count();

        return Pdf::loadView('admin.reportes.pdf.asignaciones', [
            'titulo'       => 'Historial de asignaciones',
            'asignaciones' => $asignaciones,
            'filtros'      => $filtros,
            'vehiculo'     => $filtros['vehiculo'] ? Vehiculo::find($filtros['vehiculo']) : null,
            'totales'      => [
                'asignaciones' => $asignaciones->count(),
                'activas'      => $activas,
                'finalizadas'  => $asignaciones->count() - $activas,
                'km'           => (int) $asignaciones->sum(fn ($a) => $a->kilometros_recorridos ?? 0),
            ],
            'institucion'  => $this->nombreInstitucion(),
            'generado'     => now(),
            'usuario'      => $this->nombreUsuarioActual(),
        ])->setPaper('letter', 'landscape')
            ->download($this->nombreArchivo('historial-asignaciones', 'pdf'));
    }

    public function asignacionesExcel(Request $request)
    {
        $filtros = $this->filtrosHistorial($request);
        $asignaciones = $this->consultaAsignaciones($filtros)->get();

        if ($asignaciones->isEmpty()) {
            return $this->sinRegistros();
        }

        return Excel::download(new AsignacionesExport($asignaciones), $this->nombreArchivo('historial-asignaciones', 'xlsx'));
    }

    public function mantenimientosPdf(Request $request)
    {
        $filtros = $this->filtrosHistorial($request);
        $mantenimientos = $this->consultaMantenimientos($filtros)->get();

        if ($mantenimientos->isEmpty()) {
            return $this->sinRegistros();
        }

        return Pdf::loadView('admin.reportes.pdf.mantenimientos', [
            'titulo'         => 'Historial de mantenimientos',
            'mantenimientos' => $mantenimientos,
            'filtros'        => $filtros,
            'vehiculo'       => $filtros['vehiculo'] ? Vehiculo::find($filtros['vehiculo']) : null,
            'totales'        => [
                'mantenimientos' => $mantenimientos->count(),
                'costo'          => (float) $mantenimientos->sum('monto_mantenimiento'),
                'ultimo'         => $mantenimientos->max('fecha_mantenimiento'),
            ],
            'institucion'    => $this->nombreInstitucion(),
            'generado'       => now(),
            'usuario'        => $this->nombreUsuarioActual(),
        ])->setPaper('letter', 'landscape')
            ->download($this->nombreArchivo('historial-mantenimientos', 'pdf'));
    }

    public function mantenimientosExcel(Request $request)
    {
        $filtros = $this->filtrosHistorial($request);
        $mantenimientos = $this->consultaMantenimientos($filtros)->get();

        if ($mantenimientos->isEmpty()) {
            return $this->sinRegistros();
        }

        return Excel::download(new MantenimientosExport($mantenimientos), $this->nombreArchivo('historial-mantenimientos', 'xlsx'));
    }

    /**
     * Si no hay registros, no se descarga un archivo vacío: se regresa a la pantalla con un aviso.
     */
    private function sinRegistros()
    {
        return redirect()->back(302, [], url('/admin/reportes'))
            ->with('mensaje', 'No hay registros para exportar con los filtros seleccionados.')
            ->with('icono', 'info');
    }

    /**
     * Nombre de la institución para el encabezado del PDF (se edita en Ajustes).
     */
    private function nombreInstitucion(): string
    {
        return Ajuste::query()->value('nombre') ?: 'SIGEV-TecNM';
    }

    /**
     * Nombre de quien genera el reporte.
     */
    private function nombreUsuarioActual(): string
    {
        $usuario = Auth::user();

        $nombre = trim(
            ($usuario->nombre_usuario ?? '') . ' ' .
            ($usuario->apellido_paterno ?? '') . ' ' .
            ($usuario->apellido_materno ?? '')
        );

        return $nombre !== '' ? $nombre : (string) $usuario->name;
    }

    /**
     * Nombre del archivo descargado: reporte-AAAAMMDD-HHMMSS.extensión
     */
    private function nombreArchivo(string $base, string $extension): string
    {
        return $base . '-' . now()->format('Ymd-His') . '.' . $extension;
    }
}