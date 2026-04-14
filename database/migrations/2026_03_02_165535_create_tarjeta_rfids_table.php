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
        Schema::create('tarjeta_rfids', function (Blueprint $table) {
            $table->id();
            $table->string('uid_tarjeta', 50)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->dateTime('fecha_asignacion');
            $table->dateTime('fecha_vencimiento')->nullable();
            $table->string('estado', 20)->default('activa');
            $table->dateTime('ultimo_uso')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Índices
            $table->index('uid_tarjeta');
            $table->index('estado');
            $table->index('fecha_vencimiento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarjeta_rfids');
    }
};
