<div>
    <!-- Header con título y botón nuevo -->
    <div class="flex justify-between items-center mb-6">
        <flux:heading size="xl">Gestión de Roles</flux:heading>
        <flux:button wire:click="abrirModalNuevo" variant="primary">
            Nuevo Rol
        </flux:button>
    </div>

    <!-- Filtros -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o descripción..."
            icon="magnifying-glass" />

        <flux:select wire:model.live="filtroEstado" placeholder="Filtrar por estado">
            <option value="">Todos los estados</option>
            <option value="activo">Activos</option>
            <option value="inactivo">Inactivos</option>
        </flux:select>
    </div>

    <!-- Tabla de roles -->
    <flux:table>
        <flux:table.columns>
            <flux:table.column>
                <button wire:click="ordenar('nombre')"
                    class="flex items-center gap-1 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-200">
                    Nombre
                    @if ($sortField === 'nombre')
                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                            class="size-4" />
                    @endif
                </button>
            </flux:table.column>
            <flux:table.column>Descripción</flux:table.column>
            <flux:table.column>Estado</flux:table.column>
            <flux:table.column>Usuarios</flux:table.column>
            <flux:table.column class="text-right">Acciones</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($roles as $rol)
                <flux:table.row>
                    <flux:table.cell class="font-medium">{{ $rol->nombre }}</flux:table.cell>
                    <flux:table.cell>{{ $rol->descripcion ?? 'Sin descripción' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="$rol->estado === 'activo' ? 'green' : 'red'" size="sm">
                            {{ ucfirst($rol->estado) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="blue" size="sm">{{ $rol->usuarios->count() }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-right">
                        <flux:button wire:click="abrirModalEditar({{ $rol->id }})" variant="subtle" size="sm"
                            icon="pencil-square" aria-label="Editar rol" />
                        <flux:button wire:click="confirmarEliminar({{ $rol->id }})" variant="subtle" size="sm"
                            icon="trash" aria-label="Eliminar rol" />
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center py-10">
                        No se encontraron roles
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <!-- Paginación -->
    <div class="mt-4">
        {{ $roles->links() }}
    </div>

    <!-- Modal para crear/editar rol -->
    <flux:modal wire:model="showModal" class="max-w-lg">
        <form wire:submit.prevent="{{ $modalAction === 'crear' ? 'crear' : 'editar' }}">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $modalTitle }}</flux:heading>
                    <flux:separator class="mt-2" />
                </div>

                <!-- Nombre -->
                <flux:field>
                    <flux:label>Nombre del Rol <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="nombre" placeholder="Ej: Administrador" />
                    <flux:error name="nombre" />
                </flux:field>

                <!-- Descripción -->
                <flux:field>
                    <flux:label>Descripción</flux:label>
                    <flux:textarea wire:model="descripcion" rows="3"
                        placeholder="Describe las funciones de este rol..." />
                    <flux:error name="descripcion" />
                </flux:field>

                <!-- Estado (solo en edición) -->
                @if ($modalAction === 'editar')
                    <flux:field>
                        <flux:label>Estado</flux:label>
                        <flux:select wire:model="estado">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </flux:select>
                    </flux:field>
                @endif

                <flux:separator />

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="cerrarModal">
                        Cancelar
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $modalAction === 'crear' ? 'Crear' : 'Actualizar' }}
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
                    Livewire.dispatch('eliminar', { id: datos.id });
                }
            });
        });
    });
    </script>
</div>
