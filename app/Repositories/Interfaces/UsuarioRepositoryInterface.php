<?php

namespace App\Repositories\Interfaces;

interface UsuarioRepositoryInterface
{
    public function getAll();
    public function findById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function paginar($cantidad = 10);
    public function buscar($termino);
    public function filtrarPorTipo($tipoUsuarioId);
    public function filtrarPorEstado($estado);
    public function getTiposUsuario();
    public function getRoles();
    public function asignarRoles($usuarioId, array $roles);
    public function sincronizarRoles($usuarioId, array $roles);
}