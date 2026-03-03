<?php

namespace Database\Seeders;

use App\Models\TipoUsuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TiposUsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            [
                'nombre' => 'Estudiante',
                'descripcion' => 'Estudiante activo de pregrado o posgrado',
                'horas_maximas_estadia' => 8,
                'prioridad_acceso' => 2,
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Profesor',
                'descripcion' => 'Docente de tiempo completo o cátedra',
                'horas_maximas_estadia' => 12,
                'prioridad_acceso' => 1,
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Trabajador',
                'descripcion' => 'Personal administrativo y de servicios',
                'horas_maximas_estadia' => 10,
                'prioridad_acceso' => 1,
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Invitado',
                'descripcion' => 'Visitantes externos con acceso temporal',
                'horas_maximas_estadia' => 4,
                'prioridad_acceso' => 3,
                'estado' => 'activo',
            ],
        ];

        foreach ($tipos as $tipo) {
            TipoUsuario::create($tipo);
        }
        
        $this->command->info('Tipos de usuario creados exitosamente');
    }
}
