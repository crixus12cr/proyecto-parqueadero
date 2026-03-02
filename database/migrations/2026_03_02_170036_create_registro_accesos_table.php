<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('registro_accesos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarjeta_rfid_id')->nullable()->constrained('tarjeta_rfids');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('vehiculo_id')->constrained('vehiculos');
            $table->enum('tipo_acceso', ['entrada', 'salida']);
            $table->enum('metodo_acceso', ['rfid', 'manual']);
            $table->dateTime('fecha_hora');
            $table->foreignId('operador_id')->nullable()->constrained('users');
            $table->enum('estado', ['aprobado', 'denegado', 'pendiente'])->default('aprobado');
            $table->text('motivo')->nullable();
            $table->timestamps();
            
            // Índices importantes para búsquedas frecuentes
            $table->index('fecha_hora');
            $table->index('tipo_acceso');
            $table->index(['user_id', 'fecha_hora']);
            $table->index(['estado', 'fecha_hora']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_accesos');
    }
};
