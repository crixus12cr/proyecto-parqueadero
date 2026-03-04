<div>
    <!-- Header con título y botón nuevo respaldo -->
    <div class="flex justify-between items-center mb-6">
        <flux:heading size="xl">Respaldo de Datos</flux:heading>
        <flux:button wire:click="abrirModalNuevoRespaldo" variant="primary">
            {{-- <flux:icon name="document-duplicate" class="size-5 mr-2" /> --}}
            Generar Respaldo
        </flux:button>
    </div>

    <!-- Tabs de navegación -->
    <div class="mb-6 border-b border-gray-200 dark:border-zinc-700">
        <nav class="flex space-x-8">
            <button wire:click="cambiarTab('historial')" 
                class="pb-4 px-1 text-sm font-medium border-b-2 transition-colors
                {{ $tab === 'historial' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                Historial de Respaldos
            </button>
            <button wire:click="cambiarTab('configuracion')" 
                class="pb-4 px-1 text-sm font-medium border-b-2 transition-colors
                {{ $tab === 'configuracion' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                Configuración
            </button>
        </nav>
    </div>

    @if($tab === 'historial')
        <!-- Estadísticas rápidas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Respaldos</div>
                <div class="text-2xl font-semibold">{{ $estadisticas['total'] }}</div>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Completados</div>
                <div class="text-2xl font-semibold text-green-600">{{ $estadisticas['completados'] }}</div>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Fallidos</div>
                <div class="text-2xl font-semibold text-red-600">{{ $estadisticas['fallidos'] }}</div>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Espacio Total</div>
                <div class="text-2xl font-semibold">{{ $espacioTotal }}</div>
            </div>
        </div>

        <!-- Tabla de respaldos -->
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Nombre</flux:table.column>
                <flux:table.column>Fecha</flux:table.column>
                <flux:table.column>Tipo</flux:table.column>
                <flux:table.column>Tamaño</flux:table.column>
                <flux:table.column>Estado</flux:table.column>
                <flux:table.column>Usuario</flux:table.column>
                <flux:table.column class="text-right">Acciones</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($respaldos as $respaldo)
                    <flux:table.row>
                        <flux:table.cell class="font-medium">{{ $respaldo->nombre }}</flux:table.cell>
                        <flux:table.cell>{{ $respaldo->fecha_generacion->format('d/m/Y H:i') }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="$respaldo->tipo === 'completo' ? 'blue' : 'gray'" size="sm">
                                {{ $respaldo->tipo === 'completo' ? 'Completo' : 'Base de Datos' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $respaldo->tamano_formateado }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="$respaldo->estado === 'completado' ? 'green' : 'red'" size="sm">
                                {{ ucfirst($respaldo->estado) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $respaldo->usuario->name }}</flux:table.cell>
                        <flux:table.cell class="text-right">
                            <flux:button wire:click="descargar({{ $respaldo->id }})" variant="subtle" size="sm"
                                icon="arrow-down-tray" aria-label="Descargar" />
                            <flux:button wire:click="confirmarRestaurar({{ $respaldo->id }})" variant="subtle" size="sm"
                                icon="arrow-path" aria-label="Restaurar" />
                            <flux:button wire:click="confirmarEliminar({{ $respaldo->id }})" variant="subtle" size="sm"
                                icon="trash" aria-label="Eliminar" />
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center py-10">
                            No hay respaldos generados
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <!-- Paginación -->
        <div class="mt-4">
            {{ $respaldos->links() }}
        </div>

    @elseif($tab === 'configuracion')
        <!-- Formulario de configuración -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
            <form wire:submit.prevent="guardarConfiguracion" class="space-y-6">
                <!-- Frecuencia -->
                <flux:field>
                    <flux:label>Frecuencia de Respaldo</flux:label>
                    <flux:select wire:model.live="frecuencia">
                        <option value="manual">Manual (solo cuando se solicite)</option>
                        <option value="diario">Diario</option>
                        <option value="semanal">Semanal</option>
                        <option value="mensual">Mensual</option>
                    </flux:select>
                </flux:field>

                @if($frecuencia !== 'manual')
                    <!-- Hora programada -->
                    <flux:field>
                        <flux:label>Hora programada</flux:label>
                        <flux:input type="time" wire:model="hora_programada" />
                        <flux:error name="hora_programada" />
                    </flux:field>
                @endif

                @if($frecuencia === 'semanal')
                    <!-- Día de la semana -->
                    <flux:field>
                        <flux:label>Día de la semana</flux:label>
                        <flux:select wire:model="dia_semana">
                            <option value="0">Domingo</option>
                            <option value="1">Lunes</option>
                            <option value="2">Martes</option>
                            <option value="3">Miércoles</option>
                            <option value="4">Jueves</option>
                            <option value="5">Viernes</option>
                            <option value="6">Sábado</option>
                        </flux:select>
                    </flux:field>
                @endif

                @if($frecuencia === 'mensual')
                    <!-- Día del mes -->
                    <flux:field>
                        <flux:label>Día del mes</flux:label>
                        <flux:input type="number" wire:model="dia_mes" min="1" max="31" />
                        <flux:error name="dia_mes" />
                    </flux:field>
                @endif

                <!-- Mantener respaldos -->
                <flux:field>
                    <flux:label>Número de respaldos a mantener</flux:label>
                    <flux:input type="number" wire:model="mantener_respaldos" min="1" max="100" />
                    <flux:error name="mantener_respaldos" />
                </flux:field>

                <!-- Incluir archivos -->
                <flux:field>
                    <div class="flex items-center gap-2">
                        <flux:checkbox wire:model="incluir_archivos" id="incluir_archivos" />
                        <flux:label for="incluir_archivos">Incluir archivos subidos (imágenes, documentos, etc.)</flux:label>
                    </div>
                </flux:field>

                <!-- Notificar por email -->
                <flux:field>
                    <flux:label>Notificar a (email)</flux:label>
                    <flux:input type="email" wire:model="notificar_email" placeholder="admin@campuspark.edu" />
                </flux:field>

                <!-- Activar respaldos automáticos -->
                <flux:field>
                    <div class="flex items-center gap-2">
                        <flux:checkbox wire:model="activo" id="activo" />
                        <flux:label for="activo">Activar respaldos automáticos</flux:label>
                    </div>
                </flux:field>

                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary">
                        Guardar Configuración
                    </flux:button>
                </div>
            </form>
        </div>
    @endif

    <!-- Modal para nuevo respaldo -->
    <flux:modal wire:model="showModal" class="max-w-lg">
        <form wire:submit.prevent="generarRespaldo">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Generar Nuevo Respaldo</flux:heading>
                    <flux:separator class="mt-2" />
                </div>

                <!-- Observaciones -->
                <flux:field>
                    <flux:label>Observaciones (opcional)</flux:label>
                    <flux:textarea wire:model="observaciones" rows="3"
                        placeholder="Notas sobre este respaldo..." />
                </flux:field>

                <flux:separator />

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="showModal = false">
                        Cancelar
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        Generar Respaldo
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
                        Livewire.dispatch('eliminar', datos.id);
                    }
                });
            });

            Livewire.on('confirmar-restauracion', (data) => {
                const datos = Array.isArray(data) ? data[0] : data;
                
                Swal.fire({
                    title: datos.titulo,
                    text: datos.texto,
                    icon: datos.icono,
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, restaurar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('restaurar', datos.id);
                    }
                });
            });
        });
    </script>
</div>