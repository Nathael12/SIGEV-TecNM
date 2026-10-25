<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/*
Route::get('/', function () {
    return view('welcome');
});
*/

Route::redirect('/', '/login');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

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
