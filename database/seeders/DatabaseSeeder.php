<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Los roles ahora se crean en RoleSeeder (con sus permisos)
        // Role::create(['name' => 'Administrador']);
        // Role::create(['name' => 'Encargado Flotilla']);

        /* Por defecto
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        */

        /* Creado por angel
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
        */

        // El orden importa
        $this->call([
            RoleSeeder::class,
            AdminUserSeeder::class,
            ConfiguracionAlertasSeeder::class,
            AjusteSeeder::class,
        ]);
    }
}
