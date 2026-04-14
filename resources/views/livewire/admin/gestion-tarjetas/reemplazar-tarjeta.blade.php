<div>
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <flux:heading size="xl">Reemplazar Tarjeta RFID</flux:heading>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Reemplaza una tarjeta dañada o perdida por una nueva, conservando la información del usuario.
            </p>
        </div>
    </div>

    <!-- Progreso -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $paso >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-zinc-700 text-gray-500' }}">
                    1
                </div>
                <div class="ml-2">
                    <span class="text-sm font-medium">Tarjeta Vieja</span>
                    @if($tarjetaVieja)
                        <flux:badge color="green" size="sm" class="ml-2">Completado</flux:badge>
                    @endif
                </div>
            </div>
            <div class="flex-1 h-0.5 mx-4 {{ $paso >= 2 ? 'bg-blue-600' : 'bg-gray-200 dark:bg-zinc-700' }}"></div>
            <div class="flex items-center">
                <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $paso >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-zinc-700 text-gray-500' }}">
                    2
                </div>
                <div class="ml-2">
                    <span class="text-sm font-medium">Tarjeta Nueva</span>
                    @if($tarjetaNueva)
                        <flux:badge color="green" size="sm" class="ml-2">Completado</flux:badge>
                    @endif
                </div>
            </div>
            <div class="flex-1 h-0.5 mx-4 {{ $paso >= 3 ? 'bg-blue-600' : 'bg-gray-200 dark:bg-zinc-700' }}"></div>
            <div class="flex items-center">
                <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $paso >= 3 ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-zinc-700 text-gray-500' }}">
                    3
                </div>
                <div class="ml-2">
                    <span class="text-sm font-medium">Confirmar</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Paso 1: Tarjeta Vieja -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 {{ $paso != 1 ? 'opacity-60' : '' }}">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon name="exclamation-triangle" class="size-5 text-orange-500" />
                <flux:heading size="md">1. Tarjeta a Reemplazar</flux:heading>
            </div>
            
            @if($paso == 1)
                <!-- Toggle lector -->
                <div class="flex items-center justify-between mb-4 p-3 bg-gray-50 dark:bg-zinc-700 rounded-lg">
                    <div>
                        <span class="text-sm font-medium">Modo Lector Automático</span>
                        <p class="text-xs text-gray-500">Activa para leer la tarjeta VIEJA</p>
                    </div>
                    <button type="button" 
                            wire:click="activarLectorVieja"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                            :class="{
                                'bg-blue-600': $wire.lectorActivoVieja,
                                'bg-gray-200 dark:bg-zinc-600': !$wire.lectorActivoVieja
                            }">
                        <span class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                              :class="{
                                  'translate-x-5': $wire.lectorActivoVieja,
                                  'translate-x-0': !$wire.lectorActivoVieja
                              }"></span>
                    </button>
                </div>
                
                @if($lectorActivoVieja)
                <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="flex items-center gap-2">
                        <div class="animate-pulse">
                            <flux:icon name="radio" class="size-4 text-blue-600" />
                        </div>
                        <span class="text-sm text-blue-600">{{ $lectorMensaje }}</span>
                    </div>
                </div>
                @endif
                
                <div class="flex gap-2">
                    <div class="flex-1">
                        <flux:input 
                            wire:model="uid_tarjeta_vieja" 
                            placeholder="UID de la tarjeta vieja"
                            class="uppercase font-mono"
                            :readonly="$lectorActivoVieja"
                        />
                    </div>
                    <flux:button wire:click="buscarTarjetaVieja" variant="outline" :disabled="$lectorActivoVieja">
                        Buscar
                    </flux:button>
                </div>
            @endif
            
            @if($tarjetaVieja)
            <div class="mt-4 p-3 bg-gray-50 dark:bg-zinc-700 rounded-lg">
                <div class="flex items-center gap-2">
                    <flux:icon name="check-circle" class="size-5 text-green-500" />
                    <span class="font-medium">Tarjeta seleccionada</span>
                </div>
                <div class="grid grid-cols-2 gap-2 mt-2 text-sm">
                    <div><span class="text-gray-500">UID:</span> {{ $tarjetaVieja->uid_tarjeta }}</div>
                    <div><span class="text-gray-500">Estado:</span> {{ ucfirst($tarjetaVieja->estado) }}</div>
                    <div class="col-span-2"><span class="text-gray-500">Propietario:</span> {{ $tarjetaVieja->usuario->name ?? 'Sin asignar' }}</div>
                </div>
            </div>
            @endif
        </div>

        <!-- Paso 2: Tarjeta Nueva -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 {{ $paso < 2 ? 'opacity-50 pointer-events-none' : ($paso != 2 ? 'opacity-60' : '') }}">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon name="shield-check" class="size-5 text-green-500" />
                <flux:heading size="md">2. Tarjeta Nueva</flux:heading>
            </div>
            
            @if($paso >= 2 && $paso == 2)
                <div class="flex items-center justify-between mb-4 p-3 bg-gray-50 dark:bg-zinc-700 rounded-lg">
                    <div>
                        <span class="text-sm font-medium">Modo Lector Automático</span>
                        <p class="text-xs text-gray-500">Activa para leer la tarjeta NUEVA</p>
                    </div>
                    <button type="button" 
                            wire:click="activarLectorNueva"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                            :class="{
                                'bg-blue-600': $wire.lectorActivoNueva,
                                'bg-gray-200 dark:bg-zinc-600': !$wire.lectorActivoNueva
                            }">
                        <span class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                              :class="{
                                  'translate-x-5': $wire.lectorActivoNueva,
                                  'translate-x-0': !$wire.lectorActivoNueva
                              }"></span>
                    </button>
                </div>
                
                @if($lectorActivoNueva)
                <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="flex items-center gap-2">
                        <div class="animate-pulse">
                            <flux:icon name="radio" class="size-4 text-blue-600" />
                        </div>
                        <span class="text-sm text-blue-600">{{ $lectorMensaje }}</span>
                    </div>
                </div>
                @endif
                
                <div class="flex gap-2">
                    <div class="flex-1">
                        <flux:input 
                            wire:model="uid_tarjeta_nueva" 
                            placeholder="UID de la tarjeta nueva"
                            class="uppercase font-mono"
                            :readonly="$lectorActivoNueva"
                        />
                    </div>
                    <flux:button wire:click="buscarTarjetaNueva" variant="outline" :disabled="$lectorActivoNueva">
                        Buscar
                    </flux:button>
                </div>
            @endif
            
            @if($tarjetaNueva)
            <div class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <div class="flex items-center gap-2">
                    <flux:icon name="check-circle" class="size-5 text-green-500" />
                    <span class="font-medium">Tarjeta nueva lista</span>
                </div>
                <div class="mt-2 text-sm">
                    <span class="text-gray-500">UID:</span> {{ $tarjetaNueva->uid_tarjeta }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Paso 3: Confirmar y fechas -->
    @if($paso >= 3)
    <div class="mt-6 bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
        <div class="flex items-center gap-2 mb-4">
            <flux:icon name="calendar" class="size-5 text-blue-500" />
            <flux:heading size="md">3. Confirmar Reemplazo</flux:heading>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <flux:field>
                    <flux:label>Fecha Asignación <span class="text-red-500">*</span></flux:label>
                    <flux:input type="date" wire:model="fecha_asignacion" />
                    <flux:error name="fecha_asignacion" />
                </flux:field>
            </div>
            <div>
                <flux:field>
                    <flux:label>Fecha Vencimiento (opcional)</flux:label>
                    <flux:input type="date" wire:model="fecha_vencimiento" />
                    <flux:error name="fecha_vencimiento" />
                </flux:field>
            </div>
        </div>
        
        <flux:field class="mt-4">
            <flux:label>Observaciones</flux:label>
            <flux:textarea wire:model="observaciones" rows="3" placeholder="Motivo del reemplazo..." />
        </flux:field>
        
        <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
            <div class="flex items-center gap-2">
                <flux:icon name="information-circle" class="size-5 text-yellow-600" />
                <span class="text-sm text-yellow-700 dark:text-yellow-400">
                    La tarjeta vieja será marcada como "reemplazada" y se creará una nueva tarjeta con la misma información del usuario.
                </span>
            </div>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="mt-6 flex justify-between">
        <flux:button wire:click="pasoAnterior" variant="outline">
            <flux:icon name="chevron-left" class="size-4 mr-1" />
            Volver
        </flux:button>
        <flux:button wire:click="confirmarReemplazo" variant="primary">
            <flux:icon name="check" class="size-5 mr-2" />
            Confirmar Reemplazo
        </flux:button>
    </div>
    @endif

    <!-- Scripts -->
    <script>
        let lectorActivo = false;
        let buffer = '';
        let timeoutId = null;
        
        function procesarLectura(uid) {
            if (lectorActivo && uid && uid.length > 0) {
                buffer = '';
                Livewire.dispatch('procesarUidLeido', uid);
            }
        }
        
        document.addEventListener('keydown', function(event) {
            if (!lectorActivo) return;
            
            if (event.key === 'Enter') {
                if (buffer.length > 0) {
                    procesarLectura(buffer);
                    event.preventDefault();
                }
            } else if (event.key.length === 1 && !event.ctrlKey && !event.altKey && !event.metaKey) {
                buffer += event.key;
                if (timeoutId) clearTimeout(timeoutId);
                timeoutId = setTimeout(() => { buffer = ''; }, 100);
            }
        });
        
        document.addEventListener('livewire:init', () => {
            Livewire.on('alerta', (data) => {
                const datos = Array.isArray(data) ? data[0] : data;
                Swal.fire({
                    title: datos.titulo,
                    text: datos.texto,
                    icon: datos.icono,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Aceptar'
                });
            });
            
            Livewire.on('activar-lector', () => { lectorActivo = true; buffer = ''; });
            Livewire.on('desactivar-lector', () => { lectorActivo = false; buffer = ''; });
        });
    </script>
</div>