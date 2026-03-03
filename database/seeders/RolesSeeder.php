<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'Super Administrador',
                'descripcion' => 'Acceso total al sistema, puede gestionar administradores y configuración global',
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Administrador',
                'descripcion' => 'Gestiona usuarios, tarjetas, reportes y parámetros del sistema',
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Vigilante',
                'descripcion' => 'Control de acceso diario, registro manual de entradas/salidas, reportar incidencias',
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Profesor',
                'descripcion' => 'Acceso como docente al parqueadero',
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Estudiante',
                'descripcion' => 'Acceso como estudiante al parqueadero',
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Trabajador',
                'descripcion' => 'Personal administrativo y de servicios',
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Consulta',
                'descripcion' => 'Solo visualización de reportes y monitoreo',
                'estado' => 'activo',
            ],
        ];

        foreach ($roles as $rol) {
            Rol::create($rol);
        }
        
        $this->command->info('Roles creados exitosamente');
    }
}
