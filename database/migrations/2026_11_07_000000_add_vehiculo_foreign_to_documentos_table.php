<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    /**
     * IMPORTANTE: esta migración debe tener una fecha POSTERIOR a la de
     * la migración que crea la tabla vehiculos (si no, vehiculos aún no
     * existiría cuando corra). Se puede cambiar renombrando el archivo.
     */
    public function up(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            // Sin onDelete: un vehículo con documentos no se puede borrar,
            // lo correcto es darlo de "Baja" (estado del UML).
            $table->foreign('vehiculo_id')
                ->references('id_vehiculo')
                ->on('vehiculos');
        });
    }
 
    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropForeign(['vehiculo_id']);
        });
    }
};