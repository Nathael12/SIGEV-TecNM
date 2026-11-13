<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VehiculoController extends Controller
{
    private const ESTADOS = [
        'Disponible',
        'Asignado',
        'En mantenimiento',
        'Fuera de servicio',
        'Baja',
    ];

    public function index()
    {
        $vehiculos = Vehiculo::orderBy('numero_economico')->get();
        return view('admin.vehiculos.index', compact('vehiculos'));
    }

    public function create()
    {
        $estados = self::ESTADOS;
        return view('admin.vehiculos.create', compact('estados'));
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
            'estado' => ['required', Rule::in(self::ESTADOS)],
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
        $datos['fotografia'] = $rutaFotografia;
        $vehiculo = Vehiculo::create($datos);
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
        $estados = self::ESTADOS;
        return view('admin.vehiculos.edit', compact('vehiculo', 'estados', 'fotografiaUrl'));
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
            'estado' => ['required', Rule::in(self::ESTADOS)],
            'fotografia' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'numero_economico.unique' => 'Este número económico ya está registrado por otro vehículo.',
            'placas.unique' => 'Estas placas ya están registradas por otro vehículo.',
        ]);

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
