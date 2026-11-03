<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Testing\Fluent\Concerns\Has;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuarios = User::withTrashed()->get();
        return view('admin.usuarios.index', compact('usuarios'));
    }   


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $roles = Role::all();
        return view('admin.usuarios.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre_usuario'   => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'correo_usuario'   => 'required|email|max:255|unique:users,email',
            'telefono_usuario' => 'required|digits:10',
            'rol'              => 'required|exists:roles,name',
            'estado'           => 'required|in:Activo,Inactivo',
            'password'         => 'required|min:8|confirmed',
        ]);

        $usuario = new User();
        $usuario->name = $request->nombre_usuario . ' ' . $request->apellido_paterno;
        $usuario->email            = $request->correo_usuario;
        $usuario->nombre_usuario   = $request->nombre_usuario;
        $usuario->apellido_paterno = $request->apellido_paterno;
        $usuario->apellido_materno = $request->apellido_materno;
        $usuario->correo_usuario   = $request->correo_usuario;
        $usuario->telefono_usuario = $request->telefono_usuario;
        $usuario->estado           = $request->estado;
        $usuario->password         = Hash::make($request->password);
        $usuario->save();

        $usuario->assignRole($request->rol);

        return redirect('/admin/usuarios')->with('mensaje', 'Usuario creado correctamente');
        
    }

    /** 
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $usuario = User::with('roles')->findOrFail($id);
        return view('admin.usuarios.show', compact('usuario'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $usuario = User::findOrFail($id);
        $roles = Role::all();
        return view('admin.usuarios.edit', compact('usuario', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'rol'              => 'required|string|exists:roles,name',
            'correo_usuario'   => 'required|email|max:255|unique:users,email,' . $id,
            'password'         => 'nullable|string|confirmed|min:8',
            'nombre_usuario'   => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'telefono_usuario' => 'required|digits:10',
            'estado'           => 'required|in:Activo,Inactivo',
        ]);
 
        $usuario = User::findOrFail($id);
        $usuario->name  = $request->nombre_usuario . ' ' . $request->apellido_paterno;
        $usuario->email = $request->correo_usuario;
 
        if ($request->filled('password')) {
            $usuario->password = bcrypt($request->password);
        }
 
        $usuario->nombre_usuario   = $request->nombre_usuario;
        $usuario->apellido_paterno = $request->apellido_paterno;
        $usuario->apellido_materno = $request->apellido_materno;
        $usuario->correo_usuario   = $request->correo_usuario;
        $usuario->telefono_usuario = $request->telefono_usuario;
        $usuario->estado           = $request->estado;
        $usuario->save();
 
        $usuario->syncRoles($request->rol);
 
        return redirect()->route('admin.usuarios.index')
            ->with('mensaje', 'Usuario actualizado correctamente')
            ->with('icono', 'success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //echo "Eliminar usuario con ID: " . $id;
        if (Auth::id() == $id) {
            return redirect()->route('admin.usuarios.index')
            ->with('mensaje', 'No puedes eliminar tu propio usuario')
            ->with('icono', 'error');
        }

        $usuario = User::findOrFail($id);
        $usuario->estado = 'Inactivo';
        $usuario->save();

        $usuario->delete();

        return redirect()->route('admin.usuarios.index')
            ->with('mensaje', 'Usuario eliminado correctamente')
            ->with('icono', 'success');
         
    }

    public function restaurar(string $id)
    {
        $usuario = User::withTrashed()->findOrFail($id);
        $usuario->restore();

        $usuario->estado = 'Activo';
        $usuario->save();

        return redirect()->route('admin.usuarios.index')
            ->with('mensaje', 'Usuario restaurado correctamente')
            ->with('icono', 'success');
    }
}
