<div>
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <flux:heading size="xl">Tarjetas Próximas a Vencer</flux:heading>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Tarjetas RFID que expirarán en los próximos días.
            </p>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">1 - 7 días</div>
            <div class="text-2xl font-semibold text-orange-600">{{ $estadisticas['proximas_7_dias'] }}</div>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">8 - 15 días</div>
            <div class="text-2xl font-semibold text-yellow-600">{{ $estadisticas['proximas_15_dias'] }}</div>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">16 - 30 días</div>
            <div class="text-2xl font-semibold text-blue-600">{{ $estadisticas['proximas_30_dias'] }}</div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="mb-6 flex flex-wrap gap-4">
        <div class="flex-1 min-w-[200px]">
            <flux:input 
                wire:model.live.debounce.300ms="search" 
                placeholder="Buscar por UID, propietario o documento..."
                icon="magnifying-glass" 
            />
        </div>
        
        <div class="flex gap-2">
            @foreach($diasOpciones as $dias)
                <flux:button 
                    wire:click="cambiarDiasAlerta({{ $dias }})" 
                    variant="{{ $diasAlerta == $dias ? 'primary' : 'outline' }}"
                    size="sm">
                    {{ $dias }} días
                </flux:button>
            @endforeach
        </div>
    </div>

    <!-- Tabla de tarjetas -->
    <flux:table>
        <flux:table.columns>
            <flux:table.column>
                <button wire:click="ordenar('uid_tarjeta')"
                    class="flex items-center gap-1 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-200">
                    UID Tarjeta
                    @if ($sortField === 'uid_tarjeta')
                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-4" />
                    @endif
                </button>
            </flux:table.column>
            <flux:table.column>
                <button wire:click="ordenar('user_id')"
                    class="flex items-center gap-1 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-200">
                    Propietario
                    @if ($sortField === 'user_id')
                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-4" />
                    @endif
                </button>
            </flux:table.column>
            <flux:table.column>
                <button wire:click="ordenar('fecha_vencimiento')"
                    class="flex items-center gap-1 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-200">
                    Fecha Vencimiento
                    @if ($sortField === 'fecha_vencimiento')
                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-4" />
                    @endif
                </button>
            </flux:table.column>
            <flux:table.column>Días Restantes</flux:table.column>
            <flux:table.column>Estado</flux:table.column>
            <flux:table.column class="text-right">Acciones</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($tarjetas as $tarjeta)
                @php
                    $fechaVencimiento = \Carbon\Carbon::parse($tarjeta->fecha_vencimiento);
                    $hoy = \Carbon\Carbon::now()->startOfDay();
                    
                    // Calcular días restantes correctamente
                    $diasRestantes = $hoy->diffInDays($fechaVencimiento, false);
                    
                    if ($diasRestantes == 0) {
                        $textoDias = 'Hoy';
                        $colorClase = 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
                    } elseif ($diasRestantes < 0) {
                        $textoDias = 'Vencida';
                        $colorClase = 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';
                    } else {
                        $textoDias = $diasRestantes . ' día' . ($diasRestantes != 1 ? 's' : '');
                        $colorClase = $diasRestantes <= 7 ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : 
                                      ($diasRestantes <= 15 ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400' : 
                                      'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400');
                    }
                @endphp
                <flux:table.row>
                    <flux:table.cell class="font-mono font-medium">{{ $tarjeta->uid_tarjeta }}</flux:table.cell>
                    <flux:table.cell>
                        @if($tarjeta->usuario)
                            {{ $tarjeta->usuario->name }}
                            <div class="text-xs text-gray-500">{{ $tarjeta->usuario->numero_documento }}</div>
                        @else
                            <span class="text-gray-400">Sin asignar</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>{{ $fechaVencimiento->format('d/m/Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $colorClase }}">
                            {{ $textoDias }}
                        </span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="$tarjeta->estado === 'activa' ? 'green' : 'gray'" size="sm">
                            {{ ucfirst($tarjeta->estado) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-right">
                        <flux:button wire:click="$dispatch('abrir-editar-tarjeta', { id: {{ $tarjeta->id }} })" 
                            variant="subtle" size="sm" icon="pencil-square" aria-label="Editar" />
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center py-10">
                        <div class="flex flex-col items-center gap-2">
                            <flux:icon name="check-circle" class="size-10 text-green-500" />
                            <span class="text-gray-500">No hay tarjetas próximas a vencer en los próximos {{ $diasAlerta }} días</span>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <!-- Paginación -->
    <div class="mt-4">
        {{ $tarjetas->links() }}
    </div>
</div>