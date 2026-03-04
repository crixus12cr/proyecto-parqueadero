<?php
// app/Livewire/Admin/Administracion/ParametrosIndex.php

namespace App\Livewire\Admin\Administracion;

use Livewire\Component;
use App\Repositories\Eloquent\ParametroRepository;
use App\Http\Requests\Admin\Administracion\ActualizarParametrosRequest;

class ParametrosIndex extends Component
{
    protected $parametroRepository;
    
    // Configuración general
    public $capacidad_total = 500;
    public $horario_apertura = '06:00';
    public $horario_cierre = '22:00';
    public $dias_habiles = [];
    public $alerta_ocupacion = 80;
    public $tiempo_gracia = 15;
    public $intentos_maximos_rfid = 3;
    public $notificar_email = true;
    public $email_notificaciones = '';
    
    // Tipos de usuario (almacenados como array asociativo: id => [datos])
    public $tipos_usuario = [];
    
    // Propiedades para UI
    public $activeTab = 'generales';
    public $showModal = false;
    public $tipoEditando = null;
    
    // Lista de días disponibles
    public $diasDisponibles = [
        'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'
    ];

    public function __construct()
    {
        $this->parametroRepository = app(ParametroRepository::class);
    }

    public function mount()
    {
        $this->cargarConfiguracion();
    }

    public function render()
    {
        return view('livewire.admin.administracion.parametros-index')
            ->layout('layouts.app');
    }

    protected function cargarConfiguracion()
    {
        // Cargar configuración general
        $config = $this->parametroRepository->getConfiguracionGeneral();
        
        $this->capacidad_total = $config->capacidad_total;
        $this->horario_apertura = substr($config->horario_apertura, 0, 5);
        $this->horario_cierre = substr($config->horario_cierre, 0, 5);
        $this->dias_habiles = is_string($config->dias_habiles) 
            ? json_decode($config->dias_habiles) 
            : ($config->dias_habiles ?? []);
        $this->alerta_ocupacion = $config->alerta_ocupacion;
        $this->tiempo_gracia = $config->tiempo_gracia;
        $this->intentos_maximos_rfid = $config->intentos_maximos_rfid;
        $this->notificar_email = $config->notificar_email ?? true;
        $this->email_notificaciones = $config->email_notificaciones ?? '';
        
        // Cargar tipos de usuario
        $tipos = $this->parametroRepository->getTiposUsuario();
        
        foreach ($tipos as $tipo) {
            $this->tipos_usuario[$tipo->id] = [
                'id' => $tipo->id,
                'nombre' => $tipo->nombre,
                'horas_maximas_estadia' => $tipo->horas_maximas_estadia,
                'prioridad_acceso' => $tipo->prioridad_acceso,
            ];
        }
    }

    public function guardarTodo()
    {
        // Crear request y validar
        $request = new ActualizarParametrosRequest();
        
        $datos = [
            'capacidad_total' => $this->capacidad_total,
            'horario_apertura' => $this->horario_apertura,
            'horario_cierre' => $this->horario_cierre,
            'dias_habiles' => $this->dias_habiles,
            'alerta_ocupacion' => $this->alerta_ocupacion,
            'tiempo_gracia' => $this->tiempo_gracia,
            'intentos_maximos_rfid' => $this->intentos_maximos_rfid,
            'notificar_email' => $this->notificar_email,
            'email_notificaciones' => $this->email_notificaciones,
            'tipos_usuario' => $this->tipos_usuario,
        ];
        
        $validated = $this->validate($request->rules(), $request->messages());
        
        try {
            // Guardar configuración general
            $this->parametroRepository->actualizarConfiguracionGeneral([
                'capacidad_total' => $this->capacidad_total,
                'horario_apertura' => $this->horario_apertura . ':00',
                'horario_cierre' => $this->horario_cierre . ':00',
                'dias_habiles' => json_encode($this->dias_habiles),
                'alerta_ocupacion' => $this->alerta_ocupacion,
                'tiempo_gracia' => $this->tiempo_gracia,
                'intentos_maximos_rfid' => $this->intentos_maximos_rfid,
                'notificar_email' => $this->notificar_email,
                'email_notificaciones' => $this->email_notificaciones,
            ]);
            
            // Guardar tipos de usuario
            foreach ($this->tipos_usuario as $id => $data) {
                $this->parametroRepository->actualizarTipoUsuario($id, [
                    'horas_maximas_estadia' => $data['horas_maximas_estadia'],
                    'prioridad_acceso' => $data['prioridad_acceso'],
                ]);
            }
            
            $this->dispatch('alerta', [
                'titulo' => '¡Guardado!',
                'texto' => 'Los parámetros se han actualizado correctamente.',
                'icono' => 'success'
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('alerta', [
                'titulo' => '¡Error!',
                'texto' => 'Error al guardar: ' . $e->getMessage(),
                'icono' => 'error'
            ]);
        }
    }

    public function editarTipoUsuario($id)
    {
        $this->tipoEditando = $id;
    }

    public function cancelarEdicion()
    {
        $this->tipoEditando = null;
    }

    public function cambiarTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function updatedNotificarEmail($value)
    {
        if (!$value) {
            $this->email_notificaciones = '';
        }
    }
}