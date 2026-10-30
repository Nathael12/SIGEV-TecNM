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
    Route::view('usuarios', 'admin.usuarios.index')->name('usuarios.index');


    //rutas para los roles

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';

//rutas para el admin
Route::get('/dashboard', function () { return redirect()->route('admin.index'); })->name('dashboard')->middleware('auth');
Route::get('/admin', [App\Http\Controllers\AdminController::class, 'index'])->name('admin.index')->middleware('auth');

//rutas para ajustes
Route::get('/admin/ajustes', [App\Http\Controllers\AjusteController::class, 'index'])->name('admin.ajustes.index')->middleware('auth');
Route::post('/admin/ajustes', [App\Http\Controllers\AjusteController::class, 'store'])->name('admin.ajustes.store')->middleware('auth');

//rutas para roles
Route::get('/admin/roles', [App\Http\Controllers\RoleController::class, 'index'])->name('admin.roles.index')->middleware('auth');
Route::get('/admin/roles/create', [App\Http\Controllers\RoleController::class, 'create'])->name('admin.roles.create')->middleware('auth');
Route::post('/admin/roles/create', [App\Http\Controllers\RoleController::class, 'store'])->name('admin.roles.create')->middleware('auth');