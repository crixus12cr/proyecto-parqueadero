<?php
// app/Repositories/Eloquent/VehiculoRepository.php

namespace App\Repositories\Eloquent;

use App\Models\Vehiculo;
use App\Models\User;
use App\Repositories\Interfaces\VehiculoRepositoryInterface;

class VehiculoRepository implements VehiculoRepositoryInterface
{
    protected $model;

    public function __construct()
    {
        $this->model = new Vehiculo();
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
        // Si es principal, quitar principal de otros vehículos del usuario
        if (isset($data['es_principal']) && $data['es_principal']) {
            Vehiculo::where('user_id', $data['user_id'])
                ->where('es_principal', true)
                ->update(['es_principal' => false]);
        }
        
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $vehiculo = $this->findById($id);
        
        // Si es principal, quitar principal de otros vehículos del usuario
        if (isset($data['es_principal']) && $data['es_principal']) {
            Vehiculo::where('user_id', $vehiculo->user_id)
                ->where('id', '!=', $id)
                ->where('es_principal', true)
                ->update(['es_principal' => false]);
        }
        
        $vehiculo->update($data);
        return $vehiculo;
    }

    public function delete($id)
    {
        $vehiculo = $this->findById($id);
        return $vehiculo->delete();
    }

    public function paginar($cantidad = 10)
    {
        return $this->model->with('usuario')
            ->orderBy('placa')
            ->paginate($cantidad);
    }

    public function buscarPorPlaca($placa)
    {
        return $this->model->with('usuario')
            ->where('placa', 'LIKE', "%{$placa}%")
            ->paginate(10);
    }

    public function buscarPorUsuario($usuarioId)
    {
        return $this->model->with('usuario')
            ->where('user_id', $usuarioId)
            ->paginate(10);
    }

    public function filtrarPorTipo($tipo)
    {
        return $this->model->with('usuario')
            ->where('tipo_vehiculo', $tipo)
            ->paginate(10);
    }

    public function filtrarPorEstado($estado)
    {
        return $this->model->with('usuario')
            ->where('estado', $estado)
            ->paginate(10);
    }

    public function getUsuarios()
    {
        return User::where('estado', 'activo')
            ->orderBy('name')
            ->get();
    }

    public function getTiposVehiculo()
    {
        return [
            'carro' => 'Carro',
            'moto' => 'Moto'
        ];
    }
}