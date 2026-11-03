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
                'name' => 'Administrador',
                'password' => Hash::make('admin12'), // Encriptación obligatoria
                // Si usas un sistema de roles simple por columna (ej. enum o string):
                // 'role' => 'admin', 
                'email_verified_at' => now(),
            ]
        );
    }
}
