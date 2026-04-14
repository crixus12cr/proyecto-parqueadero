<?php
// app/Livewire/Admin/GestionTarjetas/TarjetasIndex.php

namespace App\Livewire\Admin\GestionTarjetas;

use Livewire\Component;
use Livewire\WithPagination;
use App\Repositories\Eloquent\TarjetaRfidRepository;
use Carbon\Carbon;

class TarjetasIndex extends Component
{
    use WithPagination;

    protected $tarjetaRepository;
    
    // Propiedades para el modal
    public $showModal = false;
    public $modalTitle = '';
    public $modalAction = '';
    
    // Propiedades del formulario
    public $tarjetaId = null;
    public $tarjeta_id = null;
    public $uid_tarjeta = '';
    public $user_id = '';
    public $fecha_asignacion = '';
    public $fecha_vencimiento = '';
    public $estado = 'activa';
    public $observaciones = '';
    
    // Propiedades para lector RFID
    public $lectorActivo = false;
    public $lectorMensaje = '';
    public $uidLeido = '';
    
    // Propiedades para búsqueda de usuarios
    public $searchUsuario = '';
    public $usuariosFiltrados = [];
    public $mostrarDropdown = false;
    public $usuarioSeleccionado = null;
    
    // Propiedades para filtros
    public $search = '';
    public $filtroEstado = '';
    public $filtroUsuario = '';
    
    // Listas
    public $usuarios = [];
    public $estados = [];
    
    // Listeners
    protected $listeners = [
        'confirmarEliminar',
        'tarjetaActualizada' => '$refresh',
        'tarjetaCreada' => '$refresh',
        'procesarUidLeido',
        'resetearLector'
    ];

    public function __construct()
    {
        $this->tarjetaRepository = app(TarjetaRfidRepository::class);
    }

    public function mount()
    {
        $this->cargarListas();
        $this->filtrarUsuarios();
        $this->fecha_asignacion = Carbon::now()->format('Y-m-d');
    }

    public function render()
    {
        $tarjetas = $this->cargarTarjetas();
        
        return view('livewire.admin.gestion-tarjetas.tarjetas-index', [
            'tarjetas' => $tarjetas
        ])->layout('layouts.app');
    }

    protected function cargarListas()
    {
        $this->usuarios = $this->tarjetaRepository->getUsuarios();
        $this->estados = $this->tarjetaRepository->getEstados();
    }

    protected function cargarTarjetas()
    {
        if ($this->search) {
            return $this->tarjetaRepository->buscar($this->search);
        }
        
        if ($this->filtroEstado) {
            return $this->tarjetaRepository->filtrarPorEstado($this->filtroEstado);
        }
        
        if ($this->filtroUsuario) {
            return $this->tarjetaRepository->filtrarPorUsuario($this->filtroUsuario);
        }
        
        return $this->tarjetaRepository->paginar(10);
    }

    /**
     * Procesar el UID leído desde el lector
     */
    public function procesarUidLeido($uid)
    {
        $this->uidLeido = $uid;
        $this->uid_tarjeta = $uid;
        
        // Verificar si ya existe
        $existe = $this->tarjetaRepository->obtenerPorUid($uid);
        
        if ($existe) {
            $this->dispatch('alerta', [
                'titulo' => 'Tarjeta ya registrada',
                'texto' => "La tarjeta con UID {$uid} ya está registrada en el sistema.",
                'icono' => 'warning'
            ]);
            $this->uid_tarjeta = '';
        } else {
            $this->dispatch('alerta', [
                'titulo' => 'Tarjeta detectada',
                'texto' => "Se ha detectado la tarjeta con UID: {$uid}",
                'icono' => 'success'
            ]);
        }
    }

    /**
     * Resetear el estado del lector
     */
    public function resetearLector()
    {
        $this->lectorActivo = false;
        $this->lectorMensaje = '';
        $this->uidLeido = '';
    }

    /**
     * Activar/desactivar el lector desde la vista
     */
    public function toggleLector()
    {
        $this->lectorActivo = !$this->lectorActivo;
        
        if ($this->lectorActivo) {
            $this->lectorMensaje = '✅ Escuchando lector RFID... Acerca una tarjeta';
        } else {
            $this->lectorMensaje = '';
        }
    }

