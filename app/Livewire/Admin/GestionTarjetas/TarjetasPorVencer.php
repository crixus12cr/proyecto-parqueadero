<?php
// app/Livewire/Admin/GestionTarjetas/TarjetasPorVencer.php

namespace App\Livewire\Admin\GestionTarjetas;

use Livewire\Component;
use Livewire\WithPagination;
use App\Repositories\Eloquent\TarjetaRfidRepository;
use Carbon\Carbon;

class TarjetasPorVencer extends Component
{
    use WithPagination;

    protected $tarjetaRepository;
    
    // Propiedades para filtros
    public $search = '';
    public $diasAlerta = 30;
    
    // Propiedades para ordenamiento
    public $sortField = 'fecha_vencimiento';
    public $sortDirection = 'asc';
    
    // Días para alerta
    public $diasOpciones = [7, 15, 30, 45, 60];

    public function __construct()
    {
        $this->tarjetaRepository = app(TarjetaRfidRepository::class);
    }

    public function render()
    {
        $tarjetas = $this->cargarTarjetas();
        
        return view('livewire.admin.gestion-tarjetas.tarjetas-por-vencer', [
            'tarjetas' => $tarjetas,
            'estadisticas' => $this->calcularEstadisticas()
        ])->layout('layouts.app');
    }

    protected function cargarTarjetas()
    {
        $hoy = Carbon::now()->startOfDay();
        
        $query = $this->tarjetaRepository->query()
            ->where('estado', 'activa')
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '>', $hoy)
            ->whereDate('fecha_vencimiento', '<=', $hoy->copy()->addDays($this->diasAlerta));
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('uid_tarjeta', 'LIKE', "%{$this->search}%")
                  ->orWhereHas('usuario', function($sub) {
                      $sub->where('name', 'LIKE', "%{$this->search}%")
                          ->orWhere('numero_documento', 'LIKE', "%{$this->search}%");
                  });
            });
        }
        
        return $query->orderBy($this->sortField, $this->sortDirection)
            ->with('usuario')
            ->paginate(15);
    }

    protected function calcularEstadisticas()
    {
        $hoy = Carbon::now()->startOfDay();
        
        return [
            // Días 1 a 7
            'proximas_7_dias' => $this->tarjetaRepository->query()
                ->where('estado', 'activa')
                ->whereNotNull('fecha_vencimiento')
                ->whereDate('fecha_vencimiento', '>', $hoy)
                ->whereDate('fecha_vencimiento', '<=', $hoy->copy()->addDays(7))
                ->count(),
            
            // Días 8 a 15
            'proximas_15_dias' => $this->tarjetaRepository->query()
                ->where('estado', 'activa')
                ->whereNotNull('fecha_vencimiento')
                ->whereDate('fecha_vencimiento', '>', $hoy->copy()->addDays(7))
                ->whereDate('fecha_vencimiento', '<=', $hoy->copy()->addDays(15))
                ->count(),
            
            // Días 16 a 30
            'proximas_30_dias' => $this->tarjetaRepository->query()
                ->where('estado', 'activa')
                ->whereNotNull('fecha_vencimiento')
                ->whereDate('fecha_vencimiento', '>', $hoy->copy()->addDays(15))
                ->whereDate('fecha_vencimiento', '<=', $hoy->copy()->addDays(30))
                ->count(),
        ];
    }

    public function cambiarDiasAlerta($dias)
    {
        $this->diasAlerta = $dias;
        $this->resetPage();
    }

    public function ordenar($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}