<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehiculoController extends Controller
{
    public function index()
    {
        $vehiculos = Vehiculo::orderBy('numero_economico')->get();
        return view('admin.vehiculos.index', compact('vehiculos'));
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
            'estado' => ['required', Rule::in(['Activo', 'Inactivo'])],
        ], [
            'numero_economico.unique' => 'Este número económico ya está registrado.',
            'placas.unique' => 'Estas placas ya están registradas.',
        ]);

        $vehiculo = Vehiculo::create($datos);
        return redirect()->route('admin.vehiculos.index')
            ->with('mensaje', 'Vehículo registrado correctamente');
    }

    public function show(string $id)
    {
        $vehiculo = Vehiculo::findOrFail($id);
        return view('admin.vehiculos.show', compact('vehiculo'));
    }

    public function edit(string $id)
    {
        $vehiculo = Vehiculo::findOrFail($id);
        return view('admin.vehiculos.edit', compact('vehiculo'));
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
            'estado' => ['required', Rule::in(['Activo', 'Inactivo'])],
        ], [
            'numero_economico.unique' => 'Este número económico ya está registrado por otro vehículo.',
            'placas.unique' => 'Estas placas ya están registradas por otro vehículo.',
        ]);

        $vehiculo->update($datos);

        return redirect()->route('admin.vehiculos.index')
            ->with('mensaje', 'Vehículo actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $vehiculo = Vehiculo::findOrFail($id);
        $vehiculo->delete();

        return redirect()->route('admin.vehiculos.index')
            ->with('mensaje', 'Vehículo eliminado correctamente');
    }
}
