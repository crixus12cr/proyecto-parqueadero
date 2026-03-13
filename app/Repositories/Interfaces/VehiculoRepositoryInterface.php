<?php

namespace App\Repositories\Interfaces;

interface VehiculoRepositoryInterface
{
    public function getAll();
    public function findById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function paginar($cantidad = 10);
    public function buscarPorPlaca($placa);
    public function buscarPorUsuario($usuarioId);
    public function filtrarPorTipo($tipo);
    public function filtrarPorEstado($estado);
    public function getUsuarios();
    public function getTiposVehiculo();
}