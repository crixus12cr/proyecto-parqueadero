<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Models\TipoUsuario;
use App\Models\Rol;
use App\Repositories\Interfaces\UsuarioRepositoryInterface;

class UsuarioRepository implements UsuarioRepositoryInterface
{
    protected $model;

    public function __construct()
    {
        $this->model = new User();
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function findById($id)
    {
        return $this->model->with(['tipoUsuario', 'roles'])->findOrFail($id);
    }

    public function create(array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        
        $usuario = $this->model->create($data);
        
        if (isset($data['roles'])) {
            $usuario->roles()->attach($data['roles']);
        }
        
        return $usuario;
    }

    public function update($id, array $data)
    {
        $usuario = $this->findById($id);
        
        if (isset($data['password']) && $data['password']) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }
        
        $usuario->update($data);
        
        return $usuario;
    }

    public function delete($id)
    {
        $usuario = $this->findById($id);
        return $usuario->delete();
    }

    public function paginar($cantidad = 10)
    {
        return $this->model->with(['tipoUsuario', 'roles'])
            ->orderBy('id')
            ->paginate($cantidad);
    }

    public function buscar($termino)
    {
        return $this->model->with(['tipoUsuario', 'roles'])
            ->where('name', 'LIKE', "%{$termino}%")
            ->orWhere('email', 'LIKE', "%{$termino}%")
            ->orWhere('numero_documento', 'LIKE', "%{$termino}%")
            ->paginate(10);
    }

    public function filtrarPorTipo($tipoUsuarioId)
    {
        return $this->model->with(['tipoUsuario', 'roles'])
            ->where('tipo_usuario_id', $tipoUsuarioId)
            ->paginate(10);
    }

    public function filtrarPorEstado($estado)
    {
        return $this->model->with(['tipoUsuario', 'roles'])
            ->where('estado', $estado)
            ->paginate(10);
    }

    public function getTiposUsuario()
    {
        return TipoUsuario::where('estado', 'activo')->get();
    }

    public function getRoles()
    {
        return Rol::where('estado', 'activo')->get();
    }

    public function asignarRoles($usuarioId, array $roles)
    {
        $usuario = $this->findById($usuarioId);
        $usuario->roles()->attach($roles);
        return $usuario;
    }

    public function sincronizarRoles($usuarioId, array $roles)
    {
        $usuario = $this->findById($usuarioId);
        $usuario->roles()->sync($roles);
        return $usuario;
    }
}