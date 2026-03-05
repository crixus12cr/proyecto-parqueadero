<div>
    <!-- Header con título y botón nuevo -->
    <div class="flex justify-between items-center mb-6">
        <flux:heading size="xl">Gestión de Usuarios</flux:heading>
        <flux:button wire:click="abrirModalNuevo" variant="primary">
            Nuevo Usuario
        </flux:button>
    </div>

    <!-- Filtros -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <flux:input 
            wire:model.live.debounce.300ms="search" 
            placeholder="Buscar por nombre, email o documento..."
            icon="magnifying-glass" 
        />

        <flux:select wire:model.live="filtroTipo" placeholder="Filtrar por tipo">
            <option value="">Todos los tipos</option>
            @foreach($tiposUsuario as $tipo)
                <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="filtroEstado" placeholder="Filtrar por estado">
            <option value="">Todos los estados</option>
            <option value="activo">Activos</option>
            <option value="inactivo">Inactivos</option>
        </flux:select>
    </div>

    <!-- Tabla de usuarios -->
    <flux:table>
        <flux:table.columns>
            <flux:table.column>
                <button wire:click="ordenar('name')"
                    class="flex items-center gap-1 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-200">
                    Nombre
                    @if ($sortField === 'name')
                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-4" />
                    @endif
                </button>
            </flux:table.column>
            <flux:table.column>Email</flux:table.column>
            <flux:table.column>Documento</flux:table.column>
            <flux:table.column>Tipo</flux:table.column>
            <flux:table.column>Roles</flux:table.column>
            <flux:table.column>Estado</flux:table.column>
            <flux:table.column class="text-right">Acciones</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($usuarios as $usuario)
                <flux:table.row>
                    <flux:table.cell class="font-medium">
                        <div class="flex items-center gap-2">
                            @if($usuario->foto)
                                <img src="{{ Storage::url($usuario->foto) }}" class="w-8 h-8 rounded-full object-cover">
                            @else
                                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-zinc-700 flex items-center justify-center text-xs font-medium">
                                    {{ substr($usuario->name, 0, 2) }}
                                </div>
                            @endif
                            {{ $usuario->name }}
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>{{ $usuario->email }}</flux:table.cell>
                    <flux:table.cell>{{ $usuario->numero_documento }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="blue" size="sm">
                            {{ $usuario->tipoUsuario->nombre ?? 'N/A' }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex flex-wrap gap-1">
                            @foreach($usuario->roles as $rol)
                                <flux:badge color="gray" size="sm">{{ $rol->nombre }}</flux:badge>
                            @endforeach
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="$usuario->estado === 'activo' ? 'green' : 'red'" size="sm">
                            {{ ucfirst($usuario->estado) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-right">
                        <flux:button wire:click="abrirModalEditar({{ $usuario->id }})" variant="subtle" size="sm"
                            icon="pencil-square" aria-label="Editar usuario" />
                        <flux:button wire:click="confirmarEliminar({{ $usuario->id }})" variant="subtle" size="sm"
                            icon="trash" aria-label="Eliminar usuario" />
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="text-center py-10">
                        No se encontraron usuarios
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <!-- Paginación -->
    <div class="mt-4">
        {{ $usuarios->links() }}
    </div>

    <!-- Modal para crear/editar usuario -->
    <flux:modal wire:model="showModal" class="max-w-2xl">
        <form wire:submit.prevent="{{ $modalAction === 'crear' ? 'crear' : 'editar' }}" enctype="multipart/form-data">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $modalTitle }}</flux:heading>
                    <flux:separator class="mt-2" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Nombre -->
                    <flux:field class="md:col-span-2">
                        <flux:label>Nombre Completo <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="name" placeholder="Ej: Juan Pérez" />
                        <flux:error name="name" />
                    </flux:field>

                    <!-- Email -->
                    <flux:field>
                        <flux:label>Email <span class="text-red-500">*</span></flux:label>
                        <flux:input type="email" wire:model="email" placeholder="ejemplo@universidad.edu" />
                        <flux:error name="email" />
                    </flux:field>

                    <!-- Número de Documento -->
                    <flux:field>
                        <flux:label>Número de Documento <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="numero_documento" placeholder="1234567890" />
                        <flux:error name="numero_documento" />
                    </flux:field>

                    <!-- Contraseña -->
                    <flux:field>
                        <flux:label>{{ $modalAction === 'crear' ? 'Contraseña' : 'Nueva Contraseña (opcional)' }} <span class="text-red-500">{{ $modalAction === 'crear' ? '*' : '' }}</span></flux:label>
                        <flux:input type="password" wire:model="password" placeholder="••••••••" />
                        <flux:error name="password" />
                    </flux:field>

                    <!-- Confirmar Contraseña -->
                    <flux:field>
                        <flux:label>Confirmar Contraseña</flux:label>
                        <flux:input type="password" wire:model="password_confirmation" placeholder="••••••••" />
                    </flux:field>

                    <!-- Teléfono -->
                    <flux:field>
                        <flux:label>Teléfono</flux:label>
                        <flux:input wire:model="telefono" placeholder="3001234567" />
                        <flux:error name="telefono" />
                    </flux:field>

                    <!-- Tipo de Usuario -->
                    <flux:field>
                        <flux:label>Tipo de Usuario <span class="text-red-500">*</span></flux:label>
                        <flux:select wire:model="tipo_usuario_id">
                            <option value="">Seleccione un tipo</option>
                            @foreach($tiposUsuario as $tipo)
                                <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="tipo_usuario_id" />
                    </flux:field>

                    <!-- Foto -->
                    <flux:field>
                        <flux:label>Foto de Perfil</flux:label>
                        <input type="file" wire:model="foto" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-zinc-700 dark:file:text-zinc-200" />
                        <flux:error name="foto" />
                        
                        @if($foto_url && !$foto)
                            <div class="mt-2">
                                <img src="{{ Storage::url($foto_url) }}" class="w-20 h-20 rounded-full object-cover">
                            </div>
                        @endif
                        
                        @if($foto)
                            <div class="mt-2">
                                <img src="{{ $foto->temporaryUrl() }}" class="w-20 h-20 rounded-full object-cover">
                            </div>
                        @endif
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

                    <!-- Roles -->
                    <flux:field class="md:col-span-2">
                        <flux:label>Roles</flux:label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mt-2">
                            @foreach($rolesDisponibles as $rol)
                            <label class="flex items-center space-x-2">
                                <flux:checkbox wire:model="roles" value="{{ $rol->id }}" />
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $rol->nombre }}</span>
                            </label>
                            @endforeach
                        </div>
                    </flux:field>
                </div>

                <flux:separator />

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="cerrarModal">
                        Cancelar
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $modalAction === 'crear' ? 'Crear Usuario' : 'Actualizar Usuario' }}
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
                        Livewire.dispatch('eliminar', { id: datos.id });
                    }
                });
            });
        });
    </script>
</div>