<?php
// app/Livewire/Admin/GestionTarjetas/ReemplazarTarjeta.php

namespace App\Livewire\Admin\GestionTarjetas;

use Livewire\Component;
use App\Repositories\Eloquent\TarjetaRfidRepository;
use Carbon\Carbon;

class ReemplazarTarjeta extends Component
{
    // Propiedades para tarjeta vieja
    public $uid_tarjeta_vieja = '';
    public $tarjetaVieja = null;
    
    // Propiedades para tarjeta nueva
    public $uid_tarjeta_nueva = '';
    public $tarjetaNueva = null;
    
    // Propiedades para lector RFID
    public $lectorActivoVieja = false;
    public $lectorActivoNueva = false;
    public $lectorMensaje = '';
    public $modoActual = 'vieja'; // 'vieja' o 'nueva'
    
    // Propiedades para fechas
    public $fecha_asignacion = '';
    public $fecha_vencimiento = '';
    public $observaciones = '';
    
    // Estados del proceso
    public $paso = 1; // 1: leer tarjeta vieja, 2: leer tarjeta nueva, 3: confirmar
    
    protected $tarjetaRepository;
    
    protected $listeners = [
        'procesarUidLeido'
    ];

    public function __construct()
    {
        $this->tarjetaRepository = app(TarjetaRfidRepository::class);
    }

    public function mount()
    {
        $this->fecha_asignacion = Carbon::now()->format('Y-m-d');
    }

    public function render()
    {
        return view('livewire.admin.gestion-tarjetas.reemplazar-tarjeta')
            ->layout('layouts.app');
    }

    /**
     * Activar lector para tarjeta vieja
     */
    public function activarLectorVieja()
    {
        $this->resetearLectores();
        $this->lectorActivoVieja = true;
        $this->modoActual = 'vieja';
        $this->lectorMensaje = '✅ Acerca la tarjeta VIEJA (dañada/perdida) al lector USB';
    }

    /**
     * Activar lector para tarjeta nueva
     */
    public function activarLectorNueva()
    {
        $this->resetearLectores();
        $this->lectorActivoNueva = true;
        $this->modoActual = 'nueva';
        $this->lectorMensaje = '✅ Acerca la tarjeta NUEVA al lector USB';
    }

    /**
     * Resetear todos los lectores
     */
    public function resetearLectores()
    {
        $this->lectorActivoVieja = false;
        $this->lectorActivoNueva = false;
        $this->lectorMensaje = '';
    }

    /**
     * Procesar el UID leído desde el lector
     */
    public function procesarUidLeido($uid)
    {
        if ($this->modoActual === 'vieja') {
            $this->uid_tarjeta_vieja = $uid;
            $this->buscarTarjetaVieja();
        } elseif ($this->modoActual === 'nueva') {
            $this->uid_tarjeta_nueva = $uid;
            $this->buscarTarjetaNueva();
        }
    }

    /**
     * Buscar tarjeta vieja manualmente
     */
    public function buscarTarjetaVieja()
    {
        if (empty($this->uid_tarjeta_vieja)) {
            $this->dispatch('alerta', [
                'titulo' => 'Campo vacío',
                'texto' => 'Ingrese el UID de la tarjeta vieja o active el lector.',
                'icono' => 'warning'
            ]);
            return;
        }
        
        $uid = strtoupper($this->uid_tarjeta_vieja);
        $tarjeta = $this->tarjetaRepository->obtenerPorUid($uid);
        
        if (!$tarjeta) {
            $this->dispatch('alerta', [
                'titulo' => 'Tarjeta no encontrada',
                'texto' => "No existe una tarjeta con UID {$uid} en el sistema.",
                'icono' => 'error'
            ]);
            $this->tarjetaVieja = null;
            return;
        }
        
        $this->tarjetaVieja = $tarjeta;
        $this->paso = 2;
        
        $this->dispatch('alerta', [
            'titulo' => 'Tarjeta encontrada',
            'texto' => "Tarjeta: {$tarjeta->uid_tarjeta}" . ($tarjeta->usuario ? " - Propietario: {$tarjeta->usuario->name}" : " - Sin asignar"),
            'icono' => 'success'
        ]);
        
        $this->resetearLectores();
    }

