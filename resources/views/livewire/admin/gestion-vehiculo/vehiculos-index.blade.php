<div>
    <!-- Header con título y botón nuevo -->
    <div class="flex justify-between items-center mb-6">
        <flux:heading size="xl">Gestión de Vehículos</flux:heading>
        <flux:button wire:click="abrirModalNuevo" variant="primary">
            Registrar Vehículo
        </flux:button>
    </div>

    <!-- Filtros -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Buscador por placa -->
        <flux:input 
            wire:model.live.debounce.300ms="search" 
            placeholder="Buscar por placa..."
            icon="magnifying-glass" 
        />

        <!-- Filtro por usuario con búsqueda mejorado -->
        <div class="relative" wire:ignore.self 
             x-data="{ 
                 open: @entangle('mostrarDropdown'),
                 search: @entangle('searchUsuario')
             }"
             x-on:click.away="if(search.length === 0) { open = false; $wire.limpiarBusqueda() } else { open = false }">
            
            <div class="flex">
                <flux:input
                    wire:model.live="searchUsuario"
                    placeholder="Buscar usuario por nombre o documento..."
                    icon="user"
                    class="w-full"
                    x-on:focus="open = true"
                    x-on:keydown.escape="open = false"
                    x-on:keydown.enter.prevent=""
                />
                
                <!-- Botón para limpiar (aparece cuando hay texto) -->
                <div x-show="search.length > 0" 
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="relative -ml-8 flex items-center">
                    <button type="button" 
                            x-on:click="search = ''; open = false; $wire.limpiarBusqueda()"
                            class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none"
                            aria-label="Limpiar búsqueda">
                        <flux:icon name="x-mark" class="size-4" />
                    </button>
                </div>
            </div>
            
            <!-- Dropdown de resultados con animaciones -->
            <div x-show="open && $wire.usuariosFiltrados.length > 0"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute z-50 w-full mt-1 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                
                @foreach($usuariosFiltrados as $usuario)
                <div 
                    wire:click="seleccionarUsuario({{ $usuario->id }})"
                    class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-zinc-700 cursor-pointer transition-colors duration-150"
                    x-on:click="open = false"
                >
                    <div class="font-medium">{{ $usuario->name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $usuario->numero_documento }}</div>
                </div>
                @endforeach
                
                @if(count($usuariosFiltrados) === 0 && strlen($search) > 0)
                <div class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center">
                    No se encontraron usuarios
                </div>
                @endif
            </div>
        </div>

        <!-- Filtro por tipo -->
        <flux:select wire:model.live="filtroTipo" placeholder="Filtrar por tipo">
            <option value="">Todos los tipos</option>
            @foreach($tiposVehiculo as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </flux:select>

        <!-- Filtro por estado -->
        <flux:select wire:model.live="filtroEstado" placeholder="Filtrar por estado">
            <option value="">Todos los estados</option>
            <option value="activo">Activos</option>
            <option value="inactivo">Inactivos</option>
        </flux:select>
    </div>

    <!-- Tabla de vehículos -->
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Placa</flux:table.column>
            <flux:table.column>Propietario</flux:table.column>
            <flux:table.column>Marca / Modelo</flux:table.column>
            <flux:table.column>Color</flux:table.column>
            <flux:table.column>Tipo</flux:table.column>
            <flux:table.column>Principal</flux:table.column>
            <flux:table.column>Estado</flux:table.column>
            <flux:table.column class="text-right">Acciones</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($vehiculos as $vehiculo)
                <flux:table.row>
                    <flux:table.cell class="font-medium">{{ $vehiculo->placa }}</flux:table.cell>
                    <flux:table.cell>{{ $vehiculo->usuario->name ?? 'N/A' }}</flux:table.cell>
                    <flux:table.cell>
                        @if($vehiculo->marca || $vehiculo->modelo)
                            {{ $vehiculo->marca }} {{ $vehiculo->modelo }}
                        @else
                            <span class="text-gray-400">No especificado</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>{{ $vehiculo->color ?? 'N/A' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="blue" size="sm">
                            {{ $vehiculo->tipo_vehiculo === 'carro' ? 'Carro' : 'Moto' }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        @if($vehiculo->es_principal)
                            <flux:badge color="green" size="sm">Principal</flux:badge>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="$vehiculo->estado === 'activo' ? 'green' : 'red'" size="sm">
                            {{ ucfirst($vehiculo->estado) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-right">
                        <flux:button wire:click="abrirModalEditar({{ $vehiculo->id }})" variant="subtle" size="sm"
                            icon="pencil-square" aria-label="Editar vehículo" />
                        <flux:button wire:click="confirmarEliminar({{ $vehiculo->id }})" variant="subtle" size="sm"
                            icon="trash" aria-label="Eliminar vehículo" />
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="text-center py-10">
                        No se encontraron vehículos
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <!-- Paginación -->
    <div class="mt-4">
        {{ $vehiculos->links() }}
    </div>

    <!-- Modal para crear/editar vehículo -->
    <flux:modal wire:model="showModal" class="max-w-2xl">
        <form wire:submit.prevent="{{ $modalAction === 'crear' ? 'crear' : 'editar' }}">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $modalTitle }}</flux:heading>
                    <flux:separator class="mt-2" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Propietario con búsqueda mejorado -->
                    <flux:field class="md:col-span-2">
                        <flux:label>Propietario <span class="text-red-500">*</span></flux:label>
                        
                        @if($usuarioSeleccionado)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-zinc-700 rounded-lg mb-2">
                            <div>
                                <div class="font-medium">{{ $usuarioSeleccionado->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $usuarioSeleccionado->numero_documento }}</div>
                            </div>
                            <flux:button wire:click="limpiarUsuario" size="sm" variant="subtle" icon="x-mark" />
                        </div>
                        @endif
                        
                        <div class="relative" wire:ignore.self
                             x-data="{ 
                                 open: @entangle('mostrarDropdown'),
                                 search: @entangle('searchUsuario')
                             }"
                             x-on:click.away="if(search.length === 0) { open = false; $wire.limpiarBusqueda() } else { open = false }">
                            
                            <div class="flex">
                                <flux:input
                                    wire:model.live="searchUsuario"
                                    placeholder="Buscar usuario por nombre o documento..."
                                    icon="magnifying-glass"
                                    class="w-full"
                                    x-on:focus="open = true"
                                    x-on:keydown.escape="open = false"
                                    x-on:keydown.enter.prevent=""
                                />
                                
                                <div x-show="search.length > 0" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     class="relative -ml-8 flex items-center">
                                    <button type="button" 
                                            x-on:click="search = ''; open = false; $wire.limpiarBusqueda()"
                                            class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none"
                                            aria-label="Limpiar búsqueda">
                                        <flux:icon name="x-mark" class="size-4" />
                                    </button>
                                </div>
                            </div>
                            
                            <div x-show="open && $wire.usuariosFiltrados.length > 0"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute z-50 w-full mt-1 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                
                                @foreach($usuariosFiltrados as $usuario)
                                <div 
                                    wire:click="seleccionarUsuario({{ $usuario->id }})"
                                    class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-zinc-700 cursor-pointer transition-colors duration-150"
                                    x-on:click="open = false"
                                >
                                    <div class="font-medium">{{ $usuario->name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $usuario->numero_documento }}</div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <flux:error name="user_id" />
                    </flux:field>

                    <!-- Placa -->
                    <flux:field>
                        <flux:label>Placa</flux:label>
                        <flux:input wire:model="placa" placeholder="ABC123" class="uppercase" />
                        <flux:error name="placa" />
                    </flux:field>

                    <!-- Tipo de Vehículo -->
                    <flux:field>
                        <flux:label>Tipo <span class="text-red-500">*</span></flux:label>
                        <flux:select wire:model="tipo_vehiculo">
                            @foreach($tiposVehiculo as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="tipo_vehiculo" />
                    </flux:field>

                    <!-- Marca -->
                    <flux:field>
                        <flux:label>Marca</flux:label>
                        <flux:input wire:model="marca" placeholder="Ej: Mazda" />
                        <flux:error name="marca" />
                    </flux:field>

                    <!-- Modelo -->
                    <flux:field>
                        <flux:label>Modelo</flux:label>
                        <flux:input wire:model="modelo" placeholder="Ej: 2020" />
                        <flux:error name="modelo" />
                    </flux:field>

                    <!-- Color -->
                    <flux:field>
                        <flux:label>Color</flux:label>
                        <flux:input wire:model="color" placeholder="Ej: Rojo" />
                        <flux:error name="color" />
                    </flux:field>

                    <!-- Vehículo Principal -->
                    <flux:field class="md:col-span-2">
                        <div class="flex items-center gap-2">
                            <flux:checkbox wire:model="es_principal" id="es_principal" />
                            <flux:label for="es_principal">Marcar como vehículo principal</flux:label>
                        </div>
                    </flux:field>

                    <!-- Estado (solo en edición) -->
                    @if($modalAction === 'editar')
                    <flux:field>
                        <flux:label>Estado</flux:label>
                        <flux:select wire:model="estado">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </flux:select>
                    </flux:field>
                    @endif
                </div>

                <flux:separator />

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="cerrarModal">
                        Cancelar
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $modalAction === 'crear' ? 'Registrar Vehículo' : 'Actualizar Vehículo' }}
                    </flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    <!-- Script para SweetAlert -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('alerta', (data) => {
                const datos = Array.isArray(data) ? data[0] : data;
                
                Swal.fire({
                    title: datos.titulo,
                    text: datos.texto,
                    icon: datos.icono,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Aceptar',
                    timer: datos.icono === 'success' ? 2000 : undefined,
                    timerProgressBar: true,
                    showConfirmButton: datos.icono !== 'success'
                });
            });

            Livewire.on('confirmar-eliminar', (data) => {
                const datos = Array.isArray(data) ? data[0] : data;
                
                Swal.fire({
                    title: datos.titulo,
                    text: datos.texto,
                    icon: datos.icono,
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('eliminar', { id: datos.id });
                        
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: 'El vehículo ha sido eliminado correctamente.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                });
            });
        });
    </script>
</div>