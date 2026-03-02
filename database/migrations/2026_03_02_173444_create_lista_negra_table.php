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
        Schema::create('lista_negra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarjeta_rfid_id')->nullable()->constrained('tarjeta_rfids');
            $table->string('placa', 10)->nullable();
            $table->text('motivo');
            $table->dateTime('expira_en')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('placa');
            $table->index('expira_en');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lista_negra');
    }
};
