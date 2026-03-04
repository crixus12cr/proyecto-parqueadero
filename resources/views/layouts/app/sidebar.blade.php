<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <!-- Plataforma Principal (Dashboard) - siempre visible -->
                <flux:sidebar.group heading="Plataforma">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <!-- Módulo de Gestión de Usuarios (Expandible) -->
                <flux:sidebar.group heading="Gestión de Usuarios" expandable icon="users" :expanded="request()->routeIs('usuarios*')">
                    <flux:sidebar.item icon="users" href="#" :current="request()->routeIs('usuarios.todos*')" wire:navigate>
                        {{ __('Todos los Usuarios') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="user-plus" href="#" :current="request()->routeIs('usuarios.crear*')" wire:navigate>
                        {{ __('Registrar Usuario') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="academic-cap" href="#" :current="request()->routeIs('usuarios.estudiantes*')" wire:navigate>
                        {{ __('Estudiantes') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="briefcase" href="#" :current="request()->routeIs('usuarios.profesores*')" wire:navigate>
                        {{ __('Profesores') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="wrench-screwdriver" href="#" :current="request()->routeIs('usuarios.trabajadores*')" wire:navigate>
                        {{ __('Trabajadores') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="user-group" href="#" :current="request()->routeIs('usuarios.invitados*')" wire:navigate>
                        {{ __('Invitados') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <!-- Módulo de Tarjetas RFID (Expandible) -->
                <flux:sidebar.group heading="Tarjetas RFID" expandable icon="identification" :expanded="request()->routeIs('tarjetas*')">
                    <flux:sidebar.item icon="identification" href="#" :current="request()->routeIs('tarjetas.inventario*')" wire:navigate>
                        {{ __('Inventario de Tarjetas') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="plus-circle" href="#" :current="request()->routeIs('tarjetas.asignar*')" wire:navigate>
                        {{ __('Asignar Tarjeta') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="arrow-path" href="#" :current="request()->routeIs('tarjetas.reemplazar*')" wire:navigate>
                        {{ __('Reemplazar Tarjeta') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="exclamation-triangle" href="#" :current="request()->routeIs('tarjetas.perdidas*')" wire:navigate>
                        {{ __('Tarjetas Perdidas') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clock" href="#" :current="request()->routeIs('tarjetas.por-vencer*')" wire:navigate badge="3">
                        {{ __('Próximas a Vencer') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <!-- Módulo de Vehículos (Expandible) -->
                <flux:sidebar.group heading="Vehículos" expandable icon="truck" :expanded="request()->routeIs('vehiculos*')">
                    <flux:sidebar.item icon="truck" href="#" :current="request()->routeIs('vehiculos.todos*')" wire:navigate>
                        {{ __('Todos los Vehículos') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="plus" href="#" :current="request()->routeIs('vehiculos.registrar*')" wire:navigate>
                        {{ __('Registrar Vehículo') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="magnifying-glass" href="#" :current="request()->routeIs('vehiculos.buscar*')" wire:navigate>
                        {{ __('Buscar por Placa') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <!-- Módulo de Control de Acceso (Expandible) -->
                <flux:sidebar.group heading="Control de Acceso" expandable icon="arrow-right-circle" :expanded="request()->routeIs('accesos*')">
                    <flux:sidebar.item icon="arrow-right-circle" href="#" :current="request()->routeIs('accesos.entrada*')" wire:navigate>
                        {{ __('Registrar Entrada') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="arrow-left-circle" href="#" :current="request()->routeIs('accesos.salida*')" wire:navigate>
                        {{ __('Registrar Salida') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clipboard-document-list" href="#" :current="request()->routeIs('accesos.historial*')" wire:navigate>
                        {{ __('Historial de Accesos') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clock" href="#" :current="request()->routeIs('accesos.en-curso*')" wire:navigate badge="24">
                        {{ __('Accesos en Curso') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <!-- Módulo de Monitoreo (Expandible) -->
                <flux:sidebar.group heading="Monitoreo" expandable icon="eye" :expanded="request()->routeIs('monitoreo*')">
                    <flux:sidebar.item icon="eye" href="#" :current="request()->routeIs('monitoreo.ocupacion*')" wire:navigate>
                        {{ __('Ocupación Actual') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="chart-bar" href="#" :current="request()->routeIs('monitoreo.estadisticas*')" wire:navigate>
                        {{ __('Estadísticas en Vivo') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="map" href="#" :current="request()->routeIs('monitoreo.mapa*')" wire:navigate>
                        {{ __('Mapa de Ocupación') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="bell" href="#" :current="request()->routeIs('monitoreo.alertas*')" wire:navigate badge="3">
                        {{ __('Alertas') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <!-- Módulo de Reportes (Expandible) -->
                <flux:sidebar.group heading="Reportes" expandable icon="document-text" :expanded="request()->routeIs('reportes*')">
                    <flux:sidebar.item icon="document-text" href="#" :current="request()->routeIs('reportes.diarios*')" wire:navigate>
                        {{ __('Reporte Diario') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="calendar" href="#" :current="request()->routeIs('reportes.mensuales*')" wire:navigate>
                        {{ __('Reporte Mensual') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="chart-pie" href="#" :current="request()->routeIs('reportes.estadisticas*')" wire:navigate>
                        {{ __('Estadísticas') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="arrow-down-tray" href="#" :current="request()->routeIs('reportes.exportar*')" wire:navigate>
                        {{ __('Exportar Datos') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <!-- Módulo de Seguridad (Expandible) -->
                <flux:sidebar.group heading="Seguridad" expandable icon="shield-check" :expanded="request()->routeIs('seguridad*') || request()->routeIs('lista-negra*') || request()->routeIs('incidencias*')">
                    <flux:sidebar.item icon="no-symbol" href="#" :current="request()->routeIs('lista-negra*')" wire:navigate>
                        {{ __('Lista Negra') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="exclamation-circle" href="#" :current="request()->routeIs('incidencias*')" wire:navigate badge="5">
                        {{ __('Incidencias') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="shield-check" href="#" :current="request()->routeIs('auditoria*')" wire:navigate>
                        {{ __('Auditoría') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <!-- Módulo de Visitantes (Expandible) -->
                <flux:sidebar.group heading="Visitantes" expandable icon="user-group" :expanded="request()->routeIs('visitantes*')">
                    <flux:sidebar.item icon="user-plus" href="#" :current="request()->routeIs('visitantes.registrar*')" wire:navigate>
                        {{ __('Registrar Visitante') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="users" href="#" :current="request()->routeIs('visitantes.activos*')" wire:navigate badge="8">
                        {{ __('Visitantes Activos') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clock" href="#" :current="request()->routeIs('visitantes.historial*')" wire:navigate>
                        {{ __('Historial Visitantes') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <!-- Módulo de Administración (Solo para admins) -->
                @if(auth()->user()->tieneAlgunRol(['Super Administrador', 'Administrador']))
                <flux:sidebar.group heading="Administración" expandable icon="cog-6-tooth" :expanded="request()->routeIs('admin*')">
                    <flux:sidebar.item icon="adjustments-vertical" href="#" :current="request()->routeIs('admin.parametros*')" wire:navigate>
                        {{ __('Parámetros del Sistema') }}
                    </flux:sidebar.item>
                    {{-- <flux:sidebar.item icon="circle-stack" href="#" :current="request()->routeIs('admin.backup*')" wire:navigate>
                        {{ __('Respaldo de Datos') }}
                    </flux:sidebar.item> --}}
                    <flux:sidebar.item 
    icon="circle-stack" 
    href="{{ route('admin.respaldos') }}"
    :current="request()->routeIs('admin.respaldos*')" 
    wire:navigate
>
    {{ __('Respaldo de Datos') }}
</flux:sidebar.item>
                    <flux:sidebar.item icon="users" href="{{ route('admin.roles') }}" :current="request()->routeIs('admin.roles*')" wire:navigate>
                        {{ __('Gestión de Roles') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:sidebar.spacer />

            <!-- Modo oscuro -->
            <flux:button x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" variant="subtle" aria-label="Toggle dark mode" class="justify-center" />

            <!-- Perfil de usuario con menú desplegable -->
            <flux:dropdown position="top" align="start">
                <flux:sidebar.profile :name="auth()->user()->name" :avatar="auth()->user()->foto" />
                
                <flux:menu class="min-w-64">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />
                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Configuración') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Cerrar Sesión') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Configuración') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Cerrar Sesión') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

            {{ $slot }}

        @fluxScripts
    </body>
</html>