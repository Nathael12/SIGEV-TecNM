<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->string('estado_mantenimiento', 20)->default('pendiente');
        });

        Schema::create('configuracion_alertas', function (Blueprint $table) {
            $table->id();
            $table->string('entidad', 50)->default('mantenimiento');
            $table->unsignedSmallInteger('dias_anticipacion');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['entidad', 'dias_anticipacion']);
        });

        Schema::create('alertas_mantenimiento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mantenimiento_id');
            $table->unsignedSmallInteger('dias_anticipacion')->default(0);
            $table->string('tipo', 20);
            $table->string('estado', 20)->default('activa');
            $table->date('fecha_mantenimiento');
            $table->timestamp('vista_en')->nullable();
            $table->timestamps();
            $table->foreign('mantenimiento_id')->references('id_mantenimiento')->on('mantenimientos')->cascadeOnDelete();
            $table->unique(['mantenimiento_id', 'dias_anticipacion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas_mantenimiento');
        Schema::dropIfExists('configuracion_alertas');
        Schema::table('mantenimientos', fn (Blueprint $table) => $table->dropColumn('estado_mantenimiento'));
    }
};
