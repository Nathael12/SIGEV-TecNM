<?php

namespace Database\Seeders;

use App\Models\User; //Agregado para User
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; //Agregar fachada para implementar Hash

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@ejemplo.com'], // Condición para buscar si ya existe
            [
            'name' => 'admin',
            //'email' => 'admin@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin123'), //Encriptación obligatoria
            'nombre_usuario' => 'Administrador',
            'apellido_paterno' => 'Uc',
            'apellido_materno' => 'Coh',
            'correo_usuario' => 'angel@gmail.com',
            'telefono_usuario' => '9961085232',
            'estado' => 'Activo',
            // Si usas un sistema de roles simple por columna (ej. enum o string):
            // 'role' => 'admin', 
        ])->assignRole('Administrador');
    }
}
