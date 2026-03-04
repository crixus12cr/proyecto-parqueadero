<?php

use App\Livewire\Admin\Administracion\ParametrosIndex;
use App\Livewire\Admin\Administracion\RespaldosIndex;
use App\Livewire\Admin\Administracion\RolesIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::get('admin/roles', RolesIndex::class)
        ->name('admin.roles')
        ->middleware('rol:Super Administrador,Administrador');

    Route::get('admin/respaldos', RespaldosIndex::class)
        ->name('admin.respaldos')
        ->middleware('rol:Super Administrador,Administrador');
    
    Route::get('admin/parametros', ParametrosIndex::class)
        ->name('admin.parametros')
        ->middleware('rol:Super Administrador,Administrador');Route::get('admin/parametros', ParametrosIndex::class)
        ->name('admin.parametros')
        ->middleware('rol:Super Administrador,Administrador');
});

require __DIR__.'/settings.php';
