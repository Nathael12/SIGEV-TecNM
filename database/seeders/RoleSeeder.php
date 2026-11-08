<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ---------- Roles ----------
        $admin     = Role::firstOrCreate(['name' => 'Administrador',      'guard_name' => 'web']);
        $encargado = Role::firstOrCreate(['name' => 'Encargado Flotilla', 'guard_name' => 'web']);

        // ---------- Permisos ----------
        $permisos = [
            // Dashboard
            'ver dashboard',

            // Ajustes
            'ver ajustes',
            'guardar ajustes',

            // Roles
            'ver roles',
            'ver formulario crear rol',
            'guardar rol',
            'ver detalle rol',
            'ver formulario editar rol',
            'actualizar rol',
            'eliminar rol',

            // Usuarios
            'ver usuarios',
            'ver formulario crear usuario',
            'guardar usuario',
            'ver detalle usuario',
            'ver formulario editar usuario',
            'actualizar usuario',
            'eliminar usuario',
            'restaurar usuario',

            // Vehiculo
            'ver vehiculos',
            'ver formulario crear vehiculo',
            'guardar vehiculo',
            'ver detalle vehiculo',

            // Documentos
            'ver documentos',
            'ver formulario crear documento',
            'guardar documento',
            'ver detalle documento',
            'ver formulario editar documento',
            'actualizar documento',
            'eliminar documento',
            'descargar documento',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // ---------- Asignación ----------
        // Administrador: todos los permisos
        $admin->syncPermissions(Permission::all());

        // Encargado Flotilla: dashboard, vehículos y documentos
        // (puede consultar, registrar y descargar documentos; no editarlos ni eliminarlos)
        $encargado->syncPermissions([
            'ver dashboard',

            'ver vehiculos',
            'ver formulario crear vehiculo',
            'guardar vehiculo',
            'ver detalle vehiculo',

            'ver documentos',
            'ver formulario crear documento',
            'guardar documento',
            'ver detalle documento',
            'descargar documento',
        ]);
    }
}