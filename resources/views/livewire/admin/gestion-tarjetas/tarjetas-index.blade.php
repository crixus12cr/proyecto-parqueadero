<div>
    <!-- Header con título y botón nuevo -->
    <div class="flex justify-between items-center mb-6">
        <flux:heading size="xl">Inventario de Tarjetas RFID</flux:heading>
        <flux:button wire:click="abrirModalNuevo" variant="primary">
            Registrar Tarjeta
        </flux:button>
    </div>

    <!-- Filtros -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por UID, propietario o documento..."
            icon="magnifying-glass" />

        <!-- Filtro por usuario con búsqueda -->
        <div class="relative" wire:ignore.self x-data="{
            open: @entangle('mostrarDropdown'),
            search: @entangle('searchUsuario')
        }"
            x-on:click.away="if(search.length === 0) { open = false; $wire.limpiarBusqueda() } else { open = false }">

            <div class="flex">
                <flux:input wire:model.live="searchUsuario" placeholder="Buscar propietario por nombre o documento..."
                    icon="user" class="w-full" x-on:focus="open = true" x-on:keydown.escape="open = false"
                    x-on:keydown.enter.prevent="" />

                <div x-show="search.length > 0" x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    class="relative -ml-8 flex items-center">
                    <button type="button" x-on:click="search = ''; open = false; $wire.limpiarBusqueda()"
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

                @foreach ($usuariosFiltrados as $usuario)
                    <div wire:click="seleccionarUsuario({{ $usuario->id }})"
                        class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-zinc-700 cursor-pointer transition-colors duration-150"
                        x-on:click="open = false">
                        <div class="font-medium">{{ $usuario->name }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $usuario->numero_documento }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <flux:select wire:model.live="filtroEstado" placeholder="Filtrar por estado">
            <option value="">Todos los estados</option>
            @foreach ($estados as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </flux:select>
    </div>

    <!-- Tabla de tarjetas -->
    <flux:table>
        <flux:table.columns>
            <flux:table.column>UID Tarjeta</flux:table.column>
            <flux:table.column>Propietario</flux:table.column>
            <flux:table.column>Fecha Asignación</flux:table.column>
            <flux:table.column>Fecha Vencimiento</flux:table.column>
            <flux:table.column>Estado</flux:table.column>
            <flux:table.column>Último Uso</flux:table.column>
            <flux:table.column class="text-right">Acciones</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($tarjetas as $tarjeta)
                <flux:table.row>
                    <flux:table.cell class="font-mono font-medium">{{ $tarjeta->uid_tarjeta }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($tarjeta->usuario)
                            {{ $tarjeta->usuario->name }}
                            <div class="text-xs text-gray-500">{{ $tarjeta->usuario->numero_documento }}</div>
                        @else
                            <span class="text-gray-400">Sin asignar</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $tarjeta->fecha_asignacion ? \Carbon\Carbon::parse($tarjeta->fecha_asignacion)->format('d/m/Y') : '-' }}
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($tarjeta->fecha_vencimiento)
                            @php
                                $fechaVencimiento = \Carbon\Carbon::parse($tarjeta->fecha_vencimiento);
                                $hoy = \Carbon\Carbon::now()->startOfDay();
                                $diasRestantes = $hoy->diffInDays($fechaVencimiento, false);
                                
                                if ($diasRestantes == 0) {
                                    $textoDias = ' (Hoy)';
                                    $colorClase = 'text-red-600 dark:text-red-400 font-semibold';
                                } elseif ($diasRestantes < 0) {
                                    $textoDias = ' (Vencida)';
                                    $colorClase = 'text-gray-500 dark:text-gray-400';
                                } elseif ($diasRestantes <= 30) {
                                    $textoDias = ' (' . $diasRestantes . ' día' . ($diasRestantes != 1 ? 's' : '') . ')';
                                    $colorClase = 'text-red-600 dark:text-red-400 font-semibold';
                                } else {
                                    $textoDias = '';
                                    $colorClase = '';
                                }
                            @endphp

                            <span class="{{ $colorClase }}">
                                {{ $fechaVencimiento->format('d/m/Y') }}
                                @if($textoDias)
                                    <span class="text-xs">{{ $textoDias }}</span>
                                @endif
                            </span>
                        @else
                            -
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        @php
                            $color = match ($tarjeta->estado) {
                                'activa' => 'green',
                                'inactiva' => 'gray',
                                'perdida' => 'red',
                                'dañada' => 'orange',
                                'vencida' => 'red',
                                default => 'gray',
                            };
                        @endphp
                        <flux:badge :color="$color" size="sm">
                            {{ ucfirst($tarjeta->estado) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $tarjeta->ultimo_uso ? \Carbon\Carbon::parse($tarjeta->ultimo_uso)->format('d/m/Y H:i') : '-' }}
                    </flux:table.cell>
                    <flux:table.cell class="text-right">
                        <flux:button wire:click="abrirModalEditar({{ $tarjeta->id }})" variant="subtle" size="sm"
                            icon="pencil-square" aria-label="Editar tarjeta" />
                        <flux:button wire:click="confirmarEliminar({{ $tarjeta->id }})" variant="subtle"
                            size="sm" icon="trash" aria-label="Eliminar tarjeta" />
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="text-center py-10">
                        No se encontraron tarjetas RFID
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <!-- Paginación -->
    <div class="mt-4">
        {{ $tarjetas->links() }}
    </div>

    <!-- Modal para crear/editar tarjeta -->
    <flux:modal wire:model="showModal" class="max-w-2xl">
        <form wire:submit.prevent="{{ $modalAction === 'crear' ? 'crear' : 'editar' }}">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $modalTitle }}</flux:heading>
                    <flux:separator class="mt-2" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- UID Tarjeta con lector automático -->
                    <flux:field class="md:col-span-2">
                        <flux:label>UID Tarjeta <span class="text-red-500">*</span></flux:label>

                        <!-- Toggle para modo lector automático -->
                        <div class="flex items-center justify-between mb-3 p-3 bg-gray-50 dark:bg-zinc-700 rounded-lg">
                            <div>
                                <span class="text-sm font-medium">Modo Lector Automático</span>
                                <p class="text-xs text-gray-500">Activa para leer tarjetas automáticamente desde el
                                    lector USB</p>
                            </div>
                            <button type="button" wire:click="toggleLector"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                :class="{
                                    'bg-blue-600': $wire.lectorActivo,
                                    'bg-gray-200 dark:bg-zinc-600': !$wire.lectorActivo
                                }">
                                <span
                                    class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                    :class="{
                                        'translate-x-5': $wire.lectorActivo,
                                        'translate-x-0': !$wire.lectorActivo
                                    }"></span>
                            </button>
                        </div>

                        <!-- Mensaje del lector -->
                        @if ($lectorActivo)
                            <div
                                class="mb-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                <div class="flex items-center gap-2">
                                    <div class="animate-pulse">
                                        <flux:icon name="radio" class="size-4 text-blue-600" />
                                    </div>
                                    <span class="text-sm text-blue-600 dark:text-blue-400">{{ $lectorMensaje }}</span>
                                </div>
                            </div>
                        @endif

                        <div class="relative">
                            <flux:input wire:model="uid_tarjeta" placeholder="Ej: 04A3F2B1C5D6"
                                class="uppercase font-mono" :class="{ 'bg-gray-100 dark:bg-zinc-700': $lectorActivo }"
                                :readonly="$lectorActivo" />
                            @if ($lectorActivo && $uidLeido)
                                <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                    <flux:icon name="check-circle" class="size-5 text-green-500" />
                                </div>
                            @endif
                        </div>
                        <flux:error name="uid_tarjeta" />
                        <p class="text-xs text-gray-500 mt-1">
                            @if ($lectorActivo)
                                Acerca una tarjeta al lector USB para leer su UID automáticamente
                            @else
                                Ingresa manualmente el UID de la tarjeta
                            @endif
                        </p>
                    </flux:field>

                    <!-- Estado -->
                    <flux:field>
                        <flux:label>Estado <span class="text-red-500">*</span></flux:label>
                        <flux:select wire:model="estado">
                            @foreach ($estados as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="estado" />
                    </flux:field>

                    <!-- Propietario con búsqueda -->
                    <flux:field class="md:col-span-2">
                        <flux:label>Propietario</flux:label>

                        @if ($usuarioSeleccionado)
                            <div
                                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-zinc-700 rounded-lg mb-2">
                                <div>
                                    <div class="font-medium">{{ $usuarioSeleccionado->name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $usuarioSeleccionado->numero_documento }}</div>
                                </div>
                                <flux:button wire:click="limpiarUsuario" size="sm" variant="subtle"
                                    icon="x-mark" />
                            </div>
                        @endif

                        <div class="relative" wire:ignore.self x-data="{
                            open: @entangle('mostrarDropdown'),
                            search: @entangle('searchUsuario')
                        }"
                            x-on:click.away="if(search.length === 0) { open = false; $wire.limpiarBusqueda() } else { open = false }">

                            <div class="flex">
                                <flux:input wire:model.live="searchUsuario"
                                    placeholder="Buscar usuario por nombre o documento..." icon="magnifying-glass"
                                    class="w-full" x-on:focus="open = true" x-on:keydown.escape="open = false"
                                    x-on:keydown.enter.prevent="" />

                                <div x-show="search.length > 0" x-transition:enter="transition ease-out duration-100"
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

                                @foreach ($usuariosFiltrados as $usuario)
                                    <div wire:click="seleccionarUsuario({{ $usuario->id }})"
                                        class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-zinc-700 cursor-pointer transition-colors duration-150"
                                        x-on:click="open = false">
                                        <div class="font-medium">{{ $usuario->name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $usuario->numero_documento }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <flux:error name="user_id" />
                    </flux:field>

                    <!-- Fecha Asignación -->
                    <flux:field>
                        <flux:label>Fecha Asignación</flux:label>
                        <flux:input type="date" wire:model="fecha_asignacion" />
                        <flux:error name="fecha_asignacion" />
                    </flux:field>

                    <!-- Fecha Vencimiento -->
                    <flux:field>
                        <flux:label>Fecha Vencimiento</flux:label>
                        <flux:input type="date" wire:model="fecha_vencimiento" />
                        <flux:error name="fecha_vencimiento" />
                        <p class="text-xs text-gray-500 mt-1">Dejar en blanco si no tiene vencimiento</p>
                    </flux:field>

                    <!-- Observaciones -->
                    <flux:field class="md:col-span-2">
                        <flux:label>Observaciones</flux:label>
                        <flux:textarea wire:model="observaciones" rows="3"
                            placeholder="Notas adicionales sobre la tarjeta..." />
                    </flux:field>
                </div>

                <flux:separator />

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="cerrarModal">
                        Cancelar
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $modalAction === 'crear' ? 'Registrar Tarjeta' : 'Actualizar Tarjeta' }}
                    </flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    <!-- Scripts -->
    <script>
        // Variables para el lector RFID
        let lectorActivo = false;
        let buffer = '';
        let timeoutId = null;

        // Función para procesar la lectura RFID
        function procesarLectura(uid) {
            if (lectorActivo && uid && uid.length > 0) {
                buffer = '';
                Livewire.dispatch('procesarUidLeido', uid);
            }
        }

        // Escuchar eventos de teclado para el lector RFID
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
                timeoutId = setTimeout(() => {
                    buffer = '';
                }, 100);
            }
        });

        // Eventos de Livewire
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
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('eliminar', {
                            id: datos.id
                        });
                    }
                });
            });

            // Sincronizar estado del lector con Livewire
            Livewire.on('procesarUidLeido', (uid) => {
                // Ya está manejado por el método en el componente
            });
        });

        // Sincronizar el estado del lector con la variable global
        document.addEventListener('livewire:navigating', () => {
            // Resetear estado al navegar
            lectorActivo = false;
            buffer = '';
            if (timeoutId) clearTimeout(timeoutId);
        });
    </script>
</div>
