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
        Schema::create('visitantes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo');
            $table->string('numero_documento', 20);
            $table->string('placa_vehiculo', 10);
            $table->foreignId('user_id_anfitrion')->constrained('users');
            $table->dateTime('fecha_ingreso');
            $table->dateTime('fecha_salida')->nullable();
            $table->foreignId('autorizado_por')->constrained('users');
            $table->string('estado', 20)->default('activo');
            $table->timestamps();
            
            // Índices
            $table->index('placa_vehiculo');
            $table->index('numero_documento');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitantes');
    }
};
