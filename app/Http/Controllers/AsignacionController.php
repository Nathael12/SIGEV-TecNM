<?php

namespace App\Http\Controllers;

use App\Models\AsignacionVehiculo;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AsignacionController extends Controller
{
    /**
     * Nombre de la columna del kilometraje en la tabla vehiculos.
     * Hoy la BD la tiene como "kilometraje actual" (con espacio). Cuando se corrija
     * a kilometraje_actual en la migración, solo hay que cambiar esta línea.
     */
    private const COL_KM = 'kilometraje actual';

    /**
     * Solo se pueden asignar vehículos en estado Disponible (RN-03).
     */
    private function vehiculosDisponibles()
    {
        return Vehiculo::where('estado', 'Disponible')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('asignacion_vehiculos')
                    ->whereColumn('asignacion_vehiculos.vehiculo_id', 'vehiculos.id_vehiculo')
                    ->whereNull('asignacion_vehiculos.fecha_regreso');
            })
            ->orderBy('numero_economico')
            ->get();
    }

    /**
     * Solicitantes válidos: usuarios activos con el rol Solicitante (RN-02).
     */
    private function solicitantesActivos()
    {
        return User::where('estado', 'Activo')
            ->whereHas('roles', fn ($q) => $q->where('name', 'Solicitante'))
            ->orderBy('nombre_usuario')
            ->get();
    }

    /**
     * Da formato legible a un kilometraje (sin ceros decimales de sobra).
     */
    private function formatoKm($km): string
    {
        return rtrim(rtrim(number_format((float) $km, 2), '0'), '.');
    }

    /**
     * Listado e historial de asignaciones (RF-21).
     * Filtros opcionales por query string:
     *   ?vehiculo=ID  ?solicitante=ID  ?estado=activa|finalizada
     */
    public function index(Request $request)
    {
        $consulta = AsignacionVehiculo::with(['vehiculo', 'solicitante'])
            ->orderByDesc('fecha_salida');

        if ($request->filled('vehiculo')) {
            $consulta->where('vehiculo_id', $request->vehiculo);
        }

        if ($request->filled('solicitante')) {
            $consulta->where('usuario_id', $request->solicitante);
        }

        if ($request->estado === 'activa') {
            $consulta->whereNull('fecha_regreso');
        } elseif ($request->estado === 'finalizada') {
            $consulta->whereNotNull('fecha_regreso');
        }

        $asignaciones = $consulta->get();

        // Opciones de los filtros
        $vehiculos = Vehiculo::orderBy('numero_economico')->get();
        $solicitantes = User::withTrashed()
            ->whereIn('id', AsignacionVehiculo::select('usuario_id'))
            ->orderBy('nombre_usuario')
            ->get();

        // Cuántos vehículos están en ruta ahora mismo
        $enRuta = AsignacionVehiculo::activas()->count();

        return view('admin.asignaciones.index', compact('asignaciones', 'vehiculos', 'solicitantes', 'enRuta'));
    }

    /**
     * Formulario para registrar la salida de un vehículo (RF-15, RF-16).
     */
    public function create()
    {
        $vehiculos = $this->vehiculosDisponibles();
        $solicitantes = $this->solicitantesActivos();

        // Kilometraje vigente de cada vehículo, para sugerirlo en el formulario
        $kilometrajes = $vehiculos->mapWithKeys(
            fn ($v) => [$v->id_vehiculo => (float) $v->{self::COL_KM}]
        );

        return view('admin.asignaciones.create', compact('vehiculos', 'solicitantes', 'kilometrajes'));
    }

    /**
     * Registra la salida: crea la asignación y marca el vehículo como Asignado
     * (RF-15, RF-16, RF-18, RF-19; RN-02 a RN-05).
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehiculo_id'         => 'required|integer|exists:vehiculos,id_vehiculo',
            'usuario_id'          => 'required|integer|exists:users,id',
            'fecha_salida'        => 'required|date|before_or_equal:now',
            'motivo_uso'          => 'required|string|max:255',
            'destino_vehiculo'    => 'required|string|max:255',
            'kilometraje_inicial' => 'required|integer|min:0',
        ], [
            'required'                    => 'Este campo es obligatorio.',
            'integer'                     => 'Escribe un número entero.',
            'date'                        => 'Escribe una fecha y hora válidas.',
            'min'                         => 'El valor mínimo es :min.',
            'max'                         => 'Máximo :max caracteres.',
            'fecha_salida.before_or_equal' => 'La fecha de salida no puede ser futura.',
            'vehiculo_id.exists'          => 'El vehículo seleccionado no existe.',
            'usuario_id.exists'           => 'El solicitante seleccionado no existe.',
        ]);

        $vehiculo = null;

        // Todo o nada: si algo falla, no se crea la asignación ni cambia el vehículo.
        DB::transaction(function () use ($request, &$vehiculo) {
            // Se bloquea la fila del vehículo para que dos personas no lo asignen a la vez (RF-18)
            $vehiculo = Vehiculo::lockForUpdate()->findOrFail($request->vehiculo_id);

            // RN-03: solo vehículos Disponibles
            if ($vehiculo->estado !== 'Disponible') {
                throw ValidationException::withMessages([
                    'vehiculo_id' => 'El vehículo ya no está disponible (estado actual: ' . $vehiculo->estado . '). Elige otro.',
                ]);
            }

            // RN-04 / RF-18: una sola asignación activa por vehículo
            $yaActivo = AsignacionVehiculo::activas()
                ->where('vehiculo_id', $vehiculo->id_vehiculo)
                ->exists();

            if ($yaActivo) {
                throw ValidationException::withMessages([
                    'vehiculo_id' => 'Este vehículo ya tiene una asignación activa sin devolución.',
                ]);
            }

            // RN-02: solicitante registrado, activo y con rol Solicitante
            $solicitante = User::findOrFail($request->usuario_id);

            if ($solicitante->estado !== 'Activo' || ! $solicitante->hasRole('Solicitante')) {
                throw ValidationException::withMessages([
                    'usuario_id' => 'El solicitante debe ser un usuario activo con rol Solicitante.',
                ]);
            }

            // RN-05 / RF-19: el kilometraje inicial no puede ser menor al vigente del vehículo
            if ($request->kilometraje_inicial < (float) $vehiculo->{self::COL_KM}) {
                throw ValidationException::withMessages([
                    'kilometraje_inicial' => 'No puede ser menor al kilometraje actual del vehículo ('
                        . $this->formatoKm($vehiculo->{self::COL_KM}) . ' km).',
                ]);
            }

            AsignacionVehiculo::create([
                'vehiculo_id'         => $vehiculo->id_vehiculo,
                'usuario_id'          => $solicitante->id,
                'fecha_salida'        => $request->fecha_salida,
                'motivo_uso'          => $request->motivo_uso,
                'destino_vehiculo'    => $request->destino_vehiculo,
                'kilometraje_inicial' => $request->kilometraje_inicial,
            ]);

            // El vehículo sale de circulación normal
            $vehiculo->estado = 'Asignado';
            $vehiculo->save();
        });

        return redirect()->route('admin.asignaciones.index')
            ->with('mensaje', 'Salida registrada. El vehículo ' . $vehiculo->numero_economico . ' quedó como Asignado.')
            ->with('icono', 'success');
    }

    /**
     * Detalle de una asignación.
     */
    public function show(string $id)
    {
        $asignacion = AsignacionVehiculo::with(['vehiculo', 'solicitante'])->findOrFail($id);

        return view('admin.asignaciones.show', compact('asignacion'));
    }

    /**
     * Formulario de corrección de una asignación (solo Administrador, RN-08).
     */
    public function edit(string $id)
    {
        $asignacion = AsignacionVehiculo::with(['vehiculo', 'solicitante'])->findOrFail($id);

        // Solicitantes válidos. Se incluye al actual aunque ya no cumpla,
        // para que no desaparezca del select.
        $solicitantes = $this->solicitantesActivos();

        if (! $solicitantes->contains('id', $asignacion->usuario_id)) {
            $solicitantes->push($asignacion->solicitante);
        }

        return view('admin.asignaciones.edit', compact('asignacion', 'solicitantes'));
    }

    /**
     * Guarda la corrección. El vehículo y el kilometraje inicial nunca cambian.
     * Se cuida que el historial del vehículo siga coherente:
     *  - las fechas respetan a las asignaciones vecinas del mismo vehículo,
     *  - el kilometraje final no rebasa el inicial de la siguiente asignación,
     *  - si es la última asignación, el kilometraje del vehículo se sincroniza.
     */
    public function update(Request $request, string $id)
    {
        $asignacion = AsignacionVehiculo::findOrFail($id);
        $finalizada = ! $asignacion->activa;

        $reglas = [
            'usuario_id'       => 'required|integer|exists:users,id',
            'fecha_salida'     => 'required|date|before_or_equal:now',
            'motivo_uso'       => 'required|string|max:255',
            'destino_vehiculo' => 'required|string|max:255',
        ];

        // La fecha de regreso y el kilometraje final solo existen si ya hubo devolución
        if ($finalizada) {
            $reglas['fecha_regreso']     = 'required|date|before_or_equal:now|after_or_equal:fecha_salida';
            $reglas['kilometraje_final'] = 'required|integer|min:' . $asignacion->kilometraje_inicial;
        }

        $request->validate($reglas, [
            'required'                      => 'Este campo es obligatorio.',
            'integer'                       => 'Escribe un número entero.',
            'date'                          => 'Escribe una fecha y hora válidas.',
            'max'                           => 'Máximo :max caracteres.',
            'usuario_id.exists'             => 'El solicitante seleccionado no existe.',
            'fecha_salida.before_or_equal'  => 'La fecha de salida no puede ser futura.',
            'fecha_regreso.before_or_equal' => 'La fecha de regreso no puede ser futura.',
            'fecha_regreso.after_or_equal'  => 'La fecha de regreso no puede ser anterior a la de salida.',
            'kilometraje_final.min'         => 'No puede ser menor al kilometraje inicial ('
                . number_format($asignacion->kilometraje_inicial) . ' km).',
        ]);

        DB::transaction(function () use ($request, $id, $finalizada) {
            $asignacion = AsignacionVehiculo::lockForUpdate()->findOrFail($id);
            $vehiculo   = Vehiculo::lockForUpdate()->findOrFail($asignacion->vehiculo_id);

            // Solicitante: solo se valida si cambia (RN-02)
            if ((int) $request->usuario_id !== (int) $asignacion->usuario_id) {
                $nuevo = User::findOrFail($request->usuario_id);

                if ($nuevo->estado !== 'Activo' || ! $nuevo->hasRole('Solicitante')) {
                    throw ValidationException::withMessages([
                        'usuario_id' => 'El solicitante debe ser un usuario activo con rol Solicitante.',
                    ]);
                }
            }

            // Asignaciones vecinas del mismo vehículo (por el orden original)
            $previa = AsignacionVehiculo::where('vehiculo_id', $asignacion->vehiculo_id)
                ->where('id_asignacion', '!=', $asignacion->id_asignacion)
                ->where('fecha_salida', '<', $asignacion->fecha_salida)
                ->orderByDesc('fecha_salida')
                ->first();

            $siguiente = AsignacionVehiculo::where('vehiculo_id', $asignacion->vehiculo_id)
                ->where('id_asignacion', '!=', $asignacion->id_asignacion)
                ->where('fecha_salida', '>', $asignacion->fecha_salida)
                ->orderBy('fecha_salida')
                ->first();

            $nuevaSalida = \Illuminate\Support\Carbon::parse($request->fecha_salida);

            // La salida no puede invadir a la asignación previa
            if ($previa && $previa->fecha_regreso && $nuevaSalida->lt($previa->fecha_regreso)) {
                throw ValidationException::withMessages([
                    'fecha_salida' => 'No puede ser anterior al regreso de la asignación previa de este vehículo ('
                        . $previa->fecha_regreso->format('d/m/Y h:i A') . ').',
                ]);
            }

            if ($finalizada && $siguiente) {
                $nuevoRegreso = \Illuminate\Support\Carbon::parse($request->fecha_regreso);

                // El regreso no puede invadir a la asignación siguiente
                if ($nuevoRegreso->gt($siguiente->fecha_salida)) {
                    throw ValidationException::withMessages([
                        'fecha_regreso' => 'No puede ser posterior a la salida de la siguiente asignación de este vehículo ('
                            . $siguiente->fecha_salida->format('d/m/Y h:i A') . ').',
                    ]);
                }

                // El kilometraje final no puede rebasar el inicial de la siguiente asignación
                if ($request->kilometraje_final > $siguiente->kilometraje_inicial) {
                    throw ValidationException::withMessages([
                        'kilometraje_final' => 'No puede ser mayor al kilometraje inicial de la siguiente asignación ('
                            . number_format($siguiente->kilometraje_inicial) . ' km).',
                    ]);
                }
            }

            $kmFinalAnterior = $asignacion->kilometraje_final;

            // Se guardan solo los datos corregibles (vehículo y km inicial no se tocan)
            $asignacion->usuario_id       = $request->usuario_id;
            $asignacion->fecha_salida     = $request->fecha_salida;
            $asignacion->motivo_uso       = $request->motivo_uso;
            $asignacion->destino_vehiculo = $request->destino_vehiculo;

            if ($finalizada) {
                $asignacion->fecha_regreso     = $request->fecha_regreso;
                $asignacion->kilometraje_final = $request->kilometraje_final;
            }

            $asignacion->save();

            // Si es la última asignación del vehículo y este conservaba el kilometraje
            // final anterior, se actualiza al corregido (RF-20: datos consistentes)
            if ($finalizada && ! $siguiente && (float) $vehiculo->{self::COL_KM} === (float) $kmFinalAnterior) {
                $vehiculo->{self::COL_KM} = $request->kilometraje_final;
                $vehiculo->save();
            }
        });

        return redirect()->route('admin.asignaciones.index')
            ->with('mensaje', 'Asignación corregida correctamente')
            ->with('icono', 'success');
    }

    /**
     * Formulario para registrar la devolución de un vehículo (RF-17).
     */
    public function devolucion(string $id)
    {
        $asignacion = AsignacionVehiculo::with(['vehiculo', 'solicitante'])->findOrFail($id);

        // Si ya tiene devolución, no hay nada que registrar
        if (! $asignacion->activa) {
            return redirect()->route('admin.asignaciones.index')
                ->with('mensaje', 'Esta asignación ya tiene su devolución registrada.')
                ->with('icono', 'info');
        }

        return view('admin.asignaciones.devolucion', compact('asignacion'));
    }

    /**
     * Registra la devolución: cierra la asignación y actualiza el kilometraje
     * y el estado del vehículo en una sola operación (RF-17, RF-19, RF-20;
     * RN-06 y RN-07).
     */
    public function devolver(Request $request, string $id)
    {
        $asignacion = AsignacionVehiculo::findOrFail($id);

        $request->validate([
            // Debe ser posterior a la salida y no futura
            'fecha_regreso'     => 'required|date|before_or_equal:now|after_or_equal:'
                . $asignacion->fecha_salida->format('Y-m-d H:i:s'),
            // RN-06: el kilometraje final no puede ser menor al inicial
            'kilometraje_final' => 'required|integer|min:' . $asignacion->kilometraje_inicial,
            // RN-07: el vehículo vuelve disponible solo si no requiere mantenimiento
            'estado_vehiculo'   => 'required|in:Disponible,En mantenimiento,Fuera de servicio',
        ], [
            'required'                       => 'Este campo es obligatorio.',
            'integer'                        => 'Escribe un número entero.',
            'date'                           => 'Escribe una fecha y hora válidas.',
            'fecha_regreso.before_or_equal'  => 'La fecha de regreso no puede ser futura.',
            'fecha_regreso.after_or_equal'   => 'La fecha de regreso no puede ser anterior a la salida ('
                . $asignacion->fecha_salida->format('d/m/Y h:i A') . ').',
            'kilometraje_final.min'          => 'No puede ser menor al kilometraje inicial ('
                . number_format($asignacion->kilometraje_inicial) . ' km).',
            'estado_vehiculo.in'             => 'Elige un estado válido para el vehículo.',
        ]);

        $vehiculo = null;

        // Todo o nada: la asignación y el vehículo se actualizan juntos o no se actualiza ninguno.
        DB::transaction(function () use ($request, $id, &$vehiculo) {
            // Se bloquean ambos registros para evitar una doble devolución simultánea
            $asignacion = AsignacionVehiculo::lockForUpdate()->findOrFail($id);
            $vehiculo   = Vehiculo::lockForUpdate()->findOrFail($asignacion->vehiculo_id);

            if (! $asignacion->activa) {
                throw ValidationException::withMessages([
                    'fecha_regreso' => 'Esta asignación ya tiene su devolución registrada.',
                ]);
            }

            // El vehículo no puede retroceder su kilometraje vigente
            if ($request->kilometraje_final < (float) $vehiculo->{self::COL_KM}) {
                throw ValidationException::withMessages([
                    'kilometraje_final' => 'No puede ser menor al kilometraje actual del vehículo ('
                        . $this->formatoKm($vehiculo->{self::COL_KM}) . ' km).',
                ]);
            }

            // Se cierra la asignación
            $asignacion->fecha_regreso     = $request->fecha_regreso;
            $asignacion->kilometraje_final = $request->kilometraje_final;
            $asignacion->save();

            // RF-20: el kilometraje vigente pasa a ser el final y el vehículo cambia de estado
            $vehiculo->{self::COL_KM} = $request->kilometraje_final;
            $vehiculo->estado             = $request->estado_vehiculo;
            $vehiculo->save();
        });

        return redirect()->route('admin.asignaciones.index')
            ->with('mensaje', 'Devolución registrada. El vehículo ' . $vehiculo->numero_economico
                . ' quedó como ' . $vehiculo->estado . '.')
            ->with('icono', 'success');
    }
}
