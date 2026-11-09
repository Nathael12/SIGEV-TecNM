<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/*
Route::get('/', function () {
    return view('welcome');
});
*/

Route::redirect('/', '/login');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    // Mis rutas de admin       
    Route::redirect('usuarios', '/admin/usuarios')->name('usuarios.index');


    //rutas para los roles

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';

//rutas para admin
Route::get('/dashboard', function () { return redirect()->route('admin.index'); })->name('dashboard')->middleware(['auth', 'can:ver dashboard']);
Route::get('/admin', [App\Http\Controllers\AdminController::class, 'index'])->name('admin.index')->middleware(['auth', 'can:ver dashboard']);


//rutas para ajustes
Route::get('/admin/ajustes', [App\Http\Controllers\AjusteController::class, 'index'])->name('admin.ajustes.index')->middleware(['auth', 'can:ver ajustes']);
Route::post('/admin/ajustes', [App\Http\Controllers\AjusteController::class, 'store'])->name('admin.ajustes.store')->middleware(['auth', 'can:guardar ajustes']);

//rutas para roles
Route::get('/admin/roles', [App\Http\Controllers\RoleController::class, 'index'])->name('admin.roles.index')->middleware(['auth', 'can:ver roles']);
Route::get('/admin/roles/create', [App\Http\Controllers\RoleController::class, 'create'])->name('admin.roles.create')->middleware(['auth', 'can:ver formulario crear rol']);
Route::post('/admin/roles/create', [App\Http\Controllers\RoleController::class, 'store'])->name('admin.roles.create')->middleware(['auth', 'can:guardar rol']);
Route::get('/admin/rol/{id}', [App\Http\Controllers\RoleController::class, 'show'])->name('admin.roles.show')->middleware(['auth', 'can:ver detalle rol']);
Route::get('/admin/rol/{id}/edit', [App\Http\Controllers\RoleController::class, 'edit'])->name('admin.roles.edit')->middleware(['auth', 'can:ver formulario editar rol']);
Route::put('/admin/rol/{id}', [App\Http\Controllers\RoleController::class, 'update'])->name('admin.roles.update')->middleware(['auth', 'can:actualizar rol']);
Route::delete('/admin/rol/{id}', [App\Http\Controllers\RoleController::class, 'destroy'])->name('admin.roles.destroy')->middleware(['auth', 'can:eliminar rol']);


//rutas para usuarios

Route::get('/admin/usuarios', [App\Http\Controllers\UsuarioController::class, 'index'])->name('admin.usuarios.index')->middleware(['auth', 'can:ver usuarios']);
Route::get('/admin/usuarios/create', [App\Http\Controllers\UsuarioController::class, 'create'])->name('admin.usuarios.create')->middleware(['auth', 'can:ver formulario crear usuario']);
Route::post('/admin/usuarios/create', [App\Http\Controllers\UsuarioController::class, 'store'])->name('admin.usuarios.store')->middleware(['auth', 'can:guardar usuario']);
Route::get('/admin/usuario/{id}', [App\Http\Controllers\UsuarioController::class, 'show'])->name('admin.usuarios.show')->middleware(['auth', 'can:ver detalle usuario']);
Route::get('/admin/usuario/{id}/edit', [App\Http\Controllers\UsuarioController::class, 'edit'])->name('admin.usuarios.edit')->middleware(['auth', 'can:ver formulario editar usuario']);
Route::put('/admin/usuario/{id}', [App\Http\Controllers\UsuarioController::class, 'update'])->name('admin.usuarios.update')->middleware(['auth', 'can:actualizar usuario']);
Route::delete('/admin/usuario/{id}', [App\Http\Controllers\UsuarioController::class, 'destroy'])->name('admin.usuarios.destroy')->middleware(['auth', 'can:eliminar usuario']);
Route::post('/admin/usuario/{id}/restaurar', [App\Http\Controllers\UsuarioController::class, 'restaurar'])->name('admin.usuarios.restaurar')->middleware(['auth', 'can:restaurar usuario']);

