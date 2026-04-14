<?php
// app/Repositories/Interfaces/TarjetaRfidRepositoryInterface.php

namespace App\Repositories\Interfaces;

interface TarjetaRfidRepositoryInterface
{
    public function getAll();
    public function findById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function paginar($cantidad = 10);
    public function buscar($termino);
    public function filtrarPorEstado($estado);
    public function filtrarPorUsuario($usuarioId);
    public function getUsuarios();
    public function getEstados();
    public function obtenerPorUid($uid);
    public function verificarUidDisponible($uid, $excluirId = null);
}