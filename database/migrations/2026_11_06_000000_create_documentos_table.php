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
        Schema::create('documentos', function (Blueprint $table) {
            // Columnas del UML
            $table->id('id_documento');
            $table->string('descripcion_documento', 255);
            $table->date('vigencia_inicio');
            $table->date('vigencia_fin');
            $table->decimal('importe', 10, 2);
            $table->string('ruta_archivo', 255)->nullable();

            // Metadatos del archivo (RF-13): extras al UML
            $table->string('nombre_original', 255)->nullable();
            $table->string('tipo_archivo', 100)->nullable();
            $table->unsignedBigInteger('tamano_archivo')->nullable();

            // Llave foránea hacia vehiculos.id_vehiculo.
            // Todavía SIN constraint porque la tabla vehiculos aún no existe.
            // Cuando exista, se agrega con una migración aparte:
            // $table->foreign('vehiculo_id')->references('id_vehiculo')->on('vehiculos');
            $table->unsignedBigInteger('vehiculo_id')->index();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};