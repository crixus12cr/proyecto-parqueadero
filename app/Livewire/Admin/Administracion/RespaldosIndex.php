<?php
// app/Livewire/Admin/Administracion/RespaldosIndex.php

namespace App\Livewire\Admin\Administracion;

use Livewire\Component;
use Livewire\WithPagination;
use App\Repositories\Eloquent\RespaldoRepository; // ← Cambiado: usar implementación concreta
use App\Services\BackupService;
use App\Http\Requests\Admin\Administracion\ConfigurarRespaldoRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RespaldosIndex extends Component
{
    use WithPagination;

    protected $respaldoRepository;
    protected $backupService;
    
    // Propiedades para tabs
    public $tab = 'historial';
    
    // Propiedades para configuración
    public $config = [];
    public $frecuencia = 'manual';
    public $hora_programada = '02:00';
    public $dia_semana = 1; // Lunes
    public $dia_mes = 1;
    public $mantener_respaldos = 10;
    public $incluir_archivos = true;
    public $notificar_email = '';
    public $activo = true;
    
    // Propiedades para el modal de nuevo respaldo
    public $showModal = false;
    public $observaciones = '';
    
    // Propiedades para confirmación
    public $confirmandoRestaurar = false;
    public $respaldoIdRestaurar = null;
    
    // Propiedades para filtros
    public $search = '';
    public $filtroTipo = '';
    
    // Listeners
    protected $listeners = [
        'respaldoGenerado' => '$refresh',
        'confirmarRestaurar',
        'confirmarEliminar',
        'eliminar' // ← Agregado para el método eliminar
    ];

    public function __construct()
    {
        // ✅ Usar el repositorio concreto directamente, sin interfaz
        $this->respaldoRepository = app(RespaldoRepository::class);
        $this->backupService = app(BackupService::class);
    }

    public function mount()
    {
        $this->cargarConfiguracion();
    }

    public function render()
    {
        $respaldos = $this->cargarRespaldos();
        $estadisticas = $this->calcularEstadisticas();
        
        return view('livewire.admin.administracion.respaldos-index', [
            'respaldos' => $respaldos,
            'estadisticas' => $estadisticas,
            'espacioTotal' => $this->calcularEspacioTotal()
        ])->layout('layouts.app');
    }

    protected function cargarConfiguracion()
    {
        $config = $this->respaldoRepository->getConfiguracion();
        
        if ($config) {
            $this->frecuencia = $config->frecuencia;
            $this->hora_programada = $config->hora_programada ?? '02:00';
            $this->dia_semana = $config->dia_semana ?? 1;
            $this->dia_mes = $config->dia_mes ?? 1;
            $this->mantener_respaldos = $config->mantener_respaldos ?? 10;
            $this->incluir_archivos = $config->incluir_archivos ?? true;
            $this->notificar_email = $config->notificar_email ?? '';
            $this->activo = $config->activo ?? true;
        }
    }

    protected function cargarRespaldos()
    {
        return $this->respaldoRepository->getHistorialRespaldos();
    }

    protected function calcularEstadisticas()
    {
        $respaldos = $this->respaldoRepository->getHistorialRespaldos();
        $items = $respaldos->items();
        
        return [
            'total' => $respaldos->total(),
            'completados' => collect($items)->where('estado', 'completado')->count(),
            'fallidos' => collect($items)->where('estado', 'fallido')->count(),
            'ultimo' => $respaldos->first(),
        ];
    }

    protected function calcularEspacioTotal()
    {
        $respaldos = $this->respaldoRepository->getHistorialRespaldos();
        $items = $respaldos->items();
        $totalBytes = collect($items)->sum('tamano');
        
        return $this->formatearBytes($totalBytes);
    }

    protected function formatearBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function cambiarTab($tab)
    {
        $this->tab = $tab;
    }

    public function guardarConfiguracion()
    {
        $request = new ConfigurarRespaldoRequest();
        $validated = $this->validate($request->rules(), $request->messages());
        
        $this->respaldoRepository->guardarConfiguracion([
            'frecuencia' => $this->frecuencia,
            'hora_programada' => $this->hora_programada,
            'dia_semana' => $this->dia_semana,
            'dia_mes' => $this->dia_mes,
            'mantener_respaldos' => $this->mantener_respaldos,
            'incluir_archivos' => $this->incluir_archivos,
            'notificar_email' => $this->notificar_email,
            'activo' => $this->activo
        ]);
        
        $this->dispatch('alerta', [
            'titulo' => '¡Guardado!',
            'texto' => 'Configuración guardada correctamente.',
            'icono' => 'success'
        ]);
    }

    public function abrirModalNuevoRespaldo()
    {
        $this->observaciones = '';
        $this->showModal = true;
    }

    public function generarRespaldo()
    {
        try {
            $this->backupService->generarRespaldoCompleto(Auth::user(), $this->observaciones);
            
            $this->dispatch('alerta', [
                'titulo' => '¡Respaldo generado!',
                'texto' => 'El respaldo se ha generado correctamente.',
                'icono' => 'success'
            ]);
            
            $this->showModal = false;
            $this->dispatch('respaldoGenerado');
            
        } catch (\Exception $e) {
            $this->dispatch('alerta', [
                'titulo' => '¡Error!',
                'texto' => 'Error al generar respaldo: ' . $e->getMessage(),
                'icono' => 'error'
            ]);
        }
    }

    public function confirmarRestaurar($id)
    {
        $this->respaldoIdRestaurar = $id;
        $this->confirmandoRestaurar = true;
        
        $this->dispatch('confirmar-restauracion', [
            'titulo' => '¿Restaurar respaldo?',
            'texto' => 'Esta acción sobrescribirá los datos actuales. ¿Estás seguro?',
            'icono' => 'warning',
            'id' => $id
        ]);
    }

    public function restaurar($id)
    {
        try {
            $this->backupService->restaurarRespaldo($id);
            
            $this->dispatch('alerta', [
                'titulo' => '¡Restaurado!',
                'texto' => 'El respaldo se ha restaurado correctamente.',
                'icono' => 'success'
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('alerta', [
                'titulo' => '¡Error!',
                'texto' => 'Error al restaurar: ' . $e->getMessage(),
                'icono' => 'error'
            ]);
        }
        
        $this->confirmandoRestaurar = false;
        $this->respaldoIdRestaurar = null;
    }

    public function confirmarEliminar($id)
    {
        $this->dispatch('confirmar-eliminar', [
            'titulo' => '¿Eliminar respaldo?',
            'texto' => 'Esta acción no se puede deshacer.',
            'icono' => 'warning',
            'id' => $id
        ]);
    }

    public function eliminar($id)
    {
        try {
            $this->respaldoRepository->eliminarRespaldo($id);
            
            $this->dispatch('alerta', [
                'titulo' => '¡Eliminado!',
                'texto' => 'El respaldo se ha eliminado correctamente.',
                'icono' => 'success'
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('alerta', [
                'titulo' => '¡Error!',
                'texto' => 'Error al eliminar: ' . $e->getMessage(),
                'icono' => 'error'
            ]);
        }
    }

    public function descargar($id)
    {
        $respaldo = $this->respaldoRepository->findById($id);
        
        if (!$respaldo || !$respaldo->archivo) {
            $this->dispatch('alerta', [
                'titulo' => '¡Error!',
                'texto' => 'Archivo no encontrado.',
                'icono' => 'error'
            ]);
            return;
        }
        
        return response()->download(storage_path("app/{$respaldo->archivo}"));
    }

    public function updatedFrecuencia()
    {
        if ($this->frecuencia === 'manual') {
            $this->activo = false;
        }
    }
}