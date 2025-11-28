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
        Schema::create('citas', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('barbero_id')->constrained('barberos');
            $table->foreignId('servicio_id')->constrained('servicios');

            // Datos de la cita
            $table->date('fecha');
            $table->time('hora');
            $table->string('estado', 20)->default('pendiente');

            $table->timestamps();

            // Un barbero no puede tener dos citas a la misma fecha/hora
            $table->unique(
                ['barbero_id', 'fecha', 'hora'],
                'citas_unique_barbero_fecha_hora'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
