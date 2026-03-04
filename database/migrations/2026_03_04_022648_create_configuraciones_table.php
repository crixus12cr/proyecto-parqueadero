<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_configuracions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();
            $table->integer('capacidad_total')->default(500);
            $table->time('horario_apertura')->default('06:00:00');
            $table->time('horario_cierre')->default('22:00:00');
            $table->json('dias_habiles')->nullable(); // ["Lunes","Martes","Miércoles","Jueves","Viernes"]
            $table->integer('alerta_ocupacion')->default(80); // 80%
            $table->integer('tiempo_gracia')->default(15); // minutos
            $table->integer('intentos_maximos_rfid')->default(3);
            $table->boolean('notificar_email')->default(true);
            $table->string('email_notificaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};