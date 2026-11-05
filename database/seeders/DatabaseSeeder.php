<?php

namespace Database\Seeders;

use App\Models\Ajuste;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        Role::create(['name' => 'Administrador']);
        Role::create(['name' => 'Encargado Flotilla']);

        /*User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);*/
        /*
        // Llamamos al seeder del administrador
        $this->call([
            AdminUserSeeder::class,
        */

        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'administrador@gmail.com',

            'nombre_usuario' => 'Angel',
            'apellido_paterno' => 'Uc',
            'apellido_materno' => 'Coh',
            'correo_usuario' => 'angel@gmail.com',
            'telefono_usuario' => '9961085232',
            'estado' => 'Activo',
        ])->assignRole('Administrador');

        Ajuste::create([
            'nombre' => 'Mi Empresa',   
            'descripcion' => 'Descripcion de la empresa',
            'direccion' => 'Av. Principal',
            'telefono' => '9999999999',
            'email' => 'info@miempresa@.com',
            'divisa' => 'Bs',
            'logo' => null,
            'web' => 'https://www.miempresa.com',
            'interes' => 10.00,
            'mora' => 2.00,            
        ]);
    }
}
