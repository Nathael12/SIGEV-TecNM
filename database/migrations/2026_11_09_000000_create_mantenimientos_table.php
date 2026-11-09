<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id('id_mantenimiento');
            $table->date('fecha_mantenimiento');
            $table->unsignedInteger('kilometraje_mantenimiento');
            $table->string('categoria_mantenimiento', 45);
            $table->string('descripcion_mantenimiento', 255);
            $table->decimal('monto_mantenimiento', 10, 2);
            $table->date('proximo_mantenimiento_fecha');
            $table->unsignedInteger('proximo_mantenimiento_km');
            $table->unsignedBigInteger('vehiculo_id');
            $table->timestamps();

            $table->foreign('vehiculo_id')->references('id_vehiculo')->on('vehiculos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
