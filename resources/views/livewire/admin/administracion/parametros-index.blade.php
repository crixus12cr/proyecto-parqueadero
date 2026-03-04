<div>
    <!-- Header con título -->
    <div class="flex justify-between items-center mb-6">
        <flux:heading size="xl">Parámetros del Sistema</flux:heading>
    </div>

    <!-- Tabs de navegación -->
    <div class="mb-6 border-b border-gray-200 dark:border-zinc-700">
        <nav class="flex space-x-8">
            <button wire:click="cambiarTab('generales')" 
                class="pb-4 px-1 text-sm font-medium border-b-2 transition-colors
                {{ $activeTab === 'generales' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                Configuración General
            </button>
            <button wire:click="cambiarTab('tipos_usuario')" 
                class="pb-4 px-1 text-sm font-medium border-b-2 transition-colors
                {{ $activeTab === 'tipos_usuario' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                Tipos de Usuario
            </button>
        </nav>
    </div>

    <!-- Formulario principal -->
    <form wire:submit.prevent="guardarTodo">
        <!-- TAB: CONFIGURACIÓN GENERAL -->
        @if($activeTab === 'generales')
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 space-y-6">
            <div>
                <flux:heading size="lg">Configuración General</flux:heading>
                <flux:separator class="mt-2" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Capacidad Total -->
                <flux:field>
                    <flux:label>Capacidad Total del Parqueadero</flux:label>
                    <flux:input type="number" wire:model="capacidad_total" min="1" max="9999" />
                    <flux:error name="capacidad_total" />
                </flux:field>

                <!-- Alerta de Ocupación -->
                <flux:field>
                    <flux:label>Alerta de Ocupación (%)</flux:label>
                    <flux:input type="number" wire:model="alerta_ocupacion" min="1" max="100" />
                    <flux:error name="alerta_ocupacion" />
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Enviar alerta cuando se alcance este porcentaje</p>
                </flux:field>

                <!-- Horario Apertura -->
                <flux:field>
                    <flux:label>Horario de Apertura</flux:label>
                    <flux:input type="time" wire:model="horario_apertura" />
                    <flux:error name="horario_apertura" />
                </flux:field>

                <!-- Horario Cierre -->
                <flux:field>
                    <flux:label>Horario de Cierre</flux:label>
                    <flux:input type="time" wire:model="horario_cierre" />
                    <flux:error name="horario_cierre" />
                </flux:field>

                <!-- Tiempo de Gracia -->
                <flux:field>
                    <flux:label>Tiempo de Gracia (minutos)</flux:label>
                    <flux:input type="number" wire:model="tiempo_gracia" min="0" max="60" />
                    <flux:error name="tiempo_gracia" />
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Tiempo para salir después del registro</p>
                </flux:field>

                <!-- Intentos Máximos RFID -->
                <flux:field>
                    <flux:label>Intentos Máximos RFID</flux:label>
                    <flux:input type="number" wire:model="intentos_maximos_rfid" min="1" max="10" />
                    <flux:error name="intentos_maximos_rfid" />
                </flux:field>

                <!-- Días Hábiles -->
                <flux:field class="md:col-span-2">
                    <flux:label>Días Hábiles</flux:label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-2">
                        @foreach($diasDisponibles as $dia)
                        <label class="flex items-center space-x-2">
                            <flux:checkbox wire:model="dias_habiles" value="{{ $dia }}" />
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $dia }}</span>
                        </label>
                        @endforeach
                    </div>
                    <flux:error name="dias_habiles" />
                </flux:field>

                <!-- Notificaciones Email -->
                <flux:field class="md:col-span-2">
                    <div class="flex items-center gap-2">
                        <flux:checkbox wire:model.live="notificar_email" id="notificar_email" />
                        <flux:label for="notificar_email">Activar notificaciones por email</flux:label>
                    </div>
                </flux:field>

                @if($notificar_email)
                <!-- Email de Notificaciones -->
                <flux:field class="md:col-span-2">
                    <flux:label>Email para notificaciones</flux:label>
                    <flux:input type="email" wire:model="email_notificaciones" placeholder="admin@universidad.edu" />
                    <flux:error name="email_notificaciones" />
                </flux:field>
                @endif
            </div>
        </div>
        @endif

        <!-- TAB: TIPOS DE USUARIO -->
        @if($activeTab === 'tipos_usuario')
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 space-y-6">
            <div>
                <flux:heading size="lg">Configuración por Tipo de Usuario</flux:heading>
                <flux:separator class="mt-2" />
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    Define las horas máximas de estadía y prioridad de acceso para cada tipo de usuario.
                </p>
            </div>

            <div class="space-y-4">
                @foreach($tipos_usuario as $id => $tipo)
                <div class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-3">
                        <div class="flex items-center gap-2">
                            <flux:heading size="md">{{ $tipo['nombre'] }}</flux:heading>
                            @if($tipoEditando !== $id)
                                <flux:badge color="blue" size="sm">ID: {{ $id }}</flux:badge>
                            @endif
                        </div>
                        @if($tipoEditando !== $id)
                        <flux:button wire:click="editarTipoUsuario({{ $id }})" size="sm" variant="subtle" icon="pencil-square">
                            Editar
                        </flux:button>
                        @endif
                    </div>

                    @if($tipoEditando === $id)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Horas Máximas de Estadía</flux:label>
                            <flux:input 
                                type="number" 
                                wire:model="tipos_usuario.{{ $id }}.horas_maximas_estadia" 
                                min="1" 
                                max="48"
                                placeholder="Ej: 8" 
                            />
                            <flux:error name="tipos_usuario.{{ $id }}.horas_maximas_estadia" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Prioridad de Acceso (1-5)</flux:label>
                            <flux:select wire:model="tipos_usuario.{{ $id }}.prioridad_acceso">
                                <option value="1">1 - Más alta</option>
                                <option value="2">2</option>
                                <option value="3">3 - Media</option>
                                <option value="4">4</option>
                                <option value="5">5 - Más baja</option>
                            </flux:select>
                            <flux:error name="tipos_usuario.{{ $id }}.prioridad_acceso" />
                        </flux:field>
                    </div>

                    <div class="flex justify-end gap-2 mt-4">
                        <flux:button wire:click="cancelarEdicion" size="sm" variant="ghost">
                            Cancelar
                        </flux:button>
                        <flux:button wire:click="editarTipoUsuario(null)" size="sm" variant="primary">
                            Guardar Cambios
                        </flux:button>
                    </div>
                    @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Horas máximas:</span>
                            <flux:badge color="green" size="sm" class="ml-2">{{ $tipo['horas_maximas_estadia'] }} horas</flux:badge>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Prioridad de acceso:</span>
                            <flux:badge :color="$tipo['prioridad_acceso'] <= 2 ? 'yellow' : ($tipo['prioridad_acceso'] <= 3 ? 'blue' : 'gray')" size="sm" class="ml-2">
                                {{ $tipo['prioridad_acceso'] }}
                            </flux:badge>
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Botón Guardar (siempre visible) -->
        <div class="mt-6 flex justify-end">
            <flux:button type="submit" variant="primary">
                Guardar Cambios
            </flux:button>
        </div>
    </form>

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
                    confirmButtonText: 'Aceptar'
                });
            });
        });
    </script>
</div>