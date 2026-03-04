<?php

namespace App\Repositories\Eloquent;

use App\Models\Configuracion;
use App\Models\TipoUsuario;
use App\Repositories\Interfaces\ParametroRepositoryInterface;

class ParametroRepository implements ParametroRepositoryInterface
{
    protected $configuracionModel;
    protected $tipoUsuarioModel;

    public function __construct()
    {
        $this->configuracionModel = new Configuracion();
        $this->tipoUsuarioModel = new TipoUsuario();
    }

    public function getConfiguracionGeneral()
    {
        // Siempre tener al menos un registro
        $config = $this->configuracionModel->first();
        
        if (!$config) {
            $config = $this->configuracionModel->create([
                'capacidad_total' => 500,
                'horario_apertura' => '06:00:00',
                'horario_cierre' => '22:00:00',
                'dias_habiles' => json_encode(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes']),
                'alerta_ocupacion' => 80,
                'tiempo_gracia' => 15,
                'intentos_maximos_rfid' => 3,
                'notificar_email' => true,
            ]);
        }
        
        return $config;
    }

    public function getTiposUsuario()
    {
        return $this->tipoUsuarioModel->where('estado', 'activo')->get();
    }

    public function getTipoUsuarioPorId($id)
    {
        return $this->tipoUsuarioModel->findOrFail($id);
    }

    public function actualizarConfiguracionGeneral(array $data)
    {
        $config = $this->getConfiguracionGeneral();
        $config->update($data);
        return $config;
    }

    public function actualizarTipoUsuario($id, array $data)
    {
        $tipo = $this->getTipoUsuarioPorId($id);
        $tipo->update($data);
        return $tipo;
    }

    public function actualizarMultiplesTiposUsuario(array $tipos)
    {
        foreach ($tipos as $id => $data) {
            $this->actualizarTipoUsuario($id, $data);
        }
        return true;
    }
}