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
        Schema::create('incidencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registro_acceso_id')->constrained('registro_accesos');
            $table->foreignId('reportado_por')->constrained('users');
            $table->text('descripcion');
            $table->boolean('resuelto')->default(false);
            $table->dateTime('resuelto_en')->nullable();
            $table->text('notas_resolucion')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('resuelto');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidencias');
    }
};
