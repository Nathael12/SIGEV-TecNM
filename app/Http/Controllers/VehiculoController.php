<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class VehiculoController extends Controller
{
    public function index(Request $request)
    {
        $columnasOrdenables = [
            'marca' => 'marca',
            'modelo' => 'modelo',
            'anio' => 'anio',
            'estado' => 'estado',
        ];
        $orden = $request->query('orden');
        $direccion = $request->query('direccion');

        if (! isset($columnasOrdenables[$orden]) || ! in_array($direccion, ['asc', 'desc'], true)) {
            $orden = 'created_at';
            $direccion = 'desc';
        }

        $vehiculos = Vehiculo::query()
            ->orderBy($columnasOrdenables[$orden] ?? $orden, $direccion)
            ->orderBy('id_vehiculo', $direccion)
            ->paginate(15)
            ->withQueryString();

        $ordenActual = $orden;
        $direccionActual = $direccion;

        return view('admin.vehiculos.index', compact('vehiculos', 'ordenActual', 'direccionActual'));
    }

    public function create()
    {
        return view('admin.vehiculos.create');
    }

    public function store(Request $request)
    {
        // Estandariza los identificadores para que diferencias de espacios o
        // mayúsculas no permitan registrar el mismo vehículo dos veces.
        $request->merge([
            'numero_economico' => trim((string) $request->input('numero_economico')),
            'placas' => strtoupper(trim((string) $request->input('placas'))),
        ]);

        $datos = $request->validate([
            'numero_economico' => ['required', 'string', 'max:255', Rule::unique('vehiculos', 'numero_economico')],
            'placas' => ['required', 'string', 'max:255', Rule::unique('vehiculos', 'placas')],
            'marca' => ['required', 'string', 'max:255'],
            'modelo' => ['required', 'string', 'max:255'],
            'anio' => ['required', 'integer', 'between:1901,' . (now()->year + 1)],
            'tipo' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:255'],
            'combustible' => ['required', 'string', 'max:255'],
            'kilometraje' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'fecha_adquisicion' => ['required', 'date'],
            'fotografia' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'numero_economico.unique' => 'Este número económico ya está registrado.',
            'placas.unique' => 'Estas placas ya están registradas.',
        ]);

        $rutaFotografia = $request->file('fotografia')->store('vehiculos', 'public');
        if (! $rutaFotografia) {
            return back()->withInput()->withErrors(['fotografia' => 'No se pudo guardar la fotografía. Inténtalo de nuevo.']);
        }

        $datos = $this->mapearColumnasVehiculo($datos);
        // Los vehiculos nuevos siempre ingresan a la flotilla disponibles.
        // El estado no se acepta desde el formulario para evitar asignaciones manuales al registrar.
        $datos['estado'] = 'Disponible';
        $datos['fotografia'] = $rutaFotografia;

        try {
            DB::transaction(fn () => Vehiculo::create($datos));
        } catch (UniqueConstraintViolationException $exception) {
            Storage::disk('public')->delete($rutaFotografia);

            return back()->withInput()->withErrors([
                'numero_economico' => 'El número económico o las placas ya están registrados. Verifica los datos e inténtalo de nuevo.',
            ]);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($rutaFotografia);
            report($exception);

            return back()->withInput()->withErrors([
                'fotografia' => 'No se pudo registrar el vehículo. Revisa los datos e inténtalo de nuevo.',
            ]);
        }

        return redirect()->route('admin.vehiculos.index')
            ->with('mensaje', 'Vehículo registrado correctamente');
    }

    public function show(string $id)
    {
        $vehiculo = Vehiculo::findOrFail($id);
        $fotografiaUrl = $vehiculo->fotografia_url;

        return view('admin.vehiculos.show', compact('vehiculo', 'fotografiaUrl'));
    }

    public function edit(string $id)
    {
        $vehiculo = Vehiculo::findOrFail($id);
        $fotografiaUrl = $vehiculo->fotografia_url;
        return view('admin.vehiculos.edit', compact('vehiculo', 'fotografiaUrl'));
    }

    public function update(Request $request, string $id)
    {
        $vehiculo = Vehiculo::findOrFail($id);

        $request->merge([
            'numero_economico' => trim((string) $request->input('numero_economico')),
            'placas' => strtoupper(trim((string) $request->input('placas'))),
        ]);

        $datos = $request->validate([
            'numero_economico' => ['required', 'string', 'max:255', Rule::unique('vehiculos', 'numero_economico')->ignore($vehiculo->getKey(), 'id_vehiculo')],
            'placas' => ['required', 'string', 'max:255', Rule::unique('vehiculos', 'placas')->ignore($vehiculo->getKey(), 'id_vehiculo')],
            'marca' => ['required', 'string', 'max:255'],
            'modelo' => ['required', 'string', 'max:255'],
            'anio' => ['required', 'integer', 'between:1901,' . (now()->year + 1)],
            'tipo' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:255'],
            'combustible' => ['required', 'string', 'max:255'],
            'kilometraje' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'fecha_adquisicion' => ['required', 'date'],
            'fotografia' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'numero_economico.unique' => 'Este número económico ya está registrado por otro vehículo.',
            'placas.unique' => 'Estas placas ya están registradas por otro vehículo.',
        ]);

        // El estado se administra desde los flujos de asignación y devolución, no desde la edición general.
        unset($datos['estado']);
        unset($datos['fotografia']);
        $datos = $this->mapearColumnasVehiculo($datos);
        $fotografiaAnterior = $vehiculo->fotografia;
        if ($request->hasFile('fotografia')) {
            $rutaFotografia = $request->file('fotografia')->store('vehiculos', 'public');
            if (! $rutaFotografia) {
                return back()->withInput()->withErrors(['fotografia' => 'No se pudo guardar la fotografía. Inténtalo de nuevo.']);
            }

            $datos['fotografia'] = $rutaFotografia;
        }

        $vehiculo->update($datos);

        if ($request->hasFile('fotografia') && $fotografiaAnterior) {
            Storage::disk('public')->delete($fotografiaAnterior);
        }

        return redirect()->route('admin.vehiculos.index')
            ->with('mensaje', 'Vehículo actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $vehiculo = Vehiculo::findOrFail($id);
        $fotografia = $vehiculo->fotografia;
        $vehiculo->delete();
        if ($fotografia) {
            Storage::disk('public')->delete($fotografia);
        }

        return redirect()->route('admin.vehiculos.index')
            ->with('mensaje', 'Vehículo eliminado correctamente');
    }

    /** Convierte los nombres del formulario a los nombres existentes en la tabla. */
    private function mapearColumnasVehiculo(array $datos): array
    {
        $datos['tipo vehiculo'] = $datos['tipo'];
        $datos['tipo combustible'] = $datos['combustible'];
        $datos['kilometraje actual'] = $datos['kilometraje'];

        unset($datos['tipo'], $datos['combustible'], $datos['kilometraje']);

        return $datos;
    }
}
