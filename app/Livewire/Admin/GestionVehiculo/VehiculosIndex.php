<?php

namespace App\Livewire\Admin\GestionVehiculo;

use Livewire\Component;
use Livewire\WithPagination;
use App\Repositories\Eloquent\VehiculoRepository;
use App\Http\Requests\Admin\GestionVehiculo\CrearVehiculoRequest;
use App\Http\Requests\Admin\GestionVehiculo\ActualizarVehiculoRequest;

class VehiculosIndex extends Component
{
    use WithPagination;

    protected $vehiculoRepository;
    
    // Propiedades para el modal
    public $showModal = false;
    public $modalTitle = '';
    public $modalAction = '';
    
    // Propiedades del formulario
    public $vehiculoId = null;
    public $vehiculo_id = null;
    public $user_id = '';
    public $placa = '';
    public $marca = '';
    public $modelo = '';
    public $color = '';
    public $tipo_vehiculo = 'carro';
    public $es_principal = false;
    public $estado = 'activo';
    
    // Propiedades para filtros
    public $search = '';
    public $filtroTipo = '';
    public $filtroEstado = '';
    public $filtroUsuario = '';
    
    // Propiedades para búsqueda de usuarios
    public $searchUsuario = '';
    public $usuariosFiltrados = [];
    public $mostrarDropdown = false;
    public $usuarioSeleccionado = null;
    
    // Listas para selects
    public $usuarios = [];
    public $tiposVehiculo = [];
    
    // Listeners
    protected $listeners = [
        'confirmarEliminar',
        'vehiculoActualizado' => '$refresh',
        'vehiculoCreado' => '$refresh'
    ];

    public function __construct()
    {
        $this->vehiculoRepository = app(VehiculoRepository::class);
    }

    public function mount()
    {
        $this->cargarListas();
        $this->filtrarUsuarios();
    }

    public function render()
    {
        $vehiculos = $this->cargarVehiculos();
        
        return view('livewire.admin.gestion-vehiculo.vehiculos-index', [
            'vehiculos' => $vehiculos
        ])->layout('layouts.app');
    }

    protected function cargarListas()
    {
        $this->usuarios = $this->vehiculoRepository->getUsuarios();
        $this->tiposVehiculo = $this->vehiculoRepository->getTiposVehiculo();
    }

    protected function cargarVehiculos()
    {
        if ($this->search) {
            return $this->vehiculoRepository->buscarPorPlaca($this->search);
        }
        
        if ($this->filtroUsuario) {
            return $this->vehiculoRepository->buscarPorUsuario($this->filtroUsuario);
        }
        
        if ($this->filtroTipo) {
            return $this->vehiculoRepository->filtrarPorTipo($this->filtroTipo);
        }
        
        if ($this->filtroEstado) {
            return $this->vehiculoRepository->filtrarPorEstado($this->filtroEstado);
        }
        
        return $this->vehiculoRepository->paginar(10);
    }

