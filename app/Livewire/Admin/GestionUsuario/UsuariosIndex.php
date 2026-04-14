<?php
// app/Livewire/Admin/GestionUsuario/UsuariosIndex.php

namespace App\Livewire\Admin\GestionUsuario;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Repositories\Eloquent\UsuarioRepository;
use App\Http\Requests\Admin\GestionUsuario\CrearUsuarioRequest;
use App\Http\Requests\Admin\GestionUsuario\ActualizarUsuarioRequest;

class UsuariosIndex extends Component
{
    use WithPagination, WithFileUploads;

    protected $usuarioRepository;
    
    // Propiedades para el modal
    public $showModal = false;
    public $modalTitle = '';
    public $modalAction = '';
    
    // Propiedades del formulario
    public $usuarioId = null;
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $tipo_usuario_id = '';
    public $numero_documento = '';
    public $telefono = '';
    public $foto = null;
    public $foto_url = null;
    public $estado = 'activo';
    public $roles = [];
    
    // Propiedades para filtros
    public $search = '';
    public $filtroTipo = '';
    public $filtroEstado = '';
    
    // Propiedades para ordenamiento
    public $sortField = 'name';
    public $sortDirection = 'asc';
    
    // Listas para selects
    public $tiposUsuario = [];
    public $rolesDisponibles = [];
    
    // Listeners para eventos
    protected $listeners = [
        'eliminar',
        'confirmarEliminar',
        'usuarioActualizado' => '$refresh',
        'usuarioCreado' => '$refresh'
    ];

    public function __construct()
    {
        $this->usuarioRepository = app(UsuarioRepository::class);
    }

    public function mount()
    {
        $this->cargarListas();
    }

    public function render()
    {
        $usuarios = $this->cargarUsuarios();
        
        return view('livewire.admin.gestion-usuario.usuarios-index', [
            'usuarios' => $usuarios
        ])->layout('layouts.app');
    }

    protected function cargarListas()
    {
        $this->tiposUsuario = $this->usuarioRepository->getTiposUsuario();
        $this->rolesDisponibles = $this->usuarioRepository->getRoles();
    }

    protected function cargarUsuarios()
    {
        if ($this->search) {
            return $this->usuarioRepository->buscar($this->search);
        }
        
        if ($this->filtroTipo) {
            return $this->usuarioRepository->filtrarPorTipo($this->filtroTipo);
        }
        
        if ($this->filtroEstado) {
            return $this->usuarioRepository->filtrarPorEstado($this->filtroEstado);
        }
        
        return $this->usuarioRepository->paginar(10);
    }

    public function abrirModalNuevo()
    {
        $this->resetearFormulario();
        $this->modalTitle = 'Nuevo Usuario';
        $this->modalAction = 'crear';
        $this->showModal = true;
    }

    public function abrirModalEditar($id)
    {
        $usuario = $this->usuarioRepository->findById($id);
        
        $this->usuarioId = $usuario->id;
        $this->name = $usuario->name;
        $this->email = $usuario->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->tipo_usuario_id = $usuario->tipo_usuario_id;
        $this->numero_documento = $usuario->numero_documento;
        $this->telefono = $usuario->telefono;
        $this->foto_url = $usuario->foto;
        $this->estado = $usuario->estado;
        $this->roles = $usuario->roles->pluck('id')->toArray();
        
        $this->modalTitle = 'Editar Usuario';
        $this->modalAction = 'editar';
        $this->showModal = true;
    }

    public function crear()
    {
        $request = new CrearUsuarioRequest();
        $validated = $this->validate($request->rules(), $request->messages());
        
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'tipo_usuario_id' => $this->tipo_usuario_id,
            'numero_documento' => $this->numero_documento,
            'telefono' => $this->telefono,
            'estado' => $this->estado,
        ];
        
        // Manejar foto
        if ($this->foto) {
            $data['foto'] = $this->foto->store('fotos-usuarios', 'public');
        }
        
        $usuario = $this->usuarioRepository->create($data);
        
        // Asignar roles
        if (!empty($this->roles)) {
            $this->usuarioRepository->sincronizarRoles($usuario->id, $this->roles);
        }
        
