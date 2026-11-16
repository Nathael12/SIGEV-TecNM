<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * IMPORTANTE: debe tener una fecha POSTERIOR a las migraciones de
     * users y vehiculos (las llaves foráneas apuntan a esas tablas).
     */
    public function up(): void
    {
        Schema::create('asignacion_vehiculos', function (Blueprint $table) {
            // Columnas del UML
            $table->id('id_asignacion');
            $table->dateTime('fecha_salida');

            // RN-04: una asignación está activa mientras fecha_regreso sea NULL.
            // Por eso fecha_regreso y kilometraje_final son opcionales
            // (en el UML aparecen como obligatorias: hay que actualizarlo).
            $table->dateTime('fecha_regreso')->nullable();

            $table->string('motivo_uso', 255);
            $table->string('destino_vehiculo', 255);
            $table->unsignedInteger('kilometraje_inicial');
            $table->unsignedInteger('kilometraje_final')->nullable();

            // Llaves foráneas
            // usuario_id  -> users.id (el solicitante)
            // vehiculo_id -> vehiculos.id_vehiculo
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('vehiculo_id')->constrained('vehiculos', 'id_vehiculo');

            // RN-08: el historial se conserva, por eso NO lleva softDeletes
            // y no habrá opción de eliminar asignaciones.
            $table->timestamps();

            // Acelera la búsqueda de la asignación activa de un vehículo (RF-18)
            $table->index(['vehiculo_id', 'fecha_regreso']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignacion_vehiculos');
    }
};