    /**
     * Filtrar usuarios para el dropdown
     */
    public function filtrarUsuarios()
    {
        if (empty($this->searchUsuario)) {
            $this->usuariosFiltrados = $this->usuarios->take(10);
        } else {
            $this->usuariosFiltrados = $this->usuarios->filter(function($usuario) {
                return stripos($usuario->name, $this->searchUsuario) !== false || 
                       stripos($usuario->numero_documento, $this->searchUsuario) !== false;
            })->take(10);
        }
    }

    /**
     * Seleccionar un usuario del dropdown
     */
    public function seleccionarUsuario($usuarioId)
    {
        $usuario = $this->usuarios->firstWhere('id', $usuarioId);
        if ($usuario) {
            $this->user_id = $usuario->id;
            $this->usuarioSeleccionado = $usuario;
            $this->searchUsuario = $usuario->name . ' (' . $usuario->numero_documento . ')';
            $this->mostrarDropdown = false;
            $this->filtroUsuario = $usuario->id;
        }
    }

    /**
     * Limpiar la selección de usuario
     */
    public function limpiarUsuario()
    {
        $this->user_id = '';
        $this->usuarioSeleccionado = null;
        $this->limpiarBusqueda();
    }

    /**
     * Limpiar búsqueda y cerrar dropdown
     */
    public function limpiarBusqueda()
    {
        $this->searchUsuario = '';
        $this->filtrarUsuarios();
        $this->mostrarDropdown = false;
    }

    /**
     * Cuando se actualiza la búsqueda de usuarios
     */
    public function updatedSearchUsuario()
    {
        $this->filtrarUsuarios();
        
        if (strlen($this->searchUsuario) > 0 && count($this->usuariosFiltrados) > 0) {
            $this->mostrarDropdown = true;
        } else {
            $this->mostrarDropdown = false;
        }
    }

    public function abrirModalNuevo()
    {
        $this->resetearFormulario();
        $this->fecha_asignacion = Carbon::now()->format('Y-m-d');
        $this->modalTitle = 'Registrar Tarjeta RFID';
        $this->modalAction = 'crear';
        $this->showModal = true;
        $this->dispatch('resetearLector');
    }

    public function abrirModalEditar($id)
    {
        $tarjeta = $this->tarjetaRepository->findById($id);
        
        $this->tarjetaId = $tarjeta->id;
        $this->tarjeta_id = $tarjeta->id;
        $this->uid_tarjeta = $tarjeta->uid_tarjeta;
        $this->user_id = $tarjeta->user_id;
        $this->fecha_asignacion = $tarjeta->fecha_asignacion ? Carbon::parse($tarjeta->fecha_asignacion)->format('Y-m-d') : '';
        $this->fecha_vencimiento = $tarjeta->fecha_vencimiento ? Carbon::parse($tarjeta->fecha_vencimiento)->format('Y-m-d') : '';
        $this->estado = $tarjeta->estado;
        $this->observaciones = $tarjeta->observaciones;
        
        // Cargar usuario seleccionado
        if ($tarjeta->user_id) {
            $usuario = $this->usuarios->firstWhere('id', $tarjeta->user_id);
            if ($usuario) {
                $this->usuarioSeleccionado = $usuario;
                $this->searchUsuario = $usuario->name . ' (' . $usuario->numero_documento . ')';
            }
        }
        
        $this->modalTitle = 'Editar Tarjeta RFID';
        $this->modalAction = 'editar';
        $this->showModal = true;
        
        // Desactivar lector al editar
        if ($this->lectorActivo) {
            $this->toggleLector();
        }
    }

