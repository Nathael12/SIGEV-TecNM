<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AjusteController extends Controller
{
    public function index(){
        return view('admin.ajustes.index');
    }

    public function store(Request $request){
        //validar los datos
        $request->validate([
            'nombre' => 'required|string|max:255',
            /*'descripcion' => 'nullable|string|max:255',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'divisa' => 'required|string|max:10',
            'logo' => 'nullable|string|max:255',
            'web' => 'nullable|string|max:255',
            'interes' => 'required|numeric|min:0|max:100',
            'mora' => 'required|numeric|min:0|max:100',*/
        ]);
    }
}