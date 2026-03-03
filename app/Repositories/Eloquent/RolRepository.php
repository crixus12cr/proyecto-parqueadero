<?php
namespace App\Repositories\Eloquent;

use App\Models\Rol;
use App\Repositories\Interfaces\RolRepositoryInterface;

class RolRepository implements RolRepositoryInterface
{
    public function getAll()
    {
        return Rol::all();
    }

    public function getActivos()
    {
        return Rol::where('estado', 'activo')->get();
    }

    public function findById($id)
    {
        return Rol::findOrFail($id);
    }

    public function create(array $data)
    {
        return Rol::create($data);
    }

    public function update($id, array $data)
    {
        $rol = $this->findById($id);
        $rol->update($data);
        return $rol;
    }

    public function delete($id)
    {
        $rol = $this->findById($id);
        return $rol->delete();
    }

    public function paginar($cantidad = 10)
    {
        return Rol::paginate($cantidad);
    }

    public function buscar($termino)
    {
        return Rol::where('nombre', 'LIKE', "%{$termino}%")
            ->orWhere('descripcion', 'LIKE', "%{$termino}%")
            ->paginate(10);
    }

    public function filtrarPorEstado($estado)
    {
        return Rol::where('estado', $estado);
    }
}