    public function crear()
    {
        $rules = [
            'uid_tarjeta' => 'required|string|max:50|unique:tarjeta_rfids,uid_tarjeta',
            'user_id' => 'nullable|exists:users,id',
            'fecha_asignacion' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_asignacion',
            'estado' => 'required|in:activa,inactiva,perdida,dañada,vencida',
            'observaciones' => 'nullable|string'
        ];
        
        $messages = [
            'uid_tarjeta.required' => 'El UID de la tarjeta es obligatorio.',
            'uid_tarjeta.unique' => 'Esta tarjeta ya está registrada.',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento debe ser posterior o igual a la fecha de asignación.',
            'estado.required' => 'El estado es obligatorio.',
        ];
        
        $validated = $this->validate($rules, $messages);
        
        $data = [
            'uid_tarjeta' => strtoupper($this->uid_tarjeta),
            'user_id' => $this->user_id ?: null,
            'fecha_asignacion' => $this->fecha_asignacion ?: null,
            'fecha_vencimiento' => $this->fecha_vencimiento ?: null,
            'estado' => $this->estado,
            'observaciones' => $this->observaciones,
        ];
        
        $this->tarjetaRepository->create($data);
        
        $this->dispatch('alerta', [
            'titulo' => '¡Creada!',
            'texto' => 'La tarjeta RFID se ha registrado correctamente.',
            'icono' => 'success'
        ]);
        
        $this->cerrarModal();
        $this->dispatch('tarjetaCreada');
    }

    public function editar()
    {
        $this->tarjeta_id = $this->tarjetaId;
        
        $rules = [
            'uid_tarjeta' => 'required|string|max:50|unique:tarjeta_rfids,uid_tarjeta,' . $this->tarjetaId,
            'user_id' => 'nullable|exists:users,id',
            'fecha_asignacion' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_asignacion',
            'estado' => 'required|in:activa,inactiva,perdida,dañada,vencida',
            'observaciones' => 'nullable|string'
        ];
        
        $messages = [
            'uid_tarjeta.required' => 'El UID de la tarjeta es obligatorio.',
            'uid_tarjeta.unique' => 'Esta tarjeta ya está registrada.',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento debe ser posterior o igual a la fecha de asignación.',
            'estado.required' => 'El estado es obligatorio.',
        ];
        
        $validated = $this->validate($rules, $messages);
        
        $data = [
            'uid_tarjeta' => strtoupper($this->uid_tarjeta),
            'user_id' => $this->user_id ?: null,
            'fecha_asignacion' => $this->fecha_asignacion ?: null,
            'fecha_vencimiento' => $this->fecha_vencimiento ?: null,
            'estado' => $this->estado,
            'observaciones' => $this->observaciones,
        ];
        
        $this->tarjetaRepository->update($this->tarjetaId, $data);
        
        $this->dispatch('alerta', [
            'titulo' => '¡Actualizada!',
            'texto' => 'La tarjeta RFID se ha actualizado correctamente.',
            'icono' => 'success'
        ]);
        
        $this->cerrarModal();
        $this->dispatch('tarjetaActualizada');
    }

    public function confirmarEliminar($id)
    {
        $tarjeta = $this->tarjetaRepository->findById($id);
        
        $this->dispatch('confirmar-eliminar', [
            'titulo' => '¿Eliminar tarjeta?',
            'texto' => "¿Estás seguro de eliminar la tarjeta con UID {$tarjeta->uid_tarjeta}? Esta acción no se puede deshacer.",
            'icono' => 'warning',
            'id' => $id
        ]);
    }

    public function eliminar($id)
    {
        try {
            $this->tarjetaRepository->delete($id);
            
            $this->dispatch('alerta', [
                'titulo' => '¡Eliminada!',
                'texto' => 'La tarjeta RFID se ha eliminado correctamente.',
                'icono' => 'success'
            ]);
            
            $this->resetPage();
            
        } catch (\Exception $e) {
            $this->dispatch('alerta', [
                'titulo' => '¡Error!',
                'texto' => 'Error al eliminar: ' . $e->getMessage(),
                'icono' => 'error'
            ]);
        }
    }

    public function resetearFormulario()
    {
        $this->reset([
            'tarjetaId', 'tarjeta_id', 'uid_tarjeta', 'user_id',
            'fecha_asignacion', 'fecha_vencimiento', 'estado', 'observaciones',
            'uidLeido'
        ]);
        $this->limpiarUsuario();
        $this->estado = 'activa';
        $this->resetValidation();
        
        // Desactivar lector al resetear
        if ($this->lectorActivo) {
            $this->lectorActivo = false;
            $this->lectorMensaje = '';
        }
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->resetearFormulario();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedFiltroEstado()
    {
        $this->resetPage();
    }

    public function updatedFiltroUsuario()
    {
        $this->resetPage();
    }
}