    /**
     * Buscar tarjeta nueva manualmente
     */
    public function buscarTarjetaNueva()
    {
        if (empty($this->uid_tarjeta_nueva)) {
            $this->dispatch('alerta', [
                'titulo' => 'Campo vacío',
                'texto' => 'Ingrese el UID de la tarjeta nueva o active el lector.',
                'icono' => 'warning'
            ]);
            return;
        }
        
        $uid = strtoupper($this->uid_tarjeta_nueva);
        $tarjeta = $this->tarjetaRepository->obtenerPorUid($uid);
        
        if ($tarjeta) {
            $this->dispatch('alerta', [
                'titulo' => 'Tarjeta ya registrada',
                'texto' => "La tarjeta con UID {$uid} ya existe en el sistema. No se puede reemplazar con una tarjeta existente.",
                'icono' => 'error'
            ]);
            $this->tarjetaNueva = null;
            return;
        }
        
        // Verificar que no sea la misma que la vieja
        if ($this->tarjetaVieja && $this->uid_tarjeta_vieja === $uid) {
            $this->dispatch('alerta', [
                'titulo' => 'Tarjeta duplicada',
                'texto' => 'La tarjeta nueva no puede ser la misma que la tarjeta vieja.',
                'icono' => 'error'
            ]);
            $this->tarjetaNueva = null;
            return;
        }
        
        // La tarjeta nueva no existe, hay que crearla
        $this->tarjetaNueva = (object) [
            'uid_tarjeta' => $uid,
            'nueva' => true
        ];
        
        $this->paso = 3;
        
        $this->dispatch('alerta', [
            'titulo' => 'Tarjeta nueva detectada',
            'texto' => "Se creará una nueva tarjeta con UID: {$uid}",
            'icono' => 'success'
        ]);
        
        $this->resetearLectores();
    }

    /**
     * Resetear formulario
     */
    public function resetearFormulario()
    {
        $this->reset([
            'uid_tarjeta_vieja', 'tarjetaVieja', 'uid_tarjeta_nueva', 'tarjetaNueva',
            'fecha_vencimiento', 'observaciones', 'paso'
        ]);
        $this->paso = 1;
        $this->fecha_asignacion = Carbon::now()->format('Y-m-d');
        $this->resetearLectores();
        $this->resetValidation();
    }

    /**
     * Confirmar y realizar el reemplazo
     */
    public function confirmarReemplazo()
    {
        if (!$this->tarjetaVieja) {
            $this->dispatch('alerta', [
                'titulo' => 'Error',
                'texto' => 'No se ha detectado la tarjeta vieja.',
                'icono' => 'error'
            ]);
            return;
        }
        
        if (!$this->tarjetaNueva) {
            $this->dispatch('alerta', [
                'titulo' => 'Error',
                'texto' => 'No se ha detectado la tarjeta nueva.',
                'icono' => 'error'
            ]);
            return;
        }
        
        $rules = [
            'fecha_asignacion' => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_asignacion',
        ];
        
        $messages = [
            'fecha_asignacion.required' => 'La fecha de asignación es obligatoria.',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento debe ser posterior o igual a la fecha de asignación.',
        ];
        
        $this->validate($rules, $messages);
        
        try {
            // Marcar la tarjeta vieja como dañada/reemplazada
            $this->tarjetaRepository->update($this->tarjetaVieja->id, [
                'estado' => 'reemplazada',
                'observaciones' => ($this->tarjetaVieja->observaciones ? $this->tarjetaVieja->observaciones . "\n" : '') . "Reemplazada por tarjeta: {$this->tarjetaNueva->uid_tarjeta} - " . now()->format('d/m/Y H:i'),
            ]);
            
            // Crear la nueva tarjeta con los datos de la vieja
            $this->tarjetaRepository->create([
                'uid_tarjeta' => $this->tarjetaNueva->uid_tarjeta,
                'user_id' => $this->tarjetaVieja->user_id,
                'fecha_asignacion' => $this->fecha_asignacion,
                'fecha_vencimiento' => $this->fecha_vencimiento ?: null,
                'estado' => 'activa',
                'observaciones' => $this->observaciones ?: "Reemplazo de tarjeta: Tarjeta original {$this->tarjetaVieja->uid_tarjeta}",
            ]);
            
            $this->dispatch('alerta', [
                'titulo' => '¡Reemplazo completado!',
                'texto' => 'La tarjeta ha sido reemplazada exitosamente.',
                'icono' => 'success'
            ]);
            
            $this->resetearFormulario();
            
        } catch (\Exception $e) {
            $this->dispatch('alerta', [
                'titulo' => 'Error',
                'texto' => 'Ocurrió un error al reemplazar la tarjeta: ' . $e->getMessage(),
                'icono' => 'error'
            ]);
        }
    }

    /**
     * Volver al paso anterior
     */
    public function pasoAnterior()
    {
        if ($this->paso > 1) {
            $this->paso--;
            if ($this->paso === 1) {
                $this->tarjetaVieja = null;
                $this->uid_tarjeta_vieja = '';
            } elseif ($this->paso === 2) {
                $this->tarjetaNueva = null;
                $this->uid_tarjeta_nueva = '';
            }
        }
    }
}