//rutas para vehiculos

Route::get('/admin/vehiculos', [App\Http\Controllers\VehiculoController::class, 'index'])->name('admin.vehiculos.index')->middleware(['auth', 'can:ver vehiculos']);
Route::get('/admin/vehiculos/create', [App\Http\Controllers\VehiculoController::class, 'create'])->name('admin.vehiculos.create')->middleware(['auth', 'can:ver formulario crear vehiculo']);
Route::post('/admin/vehiculos', [App\Http\Controllers\VehiculoController::class, 'store'])->name('admin.vehiculos.store')->middleware(['auth', 'can:guardar vehiculo']);
Route::get('/admin/vehiculos/{id}', [App\Http\Controllers\VehiculoController::class, 'show'])->name('admin.vehiculos.show')->middleware(['auth','can:ver detalle vehiculo']);
Route::get('/admin/vehiculos/{id}/edit', [App\Http\Controllers\VehiculoController::class, 'edit'])->name('admin.vehiculos.edit')->middleware('auth');
Route::put('/admin/vehiculos/{id}', [App\Http\Controllers\VehiculoController::class, 'update'])->name('admin.vehiculos.update')->middleware('auth');
Route::delete('/admin/vehiculos/{id}', [App\Http\Controllers\VehiculoController::class, 'destroy'])->name('admin.vehiculos.destroy')->middleware('auth');

//rutas para mantenimientos
Route::get('/admin/mantenimientos', [App\Http\Controllers\MantenimientoController::class, 'index'])->name('admin.mantenimientos.index')->middleware(['auth', 'can:ver mantenimientos']);
Route::get('/admin/mantenimientos/create', [App\Http\Controllers\MantenimientoController::class, 'create'])->name('admin.mantenimientos.create')->middleware(['auth', 'can:ver formulario crear mantenimiento']);
Route::post('/admin/mantenimientos', [App\Http\Controllers\MantenimientoController::class, 'store'])->name('admin.mantenimientos.store')->middleware(['auth', 'can:guardar mantenimiento']);


//rutas para documentos
Route::get('/admin/documentos', [App\Http\Controllers\DocumentoController::class, 'index'])->name('admin.documentos.index')->middleware(['auth', 'can:ver documentos']);
Route::get('/admin/documentos/create', [App\Http\Controllers\DocumentoController::class, 'create'])->name('admin.documentos.create')->middleware(['auth', 'can:ver formulario crear documento']);
Route::post('/admin/documentos/create', [App\Http\Controllers\DocumentoController::class, 'store'])->name('admin.documentos.store')->middleware(['auth', 'can:guardar documento']);
Route::get('/admin/documento/{id}', [App\Http\Controllers\DocumentoController::class, 'show'])->name('admin.documentos.show')->middleware(['auth', 'can:ver detalle documento']);
Route::get('/admin/documento/{id}/edit', [App\Http\Controllers\DocumentoController::class, 'edit'])->name('admin.documentos.edit')->middleware(['auth', 'can:ver formulario editar documento']);
Route::put('/admin/documento/{id}', [App\Http\Controllers\DocumentoController::class, 'update'])->name('admin.documentos.update')->middleware(['auth', 'can:actualizar documento']);
Route::delete('/admin/documento/{id}', [App\Http\Controllers\DocumentoController::class, 'destroy'])->name('admin.documentos.destroy')->middleware(['auth', 'can:eliminar documento']);
Route::get('/admin/documento/{id}/descargar', [App\Http\Controllers\DocumentoController::class, 'descargar'])->name('admin.documentos.descargar')->middleware(['auth', 'can:descargar documento']);
Route::get('/admin/documento/{id}/archivo', [App\Http\Controllers\DocumentoController::class, 'archivo'])->name('admin.documentos.archivo')->middleware(['auth', 'can:ver detalle documento']);
