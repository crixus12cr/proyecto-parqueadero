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
        Schema::create('configuraciones_respaldo', function (Blueprint $table) {
            $table->id();
            $table->enum('frecuencia', ['diario', 'semanal', 'mensual', 'manual'])->default('manual');
            $table->time('hora_programada')->nullable();
            $table->integer('dia_semana')->nullable()->comment('0=Domingo, 6=Sábado');
            $table->integer('dia_mes')->nullable()->comment('1-31');
            $table->integer('mantener_respaldos')->default(10);
            $table->boolean('incluir_archivos')->default(true);
            $table->string('notificar_email')->nullable();
            $table->datetime('ultimo_respaldo')->nullable();
            $table->datetime('proximo_respaldo')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_respaldos');
    }
};
