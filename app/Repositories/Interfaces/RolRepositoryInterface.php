<?php

namespace App\Repositories\Interfaces;

interface RolRepositoryInterface
{
    public function getAll();
    public function getActivos();
    public function findById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function paginar($cantidad = 10);
    public function buscar($termino);
    public function filtrarPorEstado($estado);
}
