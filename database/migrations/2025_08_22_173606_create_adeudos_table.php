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
        Schema::create('adeudos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('concepto_id')->constrained('conceptos')->restrictOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->restrictOnDelete();
            $table->enum('estado',['pendiente','pagado','vencido']);
            $table->decimal('pendiente',10,2)->default(0);
            $table->decimal('pagado',10,2)->default(0);
            $table->decimal('total',10,2);
            $table->date('fecha_inicio');
            $table->date('fecha_vencimiento');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adeudos');
    }
};