    /**
     * Filtrar usuarios para el dropdown
     */
    public function filtrarUsuarios()
    {
        if (empty($this->searchUsuario)) {
            // Si no hay búsqueda, mostrar primeros 10 usuarios
            $this->usuariosFiltrados = $this->usuarios->take(10);
        } else {
            // Buscar por nombre o documento
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
     * Cerrar el dropdown de usuarios
     */
    public function cerrarDropdown()
    {
        $this->mostrarDropdown = false;
        if (empty($this->searchUsuario)) {
            $this->limpiarBusqueda();
        }
    }

    /**
     * Cuando se actualiza la búsqueda de usuarios
     */
    public function updatedSearchUsuario()
    {
        $this->filtrarUsuarios();
        
        // Mostrar dropdown solo si hay resultados o si hay texto
        if (strlen($this->searchUsuario) > 0 && count($this->usuariosFiltrados) > 0) {
            $this->mostrarDropdown = true;
        } else {
            $this->mostrarDropdown = false;
        }
    }

    public function abrirModalNuevo()
    {
        $this->resetearFormulario();
        $this->modalTitle = 'Registrar Vehículo';
        $this->modalAction = 'crear';
        $this->showModal = true;
    }

    public function abrirModalEditar($id)
    {
        $vehiculo = $this->vehiculoRepository->findById($id);
        
        $this->vehiculoId = $vehiculo->id;
        $this->vehiculo_id = $vehiculo->id;
        $this->user_id = $vehiculo->user_id;
        
        // Cargar el usuario seleccionado
        $usuario = $this->usuarios->firstWhere('id', $vehiculo->user_id);
        if ($usuario) {
            $this->usuarioSeleccionado = $usuario;
            $this->searchUsuario = $usuario->name . ' (' . $usuario->numero_documento . ')';
        }
        
        $this->placa = $vehiculo->placa;
        $this->marca = $vehiculo->marca;
        $this->modelo = $vehiculo->modelo;
        $this->color = $vehiculo->color;
        $this->tipo_vehiculo = $vehiculo->tipo_vehiculo;
        $this->es_principal = $vehiculo->es_principal;
        $this->estado = $vehiculo->estado;
        
        $this->modalTitle = 'Editar Vehículo';
        $this->modalAction = 'editar';
        $this->showModal = true;
    }

    public function crear()
    {
        $request = new CrearVehiculoRequest();
        $validated = $this->validate($request->rules(), $request->messages());
        
        $data = [
            'user_id' => $this->user_id,
            'placa' => strtoupper($this->placa),
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'color' => $this->color,
            'tipo_vehiculo' => $this->tipo_vehiculo,
            'es_principal' => $this->es_principal ?? false,
            'estado' => $this->estado,
        ];
        
        $this->vehiculoRepository->create($data);
        
        $this->dispatch('alerta', [
            'titulo' => '¡Creado!',
            'texto' => 'El vehículo se ha registrado correctamente.',
            'icono' => 'success'
        ]);
        
        $this->cerrarModal();
        $this->dispatch('vehiculoCreado');
    }

    public function editar()
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'placa' => 'nullable|string|max:10|unique:vehiculos,placa,' . $this->vehiculoId,
            'marca' => 'nullable|string|max:50',
            'modelo' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:30',
            'tipo_vehiculo' => 'required|in:carro,moto',
            'es_principal' => 'boolean',
            'estado' => 'in:activo,inactivo'
        ];
        
        $messages = [
            'user_id.required' => 'Debe seleccionar un propietario.',
            'user_id.exists' => 'El propietario seleccionado no es válido.',
            'placa.unique' => 'Esta placa ya está registrada para otro vehículo.',
            'placa.max' => 'La placa no puede tener más de 10 caracteres.',
            'tipo_vehiculo.required' => 'Debe seleccionar el tipo de vehículo.',
            'tipo_vehiculo.in' => 'El tipo de vehículo no es válido.',
        ];
        
        $validated = $this->validate($rules, $messages);
        
        $data = [
            'user_id' => $this->user_id,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'color' => $this->color,
            'tipo_vehiculo' => $this->tipo_vehiculo,
            'es_principal' => $this->es_principal ?? false,
            'estado' => $this->estado,
        ];
        
        // Solo incluir placa si tiene valor
        if (!empty($this->placa)) {
            $data['placa'] = strtoupper($this->placa);
        }
        
        // Actualizar vehículo
        $this->vehiculoRepository->update($this->vehiculoId, $data);
        
        $this->dispatch('alerta', [
            'titulo' => '¡Actualizado!',
            'texto' => 'El vehículo se ha actualizado correctamente.',
            'icono' => 'success'
        ]);
        
        $this->cerrarModal();
        $this->dispatch('vehiculoActualizado');
    }

    public function confirmarEliminar($id)
    {
        $vehiculo = $this->vehiculoRepository->findById($id);
        
        $this->dispatch('confirmar-eliminar', [
            'titulo' => '¿Eliminar vehículo?',
            'texto' => "¿Estás seguro de eliminar el vehículo con placa {$vehiculo->placa}? Esta acción no se puede deshacer.",
            'icono' => 'warning',
            'id' => $id
        ]);
    }

    public function eliminar($id)
    {
        try {
            $this->vehiculoRepository->delete($id);
            
            $this->dispatch('alerta', [
                'titulo' => '¡Eliminado!',
                'texto' => 'El vehículo se ha eliminado correctamente.',
                'icono' => 'success'
            ]);
            
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
            'vehiculoId', 'vehiculo_id', 'user_id', 'placa', 'marca', 'modelo',
            'color', 'tipo_vehiculo', 'es_principal', 'estado'
        ]);
        $this->limpiarUsuario();
        $this->tipo_vehiculo = 'carro';
        $this->estado = 'activo';
        $this->resetValidation();
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

    public function updatedFiltroUsuario()
    {
        $this->resetPage();
    }

    public function updatedFiltroTipo()
    {
        $this->resetPage();
    }

    public function updatedFiltroEstado()
    {
        $this->resetPage();
    }
}