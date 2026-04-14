<?php
// app/Repositories/Eloquent/TarjetaRfidRepository.php

namespace App\Repositories\Eloquent;

use App\Models\TarjetaRfid;
use App\Models\User;
use App\Repositories\Interfaces\TarjetaRfidRepositoryInterface;
use Carbon\Carbon;

class TarjetaRfidRepository implements TarjetaRfidRepositoryInterface
{
    protected $model;

    public function __construct()
    {
        $this->model = new TarjetaRfid();
    }

    public function getAll()
    {
        return $this->model->with('usuario')->get();
    }

    public function findById($id)
    {
        return $this->model->with('usuario')->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $tarjeta = $this->findById($id);
        $tarjeta->update($data);
        return $tarjeta;
    }

    public function delete($id)
    {
        $tarjeta = $this->findById($id);
        return $tarjeta->delete();
    }

    public function paginar($cantidad = 10)
    {
        return $this->model->with('usuario')
            ->orderBy('created_at', 'desc')
            ->paginate($cantidad);
    }

    public function buscar($termino)
    {
        return $this->model->with('usuario')
            ->where('uid_tarjeta', 'LIKE', "%{$termino}%")
            ->orWhereHas('usuario', function ($q) use ($termino) {
                $q->where('name', 'LIKE', "%{$termino}%")
                    ->orWhere('numero_documento', 'LIKE', "%{$termino}%");
            })
            ->paginate(10);
    }

    public function filtrarPorEstado($estado)
    {
        return $this->model->with('usuario')
            ->where('estado', $estado)
            ->paginate(10);
    }

    public function filtrarPorUsuario($usuarioId)
    {
        return $this->model->with('usuario')
            ->where('user_id', $usuarioId)
            ->paginate(10);
    }

    public function getUsuarios()
    {
        return User::where('estado', 'activo')
            ->orderBy('name')
            ->get();
    }

    public function getEstados()
    {
        return [
            'activa' => 'Activa',
            'inactiva' => 'Inactiva',
            'perdida' => 'Perdida',
            'dañada' => 'Dañada',
            'vencida' => 'Vencida'
        ];
    }

    public function obtenerPorUid($uid)
    {
        return $this->model->where('uid_tarjeta', $uid)->first();
    }

    public function verificarUidDisponible($uid, $excluirId = null)
    {
        $query = $this->model->where('uid_tarjeta', $uid);

        if ($excluirId) {
            $query->where('id', '!=', $excluirId);
        }

        return !$query->exists();
    }

    public function query()
    {
        return $this->model->newQuery();
    }
}
