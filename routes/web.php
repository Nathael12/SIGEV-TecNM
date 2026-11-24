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
Route::post('/admin/alertas-mantenimiento/{alerta}/atender', [App\Http\Controllers\AlertaMantenimientoController::class, 'atender'])->name('admin.alertas.atender')->middleware(['auth', 'can:atender alertas mantenimiento']);
Route::delete('/admin/alertas-mantenimiento/{alerta}', [App\Http\Controllers\AlertaMantenimientoController::class, 'destroy'])->name('admin.alertas.destroy')->middleware(['auth', 'can:atender alertas mantenimiento']);


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
Route::get('/admin/vehiculos/{id}/edit', [App\Http\Controllers\VehiculoController::class, 'edit'])->name('admin.vehiculos.edit')->middleware('auth','can:ver formulario editar vehiculo');
Route::put('/admin/vehiculos/{id}', [App\Http\Controllers\VehiculoController::class, 'update'])->name('admin.vehiculos.update')->middleware('auth','can:actualizar vehiculo');
Route::delete('/admin/vehiculos/{id}', [App\Http\Controllers\VehiculoController::class, 'destroy'])->name('admin.vehiculos.destroy')->middleware('auth','can:eliminar vehiculo');

//rutas para mantenimientos
Route::get('/admin/mantenimientos', [App\Http\Controllers\MantenimientoController::class, 'index'])->name('admin.mantenimientos.index')->middleware(['auth', 'can:ver mantenimientos']);
Route::get('/admin/mantenimientos/create', [App\Http\Controllers\MantenimientoController::class, 'create'])->name('admin.mantenimientos.create')->middleware(['auth', 'can:ver formulario crear mantenimiento']);
Route::post('/admin/mantenimientos', [App\Http\Controllers\MantenimientoController::class, 'store'])->name('admin.mantenimientos.store')->middleware(['auth', 'can:guardar mantenimiento']);
Route::get('/admin/mantenimientos/{mantenimiento}/edit', [App\Http\Controllers\MantenimientoController::class, 'edit'])->name('admin.mantenimientos.edit')->middleware(['auth', 'can:editar mantenimiento']);
Route::put('/admin/mantenimientos/{mantenimiento}', [App\Http\Controllers\MantenimientoController::class, 'update'])->name('admin.mantenimientos.update')->middleware(['auth', 'can:actualizar mantenimiento']);
Route::post('/admin/mantenimientos/{mantenimiento}/finalizar', [App\Http\Controllers\MantenimientoController::class, 'finalizar'])->name('admin.mantenimientos.finalizar')->middleware(['auth', 'can:actualizar mantenimiento']);
Route::delete('/admin/mantenimientos/{mantenimiento}', [App\Http\Controllers\MantenimientoController::class, 'destroy'])->name('admin.mantenimientos.destroy')->middleware(['auth', 'can:eliminar mantenimiento']);


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

//rutas para asignaciones
Route::get('/admin/asignaciones', [App\Http\Controllers\AsignacionController::class, 'index'])->name('admin.asignaciones.index')->middleware(['auth', 'can:ver asignaciones']);
Route::get('/admin/asignaciones/create', [App\Http\Controllers\AsignacionController::class, 'create'])->name('admin.asignaciones.create')->middleware(['auth', 'can:ver formulario crear asignacion']);
Route::post('/admin/asignaciones/create', [App\Http\Controllers\AsignacionController::class, 'store'])->name('admin.asignaciones.store')->middleware(['auth', 'can:guardar asignacion']);
Route::get('/admin/asignacion/{id}', [App\Http\Controllers\AsignacionController::class, 'show'])->name('admin.asignaciones.show')->middleware(['auth', 'can:ver detalle asignacion']);
Route::get('/admin/asignacion/{id}/edit', [App\Http\Controllers\AsignacionController::class, 'edit'])->name('admin.asignaciones.edit')->middleware(['auth', 'can:ver formulario editar asignacion']);
Route::put('/admin/asignacion/{id}', [App\Http\Controllers\AsignacionController::class, 'update'])->name('admin.asignaciones.update')->middleware(['auth', 'can:actualizar asignacion']);
Route::get('/admin/asignacion/{id}/devolucion', [App\Http\Controllers\AsignacionController::class, 'devolucion'])->name('admin.asignaciones.devolucion')->middleware(['auth', 'can:ver formulario devolucion']);
Route::put('/admin/asignacion/{id}/devolucion', [App\Http\Controllers\AsignacionController::class, 'devolver'])->name('admin.asignaciones.devolver')->middleware(['auth', 'can:registrar devolucion']);

//rutas para reportes
Route::get('/admin/reportes', [App\Http\Controllers\ReporteController::class, 'index'])->name('admin.reportes.index')->middleware(['auth', 'can:ver reportes']);
Route::get('/admin/reportes/flotilla', [App\Http\Controllers\ReporteController::class, 'flotilla'])->name('admin.reportes.flotilla')->middleware(['auth', 'can:ver reportes']);
Route::get('/admin/reportes/asignaciones', [App\Http\Controllers\ReporteController::class, 'asignaciones'])->name('admin.reportes.asignaciones')->middleware(['auth', 'can:ver reportes']);
Route::get('/admin/reportes/flotilla/pdf', [App\Http\Controllers\ReporteController::class, 'flotillaPdf'])->name('admin.reportes.flotilla.pdf')->middleware(['auth', 'can:exportar reportes']);
Route::get('/admin/reportes/flotilla/excel', [App\Http\Controllers\ReporteController::class, 'flotillaExcel'])->name('admin.reportes.flotilla.excel')->middleware(['auth', 'can:exportar reportes']);
Route::get('/admin/reportes/asignaciones/pdf', [App\Http\Controllers\ReporteController::class, 'asignacionesPdf'])->name('admin.reportes.asignaciones.pdf')->middleware(['auth', 'can:exportar reportes']);
Route::get('/admin/reportes/asignaciones/excel', [App\Http\Controllers\ReporteController::class, 'asignacionesExcel'])->name('admin.reportes.asignaciones.excel')->middleware(['auth', 'can:exportar reportes']);
Route::get('/admin/reportes/mantenimientos', [App\Http\Controllers\ReporteController::class, 'mantenimientos'])->name('admin.reportes.mantenimientos')->middleware(['auth', 'can:ver reportes']);
Route::get('/admin/reportes/mantenimientos/pdf', [App\Http\Controllers\ReporteController::class, 'mantenimientosPdf'])->name('admin.reportes.mantenimientos.pdf')->middleware(['auth', 'can:exportar reportes']);
Route::get('/admin/reportes/mantenimientos/excel', [App\Http\Controllers\ReporteController::class, 'mantenimientosExcel'])->name('admin.reportes.mantenimientos.excel')->middleware(['auth', 'can:exportar reportes']);