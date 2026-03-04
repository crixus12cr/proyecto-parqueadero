<?php

namespace App\Repositories\Eloquent;

use App\Models\Respaldo;
use App\Models\ConfiguracionRespaldo;
use App\Repositories\Interfaces\RespaldoRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class RespaldoRepository implements RespaldoRepositoryInterface
{
    protected $model;
    protected $configModel;

    public function __construct()
    {
        $this->model = new Respaldo();
        $this->configModel = new ConfiguracionRespaldo();
    }

    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function getConfiguracion()
    {
        return $this->configModel->first() ?? $this->configModel;
    }

    public function guardarConfiguracion(array $data)
    {
        $config = $this->configModel->first();
        
        if ($config) {
            $config->update($data);
            return $config;
        }
        
        return $this->configModel->create($data);
    }

    public function getHistorialRespaldos()
    {
        return $this->model->orderBy('created_at', 'desc')->paginate(10);
    }

    public function registrarRespaldo(array $data)
    {
        return $this->model->create($data);
    }

    public function eliminarRespaldo($id)
    {
        $respaldo = $this->model->findOrFail($id);
        
        // Eliminar archivo físico
        if ($respaldo->archivo && Storage::exists($respaldo->archivo)) {
            Storage::delete($respaldo->archivo);
        }
        
        return $respaldo->delete();
    }

    public function getRespaldosProgramados()
    {
        return $this->configModel->first();
    }

    public function guardarProgramacion(array $data)
    {
        $config = $this->configModel->first();
        
        if ($config) {
            $config->update($data);
            return $config;
        }
        
        return $this->configModel->create($data);
    }
}