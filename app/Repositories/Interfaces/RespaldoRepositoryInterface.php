<?php

namespace App\Repositories\Interfaces;

interface RespaldoRepositoryInterface
{
    public function findById($id);
    public function getConfiguracion();
    public function guardarConfiguracion(array $data);
    public function getHistorialRespaldos();
    public function registrarRespaldo(array $data);
    public function eliminarRespaldo($id);
}