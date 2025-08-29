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
        Schema::create('conceptos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->decimal('costo',10,2);
            $table->enum('periodo',['pago_unico','mensual','semestral']);
            $table->enum('nivel',['general','preescolar','primaria','secundaria','bachillerato','bachillerato_sabatino'])->default('general');
            $table->enum('modalidad',['general','presencial','en_linea'])->default('general');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conceptos');
    }
};
