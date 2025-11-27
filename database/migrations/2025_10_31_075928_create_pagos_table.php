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
        Schema::create('pagos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cita_id')->constrained('citas')->cascadeOnDelete();
    $table->decimal('monto', 12, 2);
    $table->string('metodo', 30)->default('efectivo');
    $table->string('estado', 20)->default('pagado'); // pagado|pendiente|anulado
    $table->date('pagado_at')->nullable();
    $table->timestamps();
    $table->unique('cita_id'); // 1 pago por cita (ajústalo si permites varios)
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
