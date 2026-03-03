<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\TipoUsuario;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los roles
        $roles = Rol::all();
        
        // Obtener tipos de usuario
        $tipoTrabajador = TipoUsuario::where('nombre', 'Trabajador')->first();
        $tipoProfesor = TipoUsuario::where('nombre', 'Profesor')->first();

        // USUARIO 1: Administrador Principal (todos los roles)
        $admin1 = User::create([
            'name' => 'Administrador Principal',
            'email' => 'admin.principal@campuspark.edu',
            'password' => bcrypt('admin123'),
            'tipo_usuario_id' => $tipoTrabajador->id,
            'numero_documento' => '1000000001',
            'telefono' => '3001112233',
            'estado' => 'activo',
        ]);

        // Asignar TODOS los roles al admin1
        $admin1->roles()->attach($roles->pluck('id')->toArray());

        // USUARIO 2: Administrador Secundario (todos los roles)
        $admin2 = User::create([
            'name' => 'Administrador Secundario',
            'email' => 'admin.secundario@campuspark.edu',
            'password' => bcrypt('admin123'),
            'tipo_usuario_id' => $tipoProfesor->id,
            'numero_documento' => '1000000002',
            'telefono' => '3004445566',
            'estado' => 'activo',
        ]);

        // Asignar TODOS los roles al admin2
        $admin2->roles()->attach($roles->pluck('id')->toArray());

        // USUARIO 3: Vigilante (solo rol vigilante)
        $vigilante = User::create([
            'name' => 'Carlos Vigilante',
            'email' => 'vigilante@campuspark.edu',
            'password' => bcrypt('vigilante123'),
            'tipo_usuario_id' => $tipoTrabajador->id,
            'numero_documento' => '1000000003',
            'telefono' => '3007778899',
            'estado' => 'activo',
        ]);

        // Asignar solo rol vigilante
        $rolVigilante = Rol::where('nombre', 'Vigilante')->first();
        $vigilante->roles()->attach($rolVigilante->id);

        // USUARIO 4: Profesor (rol profesor)
        $profesor = User::create([
            'name' => 'María Profesora',
            'email' => 'profesora@campuspark.edu',
            'password' => bcrypt('profesor123'),
            'tipo_usuario_id' => $tipoProfesor->id,
            'numero_documento' => '1000000004',
            'telefono' => '3001113344',
            'estado' => 'activo',
        ]);

        $rolProfesor = Rol::where('nombre', 'Profesor')->first();
        $profesor->roles()->attach($rolProfesor->id);

        // USUARIO 5: Estudiante (rol estudiante)
        $estudiante = User::create([
            'name' => 'Juan Estudiante',
            'email' => 'estudiante@campuspark.edu',
            'password' => bcrypt('estudiante123'),
            'tipo_usuario_id' => TipoUsuario::where('nombre', 'Estudiante')->first()->id,
            'numero_documento' => '1000000005',
            'telefono' => '3005556677',
            'estado' => 'activo',
        ]);

        $rolEstudiante = Rol::where('nombre', 'Estudiante')->first();
        $estudiante->roles()->attach($rolEstudiante->id);

        $this->command->info('Usuarios creados exitosamente');
        $this->command->info('Admin Principal: admin.principal@campuspark.edu / admin123');
        $this->command->info('Admin Secundario: admin.secundario@campuspark.edu / admin123');
        $this->command->info('Vigilante: vigilante@campuspark.edu / vigilante123');
        $this->command->info('Profesora: profesora@campuspark.edu / profesor123');
        $this->command->info('Estudiante: estudiante@campuspark.edu / estudiante123');
    }
}
