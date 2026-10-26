<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    // Mostrar la tabla
    public function index()
    {
        $nuevos = session('usuarios_nuevos', []);

        return view('admin.usuarios.index', compact('nuevos'));
    }

    // Mostrar el formulario
    public function create()
    {
        return view('admin.usuarios.create');
    }

    // Guardar el usuario
    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre_usuario'   => 'required|max:45',
            'apellido_paterno' => 'required|max:45',
            'apellido_materno' => 'required|max:45',
            'correo_usuario'   => 'required|email|max:45',
            'telefono_usuario' => 'required|digits:10',
            'rol'              => 'required|in:Administrador,Conductor,Supervisor',
            'password'         => 'required|min:8|confirmed',
        ]);

        $datos['password'] = Hash::make($datos['password']);

        $nuevos = session('usuarios_nuevos', []);
        $nuevos[] = $datos;
        session(['usuarios_nuevos' => $nuevos]);

        return redirect()->route('usuarios.index')
            ->with('ok', 'Usuario registrado correctamente.');
    }
}