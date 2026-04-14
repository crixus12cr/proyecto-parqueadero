<div>
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <flux:heading size="xl">Asignar Tarjeta RFID</flux:heading>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Asocia una tarjeta RFID existente a un usuario del sistema.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Panel izquierdo: Detectar tarjeta -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon name="identification" class="size-5 text-blue-500" />
                <flux:heading size="md">1. Detectar Tarjeta</flux:heading>
            </div>
            
            <!-- Toggle lector -->
            <div class="flex items-center justify-between mb-4 p-3 bg-gray-50 dark:bg-zinc-700 rounded-lg">
                <div>
                    <span class="text-sm font-medium">Modo Lector Automático</span>
                    <p class="text-xs text-gray-500">Activa para leer tarjetas automáticamente</p>
                </div>
                <button type="button" 
                        wire:click="toggleLector"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                        :class="{
                            'bg-blue-600': $wire.lectorActivo,
                            'bg-gray-200 dark:bg-zinc-600': !$wire.lectorActivo
                        }">
                    <span class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                          :class="{
                              'translate-x-5': $wire.lectorActivo,
                              'translate-x-0': !$wire.lectorActivo
                          }"></span>
                </button>
            </div>
            
            @if($lectorActivo)
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
                        wire:model="uid_tarjeta" 
                        placeholder="UID de la tarjeta"
                        class="uppercase font-mono"
                        :readonly="$lectorActivo"
                    />
                </div>
                <flux:button wire:click="buscarTarjeta" variant="outline" :disabled="$lectorActivo">
    Buscar
</flux:button>
            </div>
            
            @if($tarjetaEncontrada)
            <div class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <div class="flex items-center gap-2">
                    <flux:icon name="check-circle" class="size-5 text-green-500" />
                    <span class="font-medium text-green-700">Tarjeta disponible</span>
                </div>
                <div class="grid grid-cols-2 gap-2 mt-2 text-sm">
                    <div><span class="text-gray-500">UID:</span> {{ $tarjetaEncontrada->uid_tarjeta }}</div>
                    <div><span class="text-gray-500">Registro:</span> {{ $tarjetaEncontrada->created_at->format('d/m/Y') }}</div>
                </div>
            </div>
            @endif
        </div>

        <!-- Panel derecho: Seleccionar usuario y fechas -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon name="users" class="size-5 text-blue-500" />
                <flux:heading size="md">2. Seleccionar Usuario</flux:heading>
            </div>
            
            @if($usuarioSeleccionado)
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-zinc-700 rounded-lg mb-3">
                <div>
                    <div class="font-medium">{{ $usuarioSeleccionado->name }}</div>
                    <div class="text-sm text-gray-500">{{ $usuarioSeleccionado->numero_documento }} - {{ $usuarioSeleccionado->email }}</div>
                </div>
                <flux:button wire:click="limpiarUsuario" size="sm" variant="subtle" icon="x-mark" />
            </div>
            @endif
            
            <div class="relative">
                <flux:input
                    wire:model.live.debounce.300ms="searchUsuario"
                    placeholder="Buscar por nombre, documento o email..."
                    icon="magnifying-glass"
                    x-on:focus="$wire.mostrarDropdown = true"
                    x-on:blur="setTimeout(() => { $wire.mostrarDropdown = false }, 200)"
                />
                
                @if($mostrarDropdown && count($usuariosFiltrados) > 0)
                <div class="absolute z-50 w-full mt-1 bg-white dark:bg-zinc-800 border rounded-lg shadow-lg max-h-60 overflow-y-auto">
                    @foreach($usuariosFiltrados as $usuario)
                    <div 
                        wire:click="seleccionarUsuario({{ $usuario->id }})"
                        class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-zinc-700 cursor-pointer"
                    >
                        <div class="font-medium">{{ $usuario->name }}</div>
                        <div class="text-sm text-gray-500">{{ $usuario->numero_documento }} - {{ $usuario->email }}</div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="mt-6">
                <div class="flex items-center gap-2 mb-4">
                    <flux:icon name="calendar" class="size-5 text-blue-500" />
                    <flux:heading size="md">3. Fechas de Asignación</flux:heading>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Fecha Asignación *</flux:label>
                        <flux:input type="date" wire:model="fecha_asignacion" />
                        <flux:error name="fecha_asignacion" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Fecha Vencimiento</flux:label>
                        <flux:input type="date" wire:model="fecha_vencimiento" />
                        <flux:error name="fecha_vencimiento" />
                    </flux:field>
                </div>

                <flux:field class="mt-4">
                    <flux:label>Observaciones</flux:label>
                    <flux:textarea wire:model="observaciones" rows="2" placeholder="Notas adicionales..." />
                </flux:field>
            </div>
        </div>
    </div>

    <!-- Botón Asignar -->
    <div class="mt-6 flex justify-end">
        <flux:button wire:click="asignar" variant="primary">
    <flux:icon name="check" class="size-5 mr-2" />
    Asignar Tarjeta
</flux:button>
    </div>

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