        $this->dispatch('alerta', [
            'titulo' => '¡Creado!',
            'texto' => 'El usuario se ha creado correctamente.',
            'icono' => 'success'
        ]);
        
        $this->cerrarModal();
        $this->dispatch('usuarioCreado');
    }

    public function editar()
    {
        // Validación manual con reglas simples
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->usuarioId,
            'tipo_usuario_id' => 'required|exists:tipo_usuarios,id',
            'numero_documento' => 'required|string|max:20|unique:users,numero_documento,' . $this->usuarioId,
            'telefono' => 'nullable|string|max:20',
            'password' => 'nullable|confirmed|min:8',
            'estado' => 'in:activo,inactivo',
        ];
        
        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.unique' => 'Este email ya está registrado.',
            'tipo_usuario_id.required' => 'El tipo de usuario es obligatorio.',
            'numero_documento.required' => 'El número de documento es obligatorio.',
            'numero_documento.unique' => 'Este número de documento ya está registrado.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ];
        
        $validated = $this->validate($rules, $messages);
        
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'tipo_usuario_id' => $this->tipo_usuario_id,
            'numero_documento' => $this->numero_documento,
            'telefono' => $this->telefono,
            'estado' => $this->estado,
        ];
        
        if ($this->password) {
            $data['password'] = $this->password;
        }
        
        // Manejar foto
        if ($this->foto) {
            $data['foto'] = $this->foto->store('fotos-usuarios', 'public');
        }
        
        $this->usuarioRepository->update($this->usuarioId, $data);
        
        // Sincronizar roles
        $this->usuarioRepository->sincronizarRoles($this->usuarioId, $this->roles ?? []);
        
        $this->dispatch('alerta', [
            'titulo' => '¡Actualizado!',
            'texto' => 'El usuario se ha actualizado correctamente.',
            'icono' => 'success'
        ]);
        
        $this->cerrarModal();
        $this->dispatch('usuarioActualizado');
    }

    public function confirmarEliminar($id)
    {
        $usuario = $this->usuarioRepository->findById($id);
        
        $this->dispatch('confirmar-eliminar', [
            'titulo' => '¿Eliminar usuario?',
            'texto' => "¿Estás seguro de eliminar al usuario {$usuario->name}? Esta acción no se puede deshacer.",
            'icono' => 'warning',
            'id' => $id
        ]);
    }

    public function eliminar($id)
    {
        try {
            $usuario = $this->usuarioRepository->findById($id);
            
            // Verificar si tiene vehículos
            if ($usuario->vehiculos()->count() > 0) {
                $this->dispatch('alerta', [
                    'titulo' => '¡No se puede eliminar!',
                    'texto' => 'El usuario tiene vehículos registrados. Debe eliminar o reasignar los vehículos primero.',
                    'icono' => 'error'
                ]);
                return;
            }
            
            // Verificar si tiene tarjetas RFID
            if ($usuario->tarjetasRfid()->count() > 0) {
                $this->dispatch('alerta', [
                    'titulo' => '¡No se puede eliminar!',
                    'texto' => 'El usuario tiene tarjetas RFID asignadas. Debe eliminarlas primero.',
                    'icono' => 'error'
                ]);
                return;
            }
            
            // Verificar si tiene registros de acceso
            if ($usuario->registrosAcceso()->count() > 0) {
                $this->dispatch('alerta', [
                    'titulo' => '¡No se puede eliminar!',
                    'texto' => 'El usuario tiene historial de accesos. No se puede eliminar por auditoría.',
                    'icono' => 'error'
                ]);
                return;
            }
            
            $this->usuarioRepository->delete($id);
            
            $this->dispatch('alerta', [
                'titulo' => '¡Eliminado!',
                'texto' => 'El usuario se ha eliminado correctamente.',
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
            'usuarioId', 'name', 'email', 'password', 'password_confirmation',
            'tipo_usuario_id', 'numero_documento', 'telefono', 'foto', 'foto_url',
            'estado', 'roles'
        ]);
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

    public function updatedFiltroTipo()
    {
        $this->resetPage();
    }

    public function updatedFiltroEstado()
    {
        $this->resetPage();
    }
}