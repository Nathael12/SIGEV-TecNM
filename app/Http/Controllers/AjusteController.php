<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ajuste;

class AjusteController extends Controller
{
    public function index()
    {
        $ajuste = Ajuste::first() ?? new Ajuste();
        $logoUrl = $ajuste->logo_url;

        return view('admin.ajustes.index', compact('ajuste', 'logoUrl'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'logo' => 'nullable|image|max:10240',
            'web' => 'nullable|string|max:255',
        ]);

        // Actualizar el registro de ajustes existente.
        $ajuste = Ajuste::firstOrFail();

        // Si se seleccionó un logo, guardarlo.
        if ($request->hasFile('logo')) {
            $datos['logo'] = $request->file('logo')
                ->store('ajustes', 'public');
        } else {
            unset($datos['logo']);
        }

        $ajuste->update($datos);

        return redirect()
            ->route('admin.ajustes.index')
            ->with('success', 'Los ajustes se guardaron correctamente.');
    }
}
