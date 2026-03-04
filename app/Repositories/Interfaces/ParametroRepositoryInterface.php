<?php

namespace App\Repositories\Interfaces;

interface ParametroRepositoryInterface
{
    public function getConfiguracionGeneral();
    public function getTiposUsuario();
    public function getTipoUsuarioPorId($id);
    public function actualizarConfiguracionGeneral(array $data);
    public function actualizarTipoUsuario($id, array $data);
    public function actualizarMultiplesTiposUsuario(array $tipos);
}