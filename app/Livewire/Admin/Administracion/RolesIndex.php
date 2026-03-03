<?php

namespace App\Livewire\Admin\Administracion;

use Livewire\Component;
use Livewire\WithPagination;
use App\Repositories\Eloquent\RolRepository;
use App\Http\Requests\Admin\Administracion\CrearRolRequest;
use App\Http\Requests\Admin\Administracion\ActualizarRolRequest;

class RolesIndex extends Component
{
    use WithPagination;

    protected $rolRepository;
    
    // Propiedades para el modal
    public $showModal = false;
    public $modalTitle = '';
    public $modalAction = '';
    
    // Propiedades del formulario
    public $rolId = null;
    public $nombre = '';
    public $descripcion = '';
    public $estado = 'activo';
    
    // Propiedades para filtros
    public $search = '';
    public $filtroEstado = '';
    
    // Propiedades para ordenamiento
    public $sortField = 'nombre';
    public $sortDirection = 'asc';
    
    // Listeners para eventos
    protected $listeners = [
        'confirmarEliminar',
        'eliminar',
        'rolActualizado' => '$refresh',
        'rolCreado' => '$refresh'
    ];

    public function __construct()
    {
        $this->rolRepository = app(RolRepository::class);
    }

    public function render()
    {
        $roles = $this->cargarRoles();
        
        return view('livewire.admin.administracion.roles-index', [
            'roles' => $roles
        ])->layout('layouts.app');
    }

    protected function cargarRoles()
    {
        // Si hay búsqueda por texto, usar el método buscar (ya incluye paginación)
        if ($this->search) {
            return $this->rolRepository->buscar($this->search);
        }
        
        // Si solo hay filtro por estado
        if ($this->filtroEstado) {
            $query = $this->rolRepository->filtrarPorEstado($this->filtroEstado);
            return $query->orderBy($this->sortField, $this->sortDirection)->paginate(10);
        }
        
        // Si no hay filtros, usar paginación normal
        return $this->rolRepository->paginar(10);
    }

    public function abrirModalNuevo()
    {
        $this->resetearFormulario();
        $this->modalTitle = 'Nuevo Rol';
        $this->modalAction = 'crear';
        $this->showModal = true;
    }

    public function abrirModalEditar($id)
    {
        $rol = $this->rolRepository->findById($id);
        
        $this->rolId = $rol->id;
        $this->nombre = $rol->nombre;
        $this->descripcion = $rol->descripcion;
        $this->estado = $rol->estado;
        
        $this->modalTitle = 'Editar Rol';
        $this->modalAction = 'editar';
        $this->showModal = true;
    }

    public function crear()
    {
        $request = new CrearRolRequest();
        $validated = $this->validate($request->rules(), $request->messages());
        
        $this->rolRepository->create($validated);
        
        $this->dispatch('alerta', [
            'titulo' => '¡Creado!',
            'texto' => 'El rol se ha creado correctamente.',
            'icono' => 'success'
        ]);
        
        $this->cerrarModal();
        $this->dispatch('rolCreado');
    }

    public function editar()
    {
        $request = new ActualizarRolRequest();
        $validated = $this->validate($request->rules(), $request->messages());
        
        $this->rolRepository->update($this->rolId, $validated);
        
        $this->dispatch('alerta', [
            'titulo' => '¡Actualizado!',
            'texto' => 'El rol se ha actualizado correctamente.',
            'icono' => 'success'
        ]);
        
        $this->cerrarModal();
        $this->dispatch('rolActualizado');
    }

    public function confirmarEliminar($id)
    {
        $rol = $this->rolRepository->findById($id);
        
        $this->dispatch('confirmar-eliminar', [
            'titulo' => '¿Eliminar rol?',
            'texto' => "¿Estás seguro de eliminar el rol {$rol->nombre}? Esta acción no se puede deshacer.",
            'icono' => 'warning',
            'id' => $id
        ]);
    }

    public function eliminar($id)
    {
        $rol = $this->rolRepository->findById($id);
        
        // Verificar si tiene usuarios asignados
        if ($rol->usuarios()->count() > 0) {
            $this->dispatch('alerta', [
                'titulo' => '¡Error!',
                'texto' => 'No se puede eliminar el rol porque tiene usuarios asignados.',
                'icono' => 'error'
            ]);
            return;
        }
        
        $this->rolRepository->delete($id);
        
        $this->dispatch('alerta', [
            'titulo' => '¡Eliminado!',
            'texto' => 'El rol se ha eliminado correctamente.',
            'icono' => 'success'
        ]);
        
        $this->resetPage();
    }

    public function resetearFormulario()
    {
        $this->reset(['rolId', 'nombre', 'descripcion', 'estado']);
        $this->resetValidation();
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->resetearFormulario();
    }

    public function ordenar($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}