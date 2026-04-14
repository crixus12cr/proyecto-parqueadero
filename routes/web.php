<?php

use App\Livewire\Admin\Administracion\ParametrosIndex;
use App\Livewire\Admin\Administracion\RespaldosIndex;
use App\Livewire\Admin\Administracion\RolesIndex;
use App\Livewire\Admin\GestionTarjetas\AsignarTarjeta;
use App\Livewire\Admin\GestionTarjetas\ReemplazarTarjeta;
use App\Livewire\Admin\GestionTarjetas\TarjetasIndex;
use App\Livewire\Admin\GestionTarjetas\TarjetasPorVencer;
use App\Livewire\Admin\GestionUsuario\UsuariosIndex;
use App\Livewire\Admin\GestionVehiculo\VehiculosIndex;
use Illuminate\Support\Facades\Route;

// Route::view('/', 'welcome')->name('home');
Route::redirect('/', '/login');

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
        ->middleware('rol:Super Administrador,Administrador');
    Route::get('admin/parametros', ParametrosIndex::class)
        ->name('admin.parametros')
        ->middleware('rol:Super Administrador,Administrador');

    Route::get('admin/gestion-usuario/usuarios', UsuariosIndex::class)
        ->name('admin.gestion-usuario.usuarios')
        ->middleware('rol:Super Administrador,Administrador');

    Route::get('admin/gestion-vehiculo/vehiculos', VehiculosIndex::class)
        ->name('admin.gestion-vehiculo.vehiculos')
        ->middleware('rol:Super Administrador,Administrador,Vigilante');

    Route::get('admin/gestion-tarjetas/tarjetas', TarjetasIndex::class)
        ->name('admin.gestion-tarjetas.tarjetas')
        ->middleware('rol:Super Administrador,Administrador,Vigilante');

    Route::get('admin/gestion-tarjetas/asignar', AsignarTarjeta::class)
        ->name('admin.gestion-tarjetas.asignar')
        ->middleware('rol:Super Administrador,Administrador');

    Route::get('admin/gestion-tarjetas/reemplazar', ReemplazarTarjeta::class)
        ->name('admin.gestion-tarjetas.reemplazar')
        ->middleware('rol:Super Administrador,Administrador');
    
    Route::get('admin/gestion-tarjetas/por-vencer', TarjetasPorVencer::class)
        ->name('admin.gestion-tarjetas.por-vencer')
        ->middleware('rol:Super Administrador,Administrador,Vigilante');
});

require __DIR__ . '/settings.php';
