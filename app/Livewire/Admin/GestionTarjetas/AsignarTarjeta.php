<?php
// app/Livewire/Admin/GestionTarjetas/AsignarTarjeta.php

namespace App\Livewire\Admin\GestionTarjetas;

use Livewire\Component;
use App\Repositories\Eloquent\TarjetaRfidRepository;
use Carbon\Carbon;

class AsignarTarjeta extends Component
{
    // Propiedades para búsqueda de tarjeta
    public $uid_tarjeta = '';
    public $tarjetaEncontrada = null;
    
    // Propiedades para lector RFID
    public $lectorActivo = false;
    public $lectorMensaje = '';
    
    // Propiedades para selección de usuario
    public $searchUsuario = '';
    public $usuariosFiltrados = [];
    public $mostrarDropdown = false;
    public $usuarioSeleccionado = null;
    public $user_id = '';
    
    // Propiedades para fechas
    public $fecha_asignacion = '';
    public $fecha_vencimiento = '';
    public $observaciones = '';
    
    // Listas
    public $usuarios = [];
    
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
        $this->cargarUsuarios();
        $this->fecha_asignacion = Carbon::now()->format('Y-m-d');
    }

    public function render()
    {
        return view('livewire.admin.gestion-tarjetas.asignar-tarjeta')
            ->layout('layouts.app');
    }

    protected function cargarUsuarios()
    {
        $this->usuarios = $this->tarjetaRepository->getUsuarios();
        $this->filtrarUsuarios();
    }

    public function filtrarUsuarios()
    {
        if (empty($this->searchUsuario)) {
            $this->usuariosFiltrados = $this->usuarios->take(10);
        } else {
            $this->usuariosFiltrados = $this->usuarios->filter(function($usuario) {
                return stripos($usuario->name, $this->searchUsuario) !== false || 
                       stripos($usuario->numero_documento, $this->searchUsuario) !== false ||
                       stripos($usuario->email, $this->searchUsuario) !== false;
            })->take(10);
        }
    }

    public function seleccionarUsuario($usuarioId)
    {
        $usuario = $this->usuarios->firstWhere('id', $usuarioId);
        if ($usuario) {
            $this->user_id = $usuario->id;
            $this->usuarioSeleccionado = $usuario;
            $this->searchUsuario = $usuario->name . ' (' . $usuario->numero_documento . ')';
            $this->mostrarDropdown = false;
        }
    }

    public function limpiarUsuario()
    {
        $this->user_id = '';
        $this->usuarioSeleccionado = null;
        $this->searchUsuario = '';
        $this->filtrarUsuarios();
    }

    public function updatedSearchUsuario()
    {
        $this->filtrarUsuarios();
        $this->mostrarDropdown = strlen($this->searchUsuario) > 0 && count($this->usuariosFiltrados) > 0;
    }

    public function limpiarBusqueda()
    {
        $this->searchUsuario = '';
        $this->filtrarUsuarios();
        $this->mostrarDropdown = false;
    }

    public function toggleLector()
    {
        $this->lectorActivo = !$this->lectorActivo;
        
        if ($this->lectorActivo) {
            $this->lectorMensaje = '✅ Escuchando lector RFID... Acerca una tarjeta';
        } else {
            $this->lectorMensaje = '';
        }
    }

    public function procesarUidLeido($uid)
    {
        $this->uid_tarjeta = $uid;
        $this->buscarTarjeta();
    }

    public function buscarTarjeta()
    {
        if (empty($this->uid_tarjeta)) {
            $this->dispatch('alerta', [
                'titulo' => 'Campo vacío',
                'texto' => 'Ingrese el UID de la tarjeta o active el lector automático.',
                'icono' => 'warning'
            ]);
            return;
        }
        
        $uid = strtoupper($this->uid_tarjeta);
        $tarjeta = $this->tarjetaRepository->obtenerPorUid($uid);
        
        if (!$tarjeta) {
            $this->dispatch('alerta', [
                'titulo' => 'Tarjeta no encontrada',
                'texto' => "No existe una tarjeta con UID {$uid} en el sistema. Debe registrarla primero en el Inventario.",
                'icono' => 'error'
            ]);
            $this->tarjetaEncontrada = null;
            return;
        }
        
        if ($tarjeta->user_id) {
            $this->dispatch('alerta', [
                'titulo' => 'Tarjeta ya asignada',
                'texto' => "La tarjeta ya está asignada a: {$tarjeta->usuario->name}",
                'icono' => 'warning'
            ]);
            $this->tarjetaEncontrada = null;
            return;
        }
        
        $this->tarjetaEncontrada = $tarjeta;
        
        $this->dispatch('alerta', [
            'titulo' => 'Tarjeta disponible',
            'texto' => "La tarjeta con UID {$uid} está disponible para asignar.",
            'icono' => 'success'
        ]);
    }

    public function asignar()
    {
        if (!$this->tarjetaEncontrada) {
            $this->dispatch('alerta', [
                'titulo' => 'Error',
                'texto' => 'Primero debe buscar una tarjeta válida y disponible.',
                'icono' => 'error'
            ]);
            return;
        }
        
        if (!$this->user_id) {
            $this->dispatch('alerta', [
                'titulo' => 'Error',
                'texto' => 'Debe seleccionar un usuario para asignar la tarjeta.',
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
        
        // Solo actualizar los campos de asignación
        $this->tarjetaRepository->update($this->tarjetaEncontrada->id, [
            'user_id' => $this->user_id,
            'fecha_asignacion' => $this->fecha_asignacion,
            'fecha_vencimiento' => $this->fecha_vencimiento ?: null,
            'observaciones' => $this->observaciones,
            'estado' => 'activa'
        ]);
        
        $this->dispatch('alerta', [
            'titulo' => '¡Asignada!',
            'texto' => 'La tarjeta se ha asignado correctamente.',
            'icono' => 'success'
        ]);
        
        $this->resetearFormulario();
    }

    public function resetearFormulario()
    {
        $this->reset([
            'uid_tarjeta', 'tarjetaEncontrada', 'user_id', 'usuarioSeleccionado',
            'searchUsuario', 'fecha_vencimiento', 'observaciones'
        ]);
        $this->fecha_asignacion = Carbon::now()->format('Y-m-d');
        $this->filtrarUsuarios();
        $this->resetValidation();
        
        if ($this->lectorActivo) {
            $this->lectorActivo = false;
            $this->lectorMensaje = '';
        }
    }
}